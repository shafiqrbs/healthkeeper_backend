<?php

namespace App\Jobs;

use App\Services\DailyStockService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Models\DamageItemModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Medicine\App\Models\PurchaseItemModel;

class ProcessExpiredItems implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {

            $expiredPurchaseItems = PurchaseItemModel::whereDate('expired_date', '<=', now())
                ->where('remaining_quantity', '>', 0)
                ->select('id', 'remaining_quantity', 'stock_item_id', 'name', 'warehouse_id', 'config_id')
                ->lockForUpdate()
                ->get();

            foreach ($expiredPurchaseItems as $item) {

                $qty = $item->remaining_quantity;

                // create or update damage record
                $damageItem = DamageItemModel::updateOrCreate([
                    'config_id' => $item->config_id,
                    'purchase_item_id' => $item->id,
                    'warehouse_id' => $item->warehouse_id,
                    'damage_mode' => 'Purchase',
                    'process' => 'Created'
                ],[
                    'quantity' => $qty,
                    'price' => $item->purchase_price,
                    'purchase_price' => $item->purchase_price,
                    'sub_total' => $item->purchase_price*$qty,
                ]);

                // stock manage
                $domain = UserModel::getUserDataByConfigId($item->config_id);
                $damageItem->stock_item_id = $item->stock_item_id;
                $damageItem->name = $item->name;
                StockItemHistoryModel::openingStockQuantity($damageItem, 'damage', $domain);
                // for maintain inventory daily stock
                date_default_timezone_set('Asia/Dhaka');
                DailyStockService::maintainDailyStock(
                    date: date('Y-m-d'),
                    field: 'damage_quantity',
                    configId: $damageItem->config_id,
                    warehouseId: $damageItem->warehouse_id,
                    stockItemId: $damageItem->stock_item_id,
                    quantity: $damageItem->quantity
                );
                DamageItemModel::find($damageItem->id)->update(['process' => 'Completed']);

                $remainingQuantity = PurchaseItemModel::getPurchaseRemainingQuantity($item->id);

                $item->update([
                    'damage_quantity' => $item->damage_quantity + $qty,
                    'remaining_quantity' => $remainingQuantity,
                ]);
            }
        });
    }
}
