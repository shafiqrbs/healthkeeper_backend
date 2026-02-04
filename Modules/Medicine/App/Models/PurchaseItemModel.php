<?php

namespace Modules\Medicine\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemInventoryHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;

class PurchaseItemModel extends Model
{
    protected $table = 'inv_purchase_item';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
                'config_id',
                'created_by_id',
                'approved_by_id',
                'warehouse_transfer_quantity',
				"stock_item_id",
				"purchase_id",
				"name",
				"quantity",
				"production_date",
				"expired_date",
				"warehouse_id"
        ];

    public static function boot() {

        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }


    public function purchase()
    {
        return $this->belongsTo(PurchaseModel::class);
    }

    public function stockItems()
    {
        return $this->hasMany(StockItemHistoryModel::class,'purchase_item_id');
    }
    public function inventoryItemHistory() : HasMany
    {
        return $this->hasMany(StockItemInventoryHistoryModel::class,'purchase_item_id');
    }

    public function stock() : BelongsTo
    {
        return $this->belongsTo(StockItemModel::class , 'stock_item_id');
    }

    public static function remainingQuantity($id): int
    {
        $item = self::find($id, [
            'quantity',
            'sales_quantity',
            'sales_return_quantity',
            'sales_replace_quantity',
            'purchase_return_quantity',
            'damage_quantity',
            'warehouse_transfer_quantity',
        ]);

        if (!$item) {
            return 0;
        }

        $salesQuantity = $item->sales_quantity ?? 0;
        $salesReplaceQuantity = $item->sales_replace_quantity ?? 0;
        $damageQuantity = $item->damage_quantity ?? 0;
        $warehouseTransferQuantity = $item->warehouse_transfer_quantity ?? 0;

        $salesReturnQuantity = $item->sales_return_quantity ?? 0;
        $purchaseReturnQuantity = $item->purchase_return_quantity ?? 0;

        $minusQuantity = $salesQuantity + $salesReplaceQuantity + $damageQuantity + $warehouseTransferQuantity;
        $plusQuantity = $salesReturnQuantity + $purchaseReturnQuantity;

        $remainingQuantity = ($item->quantity ?? 0) + $plusQuantity - $minusQuantity;

        return (int) $remainingQuantity;
    }
    public static function getBatchWiseStockReport($params, $domain)
    {
        $page     = max(1, (int)($params['page'] ?? 1));
        $perPage  = (int)($params['offset'] ?? 50);
        $skip     = ($page - 1) * $perPage;

        $stockItemId = $params['stock_item_id'] ?? null;

        $startDate = !empty($params['start_date'])
            ? Carbon::parse($params['start_date'])->startOfDay()
            : null;

        $endDate = !empty($params['end_date'])
            ? Carbon::parse($params['end_date'])->endOfDay()
            : ($startDate ? $startDate->copy()->endOfDay() : null);

        $query = self::where('inv_purchase_item.config_id', $domain['inv_config'])
            ->select([
                'inv_purchase_item.id',
                'inv_purchase_item.stock_item_id',
                'inv_purchase_item.quantity as purchase_quantity',
                'inv_purchase_item.warehouse_transfer_quantity as indent_quantity',
                'inv_purchase_item.mode',
                'inv_purchase_item.name',
                DB::raw('DATE_FORMAT(inv_purchase_item.production_date, "%d-%M-%Y") as production_date'),
                DB::raw('DATE_FORMAT(inv_purchase_item.expired_date, "%d-%M-%Y") as expired_date'),
                DB::raw('DATE_FORMAT(inv_purchase_item.created_at, "%d-%M-%Y") as created_at'),
            ]);

        if ($startDate && $endDate) {
            $query->whereBetween('inv_purchase_item.created_at', [$startDate, $endDate]);
        }

        if ($stockItemId) {
            $query->where('inv_purchase_item.stock_item_id', $stockItemId);
        }

        // Clone query for count safety
        $total = (clone $query)->count();

        $data = $query
            ->orderByDesc('inv_purchase_item.id')
            ->skip($skip)
            ->take($perPage)
            ->get();

        return [
            'count'    => $total,
            'items' => $data->toArray(),
        ];
    }



}
