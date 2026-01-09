<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DailyStockService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Log;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\FileUploadRequest;
use Modules\Core\App\Models\FileUploadModel;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileUploadController extends Controller
{
    protected $domain;
    protected $pattarnCodeService;

    public function __construct(Request $request, GeneratePatternCodeService $patternCodeService)
    {
        $userId = $request->header('X-Api-User');
        if ($userId && !empty($userId)) {
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
        $this->pattarnCodeService = $patternCodeService;
    }

    public function index(Request $request)
    {

        $data = FileUploadModel::getRecords($request, $this->domain);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(FileUploadRequest $request)
    {
        $data = $request->validated();

        // Start the transaction.
        DB::beginTransaction();

        try {
            $data['domain_id'] = $this->domain->global_id;
            if ($request->file('file')) {
                $data['original_name'] = $request->file('file')->getClientOriginalName();
                $file = $this->processFileUpload($request->file('file'), '/uploads/core/file-upload/');
                if ($file) {
                    $data['file'] = $file;
                }
            }

            $entity = FileUploadModel::create($data);

            // If we got this far, everything is okay, commit the transaction.
            DB::commit();

            // Return a json response using your service.
            $service = new JsonRequestResponse();
            return $service->returnJosnResponse($entity);

        } catch (Exception $e) {
            // If there's an exception, rollback the transaction.
            DB::rollBack();

            // Optionally log the exception (for debugging purposes)
            Log::error('Error updating domain and inventory settings: ' . $e->getMessage());

            // Return an error response.
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'An error occurred while updating.',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }
    }

    private function processFileUpload($file, $uploadDir)
    {
        if ($file) {
            $uploadDirPath = public_path($uploadDir);

            // Ensure that the directory exists
            if (!file_exists($uploadDirPath)) {
                mkdir($uploadDirPath, 0777, true); // Recursively create the directory with full permissions
            }

            // Generate a unique file name with timestamp
            $fileName = time() . '.' . $file->extension();

            // Move the uploaded file to the target location
            $file->move($uploadDirPath, $fileName);

            return $fileName;
        }

        return null;
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        FileUploadModel::find($id)->delete();
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'delete'
        ]);
    }

    /**
     * process file data to DB.
     */

    public function fileProcessToDB(Request $request)
    {
        set_time_limit(0);
        $fileID = $request->file_id;
        $getFile = FileUploadModel::find($fileID);
        if ($getFile->is_process) {
            return response()->json([
                'status' => ResponseAlias::HTTP_BAD_REQUEST,
                'success' => false,
                'message' => 'File already processed'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
        $filePath = public_path('/uploads/core/file-upload/') . $getFile->file;

        // Load file based on extension
        $reader = match (pathinfo($filePath, PATHINFO_EXTENSION)) {
            'xlsx' => new Xlsx(),
            'csv' => new Csv(),
            default => throw new Exception('Unsupported file format.')
        };

        $allData = $reader->load($filePath)->getActiveSheet()->toArray();

        // for process data with header
        $spreadsheet = $reader->load($filePath);
        $data = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        // Use the first row as headers
        $headers = array_shift($data);

        // Map rows to headers
        $dataWithHeaders = [];
        foreach ($data as $row) {
            $mappedRow = [];
            foreach ($headers as $column => $headerName) {
                $mappedRow[$headerName] = $row[$column] ?? null; // Use header name as key
            }
            $dataWithHeaders[] = $mappedRow;
        }

        // Remove headers
        $keys = array_map('trim', array_shift($allData));
        // Only proceed if it's 'Product' and structure is correct
        if ($getFile->file_type === 'Opening-Stock') {
            $isInsert = $this->insertOpeningStock($dataWithHeaders);
        } else {
            $message = 'Invalid file type or structure.';
            return response()->json([
                'message' => $message,
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($isInsert['is_insert']) {
            $getFile->update(['is_process' => true, 'process_row' => $isInsert['row_count']]);

            return response()->json([
                'message' => 'success',
                'status' => ResponseAlias::HTTP_OK,
                'row' => $isInsert['row_count']
            ], ResponseAlias::HTTP_OK);
        }
    }

    // for opening stock process for upload
    private function insertOpeningStock($allData)
    {
        $batchSize = 1000;
        $batch = [];
        $rowsProcessed = 0;

        // Get all stock items in one query
        $stockItemsIds = array_column($allData, 'StockItemID');
        $stockItems = StockItemModel::whereIn('id', $stockItemsIds)->get()->keyBy('id');

        foreach ($allData as $index => $data) {
            $values = array_map(fn($item) => is_string($item) ? trim($item) : $item, $data);
            $stockItemId = $values['StockItemID'] ?? null;
            $openingStock = $values['OpeningQuantity'] ?? 0;
            $warehouseId = $values['WarehouseID'];
            $productionDate = $values['ProductionDate'] ?? null;
            $expireDate = $values['ExpiredDate'] ?? null;

            if (!$stockItemId || !isset($stockItems[$stockItemId]) || !$warehouseId) {
                continue;
            }

            $findStockItem = $stockItems[$stockItemId];

            $batch[] = [
                'config_id' => $this->domain['config_id'],
                'created_by_id' => $this->domain['user_id'],
                'approved_by_id' => $this->domain['user_id'],
                'production_date' => $productionDate,
                'expired_date' => $expireDate,
                'warehouse_id' => $warehouseId,
                'stock_item_id' => $findStockItem->id,
                'name' => $findStockItem->name,
                'opening_quantity' => $openingStock,
                'quantity' => $openingStock,
                'mode' => 'opening',
                'sales_price' => $findStockItem->sales_price,
                'purchase_price' => $findStockItem->purchase_price,
                'sub_total' => $openingStock * $findStockItem->purchase_price,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Batch insert when batch size is reached
            if (count($batch) >= $batchSize) {
                $rowsProcessed += $this->processOpeningStockBatch($batch);
                $batch = [];
            }
        }

        // Process remaining items
        if (count($batch) > 0) {
            $rowsProcessed += $this->processOpeningStockBatch($batch);
        }

        return ['is_insert' => true, 'row_count' => $rowsProcessed];
    }

    private function processOpeningStockBatch(array $batch)
    {
        if (empty($batch)) {
            return 0;
        }
        // Bulk insert
        PurchaseItemModel::insert($batch);

        // Get inserted records for furthequantityr processing
        $insertedRecords = PurchaseItemModel::latest('id')->take(count($batch))->get();
        foreach ($insertedRecords as $purchase) {
            if ($purchase->quantity) {
                StockItemHistoryModel::openingStockQuantity($purchase, 'opening', $this->domain);

                // for maintain inventory daily stock
                date_default_timezone_set('Asia/Dhaka');
                DailyStockService::maintainDailyStock(
                    date: date('Y-m-d'),
                    field: 'purchase_quantity',
                    configId: $this->domain['config_id'],
                    warehouseId: $purchase->warehouse_id ?? $this->domain['warehouse_id'],
                    stockItemId: $purchase->stock_item_id,
                    quantity: $purchase->quantity
                );
            }
        }
        return count($batch);
    }


}
