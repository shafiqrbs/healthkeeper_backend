<?php

namespace Modules\Medicine\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DailyStockService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\AccountJournalModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Models\CurrentStockModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Medicine\App\Http\Requests\DispenseRequest;
use Modules\Medicine\App\Models\DispenseItemModel;
use Modules\Medicine\App\Models\DispenseModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class DispenseController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if ($userId && !empty($userId)){
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function dispenseStockItem(Request $request, $warehouseId){
        $data = CurrentStockModel::getDispenseStockItem($request,$this->domain,$warehouseId);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => ResponseAlias::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities']
        ]));
        $response->setStatusCode(ResponseAlias::HTTP_OK);
        return $response;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request){

        $data = DispenseModel::getRecords($request,$this->domain);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => ResponseAlias::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities']
        ]));
        $response->setStatusCode(ResponseAlias::HTTP_OK);
        return $response;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DispenseRequest $request)
    {
        DB::beginTransaction();

        try {
            $input = $request->validated();
            $input['config_id'] = $this->domain['hms_config'];
            $input['process']   = 'Created';
            $input['status']    = 1;

            $dispense = DispenseModel::create($input);

            DispenseItemModel::insertDispenseItems(
                $dispense,
                $input['items']
            );

            DB::commit();

            return response()->json([
                'status'  => 200,
                'success' => true,
                'message' => 'Dispense created successfully.',
                'data'    => $dispense,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'success' => false,
                'message' => 'Failed to create dispense.',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function update(DispenseRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $input = $request->validated();

            $dispense = DispenseModel::find($id);
            if (!$dispense) {
                DB::rollBack();

                return response()->json([
                    'status'  => 404,
                    'success' => false,
                    'message' => 'Dispense not found.',
                ], 404);
            }

            $dispense->update([
                'remark'         => $input['remark'],
                'dispense_type'  => $input['dispense_type'],
                'dispense_no'    => $input['dispense_no'],
            ]);

            if (!empty($input['items'])) {
                DispenseItemModel::where('dispense_id', $id)->delete();
                DispenseItemModel::insertDispenseItems(
                    $dispense,
                    $input['items']
                );
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'success' => true,
                'message' => 'Dispense updated successfully.',
                'data'    => $dispense,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'success' => false,
                'message' => 'Failed to update dispense.',
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = DispenseModel::show($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        return $service->returnJosnResponse($entity);
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {

            $dispense = DispenseModel::find($id);

            if (!$dispense) {
                abort(404, 'Dispense not found.');
            }

            if ($dispense->process === 'Approved' || $dispense->approved_by_id) {
                abort(400, 'Dispense has been approved.');
            }

            DispenseItemModel::where('dispense_id', $id)->delete();
            $dispense->delete();

            return response()->json([
                'status'  => 200,
                'success' => true,
                'message' => 'Dispense deleted successfully.',
            ]);
        });
    }

    public function approve($id)
    {
        DB::beginTransaction();

        try {
            $dispense = DispenseModel::where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($dispense->process === 'Approved') {
                return response()->json([
                    'status'  => ResponseAlias::HTTP_BAD_REQUEST,
                    'success' => false,
                    'message' => 'Dispense has been approved already.',
                ]);
            }

            $dispense->update([
                'approved_by_id' => $this->domain['user_id'],
                'approved_date' => now(),
                'process' => 'Approved',
            ]);

            if ($dispense->dispenseItems()->exists()) {
                foreach ($dispense->dispenseItems as $item) {
                    StockItemHistoryModel::openingStockQuantity(
                        $item,
                        $dispense->dispense_type,
                        $this->domain
                    );

                    DailyStockService::maintainDailyStock(
                        date: now()->toDateString(),
                        field: $dispense->dispense_type === 'dispense-in'
                            ? 'dispense_in_quantity'
                            : 'dispense_out_quantity',
                        configId: $this->domain['config_id'],
                        warehouseId: $item->warehouse_id,
                        stockItemId: $item->stock_item_id,
                        quantity: $item->quantity
                    );
                }
            }

            DB::commit();

            return response()->json([
                'status'  => ResponseAlias::HTTP_OK,
                'success' => true,
                'message' => 'Dispense Approved successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }



}
