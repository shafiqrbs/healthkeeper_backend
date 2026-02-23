<?php

namespace App\Jobs;

use App\Services\DailyStockService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Models\DamageItemModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Medicine\App\Models\PurchaseItemModel;
use Modules\Medicine\App\Models\StockTransferItemModel;

class ProcessExpiredItems implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        PurchaseItemModel::whereDate('expired_date', '<', now())
            ->where('remaining_quantity', '>', 0)
            ->select('id', 'remaining_quantity', 'stock_item_id', 'name', 'warehouse_id', 'config_id', 'purchase_price',
                'quantity','sales_return_quantity','bonus_quantity','warehouse_transfer_quantity',
                'sales_quantity','purchase_return_quantity','damage_quantity'
            )
            ->chunk(/**
             * @throws \Throwable
             */ 100, function ($items) {

                foreach ($items as $item) {

                    DB::transaction(function () use ($item) {

                        $qty = $item->remaining_quantity;

                        if ($qty <= 0) {
                            return;
                        }

                        // =========================
                        // PURCHASE DAMAGE PROCESS
                        // =========================
                        $this->processDamage(
                            type: 'purchase',
                            refId: $item->id,
                            qty: $qty,
                            stockItemId: $item->stock_item_id,
                            name: $item->name,
                            warehouseId: $item->warehouse_id,
                            configId: $item->config_id,
                            price: $item->purchase_price ?? 0
                        );

                        // update purchase item (atomic + correct calc)
                        $newDamageQty = $item->damage_quantity + $qty;

                        $remainingQuantity = PurchaseItemModel::getPurchaseItemRemainingQuantity($item->id);

                        $item->update([
                            'damage_quantity' => $newDamageQty,
                            'remaining_quantity' => $remainingQuantity-$newDamageQty,
                        ]);

                        // =========================
                        // TRANSFER DAMAGE PROCESS
                        // =========================
                        $transferItems = StockTransferItemModel::where('inv_stock_transfer_item.purchase_item_id', $item->id)
                            ->join('inv_stock_transfer', 'inv_stock_transfer.id', '=', 'inv_stock_transfer_item.stock_transfer_id')
                            ->where('inv_stock_transfer_item.remaining_quantity', '>', 0)
                            ->select(
                                'inv_stock_transfer_item.*',
                                'inv_stock_transfer.to_warehouse_id as warehouse_id'
                            )
                            ->lockForUpdate()
                            ->get();

                        foreach ($transferItems as $transfer) {

                            $qty = $transfer->remaining_quantity;

                            if ($qty <= 0) {
                                continue;
                            }

                            $this->processDamage(
                                type: 'transfer',
                                refId: $transfer->id,
                                qty: $qty,
                                stockItemId: $transfer->stock_item_id,
                                name: $transfer->name,
                                warehouseId: $transfer->warehouse_id,
                                configId: $transfer->config_id,
                                price: $transfer->purchase_price ?? 0
                            );

                            $newDamageQty = $transfer->damage_quantity + $qty;

                            $remainingQuantity =
                                ($transfer->quantity ?? 0)
                                - (
                                    ($transfer->issue_quantity ?? 0)
                                    + $newDamageQty
                                );

                            $transfer->update([
                                'damage_quantity' => $newDamageQty,
                                'remaining_quantity' => $remainingQuantity,
                            ]);
                        }
                    });
                }
            });
    }

    /**
     * Common Damage Processing
     */
    private function processDamage(
        string $type,
        int $refId,
        float $qty,
        int $stockItemId,
        string $name,
        int $warehouseId,
        int $configId,
        float $price
    ): void {

        $damageMode = $type === 'purchase' ? 'Purchase' : 'Stock-transfer';

        $attributes = [
            'config_id' => $configId,
            'warehouse_id' => $warehouseId,
            'damage_mode' => $damageMode,
        ];

        if ($type === 'purchase') {
            $attributes['purchase_item_id'] = $refId;
        } else {
            $attributes['stock_transfer_item_id'] = $refId;
        }

        $damageItem = DamageItemModel::updateOrCreate(
            $attributes,
            [
                'quantity' => $qty,
                'price' => $price,
                'purchase_price' => $price,
                'sub_total' => $price * $qty,
                'process' => 'Created'
            ]
        );

        // stock history
        $domain = UserModel::getUserDataByConfigId($configId);

        StockItemHistoryModel::openingStockQuantity(
            (object)[
                'id' => $damageItem->id,
                'stock_item_id' => $stockItemId,
                'name' => $name,
                'config_id' => $configId,
                'warehouse_id' => $warehouseId,
                'quantity' => $qty,
            ],
            'damage',
            $domain
        );

        // daily stock
        DailyStockService::maintainDailyStock(
            date: now()->toDateString(),
            field: 'damage_quantity',
            configId: $configId,
            warehouseId: $warehouseId,
            stockItemId: $stockItemId,
            quantity: $qty
        );

        // mark completed
        $damageItem->update([
            'process' => 'Completed'
        ]);
    }
}