<?php

namespace Modules\Medicine\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemInventoryHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;

class DispenseItemModel extends Model
{
    protected $table = 'hms_dispense_item';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
                'config_id',
                'dispense_id',
                'warehouse_id',
                'stock_item_id',
                'unit_id',
				"stock_item_id",
				"name",
				"quantity",
				"status"
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


    public function dispense()
    {
        return $this->belongsTo(DispenseModel::class);
    }

    public function stockItems()
    {
        return $this->hasMany(StockItemHistoryModel::class,'purchase_item_id');
    }
    public function stock() : BelongsTo
    {
        return $this->belongsTo(StockItemModel::class , 'stock_item_id');
    }

    /**
     * Insert multiple dispense items.
     *
     * @param DispenseModel $dispense dispense instance.
     * @param array $items Incoming items to insert.
     * @return bool
     */
    public static function insertDispenseItems($dispense, array $items): bool
    {
        $timestamp = Carbon::now();

        $formattedItems = array_map(function ($item) use ($dispense, $timestamp) {
            return [
                'dispense_id'       => $dispense->id,
                'warehouse_id'      => $item['warehouse_id'] ?? null,
                'config_id'      => $item['config_id'] ?? null,
                'stock_item_id'  => $item['stock_item_id'] ?? null,
                'unit_id'  => $item['unit_id'] ?? null,
                'name'  => $item['name'] ?? null,
                'quantity'       => $item['quantity'] ?? 0,
                'status'     => 1,
                'created_at'     => $timestamp,
                'updated_at'     => $timestamp,
            ];
        }, $items);

        return self::insert($formattedItems);
    }

}
