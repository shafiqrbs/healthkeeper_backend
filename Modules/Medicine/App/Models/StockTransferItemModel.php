<?php

namespace Modules\Medicine\App\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockTransferItemModel extends Model
{
    protected $table = 'inv_stock_transfer_item';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'config_id', 'stock_transfer_id', 'stock_item_id', 'purchase_item_id', 'quantity', 'created_at', 'updated_at', 'uom', 'name', 'stock_quantity','remaining_quantity','issue_quantity','damage_quantity'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $date = new DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date = new DateTime("now");
            $model->updated_at = $date;
        });
    }

    public function stockTransfer()
    {
        return $this->belongsTo(StockTransferModel::class, 'stock_transfer_id');
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItemModel::class, 'purchase_item_id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItemModel::class, 'stock_item_id', 'stock_item_id')
            ->whereNotNull('expired_date')
            ->where('expired_date', '>', now())
            ->whereRaw('quantity > COALESCE(sales_quantity, 0)');
    }

    public static function getBatchWiseStockLedgerReport(array $params)
    {
        $page    = max(1, (int)($params['page'] ?? 1));
        $perPage = (int)($params['offset'] ?? 50);
        $skip    = ($page - 1) * $perPage;

        $stockItemId    = $params['stock_item_id'] ?? null;
        $purchaseItemId = $params['purchase_item_id'] ?? null;

        $startDate = !empty($params['start_date'])
            ? Carbon::parse($params['start_date'])->startOfDay()
            : null;

        $endDate = !empty($params['end_date'])
            ? Carbon::parse($params['end_date'])->endOfDay()
            : ($startDate ? $startDate->copy()->endOfDay() : null);

        $firstRow = DB::table('inv_stock_transfer_item')
            ->where('stock_item_id', $stockItemId)
            ->where('purchase_item_id', $purchaseItemId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->first();

        $openingStock = $firstRow ? (int)$firstRow->stock_quantity : 0;

        /*$baseQuery = DB::table('inv_stock_transfer_item')
            ->selectRaw('DATE(created_at) as date, SUM(quantity) as indent_quantity')
            ->where('stock_item_id', $stockItemId)
            ->where('purchase_item_id', $purchaseItemId)
            ->whereBetween('created_at', [$startDate, $endDate])
//            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('created_at', 'asc');*/

        $baseQuery = DB::table('inv_stock_transfer_item')
            ->join('inv_stock_transfer', 'inv_stock_transfer_item.stock_transfer_id', '=', 'inv_stock_transfer.id')
            ->join('cor_warehouses as fw', 'fw.id', '=', 'inv_stock_transfer.from_warehouse_id')
            ->join('cor_warehouses as tw', 'tw.id', '=', 'inv_stock_transfer.to_warehouse_id')
            ->select([
                DB::raw('DATE(inv_stock_transfer_item.created_at) as date'),
                'inv_stock_transfer_item.quantity as indent_quantity',
                'fw.name as from_warehouse_name',
                'tw.name as to_warehouse_name',
                'inv_stock_transfer_item.stock_quantity', // Needed for remaining calculation
            ])
            ->where('inv_stock_transfer_item.stock_item_id', $stockItemId)
            ->where('inv_stock_transfer_item.purchase_item_id', $purchaseItemId)
            ->whereBetween('inv_stock_transfer_item.created_at', [$startDate, $endDate])
            ->orderBy('inv_stock_transfer_item.created_at', 'asc');
        
//        dump($rows);

        $total = DB::query()->fromSub($baseQuery, 't')->count();

        $rows = $baseQuery
            ->skip($skip)
            ->take($perPage)
            ->get();

        /*$report = [];
        $currentOpeningStock = $openingStock;

        foreach ($rows as $row) {
            $indentQty = (int)$row->indent_quantity;
            $remainingQty = $currentOpeningStock - $indentQty;

            $report[] = [
                'date'               => Carbon::parse($row->date)->format('d-m-y'),
                'opening_stock'      => $currentOpeningStock,
                'indent_quantity'    => $indentQty,
                'remaining_quantity' => $remainingQty,
            ];

            $currentOpeningStock = $remainingQty;
        }*/

        $rows = $baseQuery->get();

        $report = [];
        $currentOpeningStock = $rows->first()->stock_quantity ?? 0;

        foreach ($rows as $row) {
            $indentQty = (int)$row->indent_quantity;
            $remainingQty = $currentOpeningStock - $indentQty;

            $report[] = [
                'date'               => Carbon::parse($row->date)->format('d-m-y'),
                'opening_stock'      => $currentOpeningStock,
                'indent_quantity'    => $indentQty,
                'remaining_quantity' => $remainingQty,
                'from_warehouse'     => $row->from_warehouse_name,
                'to_warehouse'       => $row->to_warehouse_name,
            ];

            $currentOpeningStock = $remainingQty;
        }


        return [
            'count' => $total,
            'items' => $report,
            'page'  => $page,
            'perPage' => $perPage,
        ];
    }

    public static function getBatchWiseStockLedgerReport1($params , $domain){
//        dump($params,$domain);

        /*$page     = max(1, (int)($params['page'] ?? 1));
        $perPage  = (int)($params['offset'] ?? 50);
        $skip     = ($page - 1) * $perPage;

        $stockItemId = $params['stock_item_id'] ?? null;
        $purchaseItemId = $params['purchase_item_id'] ?? null;

        $startDate = !empty($params['start_date'])
            ? Carbon::parse($params['start_date'])->startOfDay()
            : null;

        $endDate = !empty($params['end_date'])
            ? Carbon::parse($params['end_date'])->endOfDay()
            : ($startDate ? $startDate->copy()->endOfDay() : null);

        $firstRow = DB::table('inv_stock_transfer_item')
            ->where('stock_item_id', $stockItemId)
            ->where('purchase_item_id', $purchaseItemId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')  // earliest row first
            ->first();

        $openingStock = $firstRow ? (int)$firstRow->stock_quantity : 0;


        $rows = DB::table('inv_stock_transfer_item')
            ->selectRaw('
        DATE(created_at) as date,
        SUM(quantity) as indent_quantity
    ')
            ->where('stock_item_id', $stockItemId)
            ->where('purchase_item_id', $purchaseItemId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('created_at', 'asc')
            ->get();

        $report = [];
        $currentOpeningStock = $openingStock;

        foreach ($rows as $row) {
            $indentQty = (int)$row->indent_quantity;
            $remainingQty = $currentOpeningStock - $indentQty;

            $report[] = [
                'date'               => Carbon::parse($row->date)->format('d-m-y'),
                'opening_stock'      => $currentOpeningStock,
                'indent_quantity'    => $indentQty,
                'remaining_quantity' => $remainingQty,
            ];

            $currentOpeningStock = $remainingQty;
        }

        dump($report);*/



        /* $query = DB::table('inv_stock_transfer_item')
             ->selectRaw('
             DATE(created_at) as date,
             SUM(quantity) as indent_quantity,
             MIN(stock_quantity) as first_row_stock_quantity
         ')
             ->where('stock_item_id', $stockItemId)
             ->where('purchase_item_id', $purchaseItemId)
             ->whereBetween('created_at', [$startDate, $endDate])
             ->groupBy(DB::raw('DATE(created_at)'))
             ->orderBy('id', 'asc')->get();

         dump($query);*/

        /*$total = DB::query()
            ->fromSub($query, 't')
            ->count();


        $rows = $query
            ->skip($skip)
            ->take($perPage)
            ->get();

        $items = [];
        $currentOpeningStock = null;

        foreach ($rows as $index => $row) {
            // For the first row, opening stock = stock_quantity of that first row
            if ($index === 0 && $currentOpeningStock === null) {
                $currentOpeningStock = (int)$row->first_row_stock_quantity;
            }

            $indentQty = (int)$row->indent_quantity;
            $remainingQty = $currentOpeningStock - $indentQty;

            $items[] = [
                'date'               => Carbon::parse($row->date)->format('d-m-y'),
                'opening_stock'      => $currentOpeningStock,
                'indent_quantity'    => $indentQty,
                'remaining_quantity' => $remainingQty,
            ];

            // Next row opening stock = previous row's remaining
            $currentOpeningStock = $remainingQty;
        }

        return [
            'count' => $total,
            'items' => $items,
        ];

        /*$rows = DB::table('inv_stock_transfer_item')
            ->selectRaw('
                DATE(created_at) as date,
                SUM(quantity) as indent_quantity,
                SUM(stock_quantity) as opening_quantity,
            ')
            ->where('stock_item_id', $stockItemId)
            ->where('purchase_item_id', $purchaseItemId)
            ->whereBetween('created_at', [
                $startDate ,
                $endDate
            ])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Clone query for count safety
        $total = (clone $rows)->count();

        $data = $rows
            ->orderByDesc('inv_stock_transfer_item.id')
            ->skip($skip)
            ->take($perPage)
            ->get();

        return [
            'count'    => $total,
            'items' => $data->toArray(),
        ];*/

        /*$openingStock = DB::table('inv_stock_transfer_item')
            ->where('stock_item_id', $stockItemId)
            ->where('purchase_item_id', $purchaseItemId)
            ->whereDate('created_at', '<', $startDate)
            ->orderBy('created_at', 'desc')
            ->value('stock_quantity') ?? 0;*/

        /*$report = [];
        $currentOpeningStock = $openingStock;

        foreach ($rows as $row) {
            $indentQty = (int) $row->indent_quantity;
            $remainingQty = $currentOpeningStock - $indentQty;

            $report[] = [
                'date' => $row->date,
                'opening_stock' => $currentOpeningStock,
                'indent_quantity' => $indentQty,
                'remaining_quantity' => $remainingQty,
            ];

            // next day's opening stock
            $currentOpeningStock = $remainingQty;
        }*/


    }


    public static function getStockTransferItemRemainingQuantity($id)
    {
        $item = self::find($id);

        if (!$item) {
            return 0;
        }

        // Calculate remaining quantity
        return ($item->quantity ?? 0)
            - (
                ($item->issue_quantity ?? 0)
                + ($item->damage_quantity ?? 0)
            );
    }

    public static function getIndentWiseItemIssue1($stockItemId, $quantity, $warehouseId, $configId)
    {
        $items = self::join('inv_stock_transfer','inv_stock_transfer.id','=','inv_stock_transfer_item.stock_transfer_id')
            ->where('inv_stock_transfer_item.stock_item_id', $stockItemId)
            ->where('inv_stock_transfer.to_warehouse_id', $warehouseId)
            ->where('inv_stock_transfer.config_id', $configId)
            ->whereNotNull('inv_stock_transfer_item.purchase_item_id')
            ->where('inv_stock_transfer_item.remaining_quantity','>',0)
            ->orderBy('inv_stock_transfer_item.id','desc')
            ->select([
                'inv_stock_transfer_item.id',
                'inv_stock_transfer_item.name',
                'inv_stock_transfer_item.remaining_quantity',
                'inv_stock_transfer_item.issue_quantity',
            ])
            ->get();

        $remainingToIssue = $quantity;

        foreach ($items as $item) {
            if ($remainingToIssue <= 0) break;

            $availableQty = $item->remaining_quantity;

            $issueQty = min($availableQty, $remainingToIssue);

            $item->issue_quantity = ($item->issue_quantity ?? 0) + $issueQty;
            $item->remaining_quantity = $availableQty - $issueQty;
            $item->save();

            $remainingToIssue -= $issueQty;
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    public static function indentWiseItemIssue($stockItemId, $quantity, $warehouseId, $configId, $name)
    {
        $items = self::join('inv_stock_transfer','inv_stock_transfer.id','=','inv_stock_transfer_item.stock_transfer_id')
            ->where('inv_stock_transfer_item.stock_item_id', $stockItemId)
            ->where('inv_stock_transfer.to_warehouse_id', $warehouseId)
            ->where('inv_stock_transfer.config_id', $configId)
            ->whereNotNull('inv_stock_transfer_item.purchase_item_id')
            ->where('inv_stock_transfer_item.remaining_quantity','>',0)
            ->orderBy('inv_stock_transfer_item.id','asc')
            ->lockForUpdate()
            ->select([
                'inv_stock_transfer_item.id',
                'inv_stock_transfer_item.remaining_quantity',
                'inv_stock_transfer_item.issue_quantity',
            ])
            ->get();

        $remainingToIssue = $quantity;

        foreach ($items as $item) {
            if ($remainingToIssue <= 0) break;

            $availableQty = $item->remaining_quantity;

            $issueQty = min($availableQty, $remainingToIssue);

            $item->issue_quantity = ($item->issue_quantity ?? 0) + $issueQty;
            $item->remaining_quantity = $availableQty - $issueQty;
            $item->save();

            $remainingToIssue -= $issueQty;
        }

        if ($remainingToIssue > 0) {
            throw new \Exception("{$name} stock low. ");
        }

        return true;
    }



}
