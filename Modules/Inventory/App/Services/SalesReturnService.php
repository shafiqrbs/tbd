<?php

namespace Modules\Inventory\App\Services;

use App\Services\DailyStockService;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Models\DamageItemModel;
use Modules\Inventory\App\Models\DamageModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseReturnItemModel;
use Modules\Inventory\App\Models\PurchaseReturnModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesReturnItemModel;
use Modules\Inventory\App\Models\SalesReturnModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;

class SalesReturnService
{
    /**
     * Process a sales return: split quantities into stock vs damage,
     * update linked purchase return, process stock history, and create damage records.
     */
    public function processReturn(int $salesReturnId, array $items, $domain): array
    {
        $salesReturn = SalesReturnModel::with('salesReturnItems')
            ->lockForUpdate()
            ->findOrFail($salesReturnId);

        // Prevent duplicate processing
        if ($salesReturn->process !== 'Created') {
            return [
                'status'  => 409,
                'message' => 'Sales return has already been processed.',
            ];
        }

        $totalQuantity = 0;
        $totalAmount = 0;
        $damageItems = [];

        // ----------------------------
        // STEP 1: UPDATE SALES RETURN ITEMS
        // ----------------------------
        foreach ($items as $item) {
            $returnItem = SalesReturnItemModel::lockForUpdate()->findOrFail($item['id']);

            $stockQty  = (float) $item['stock_entry_quantity'];
            $damageQty = (float) $item['damage_entry_quantity'];
            $totalQty  = $stockQty + $damageQty;

            if ($totalQty > $returnItem->request_quantity) {
                return [
                    'status'  => 422,
                    'message' => 'Stock + Damage must not exceed requested quantity for item ID ' . $item['id'],
                ];
            }

            $returnItem->update([
                'stock_entry_quantity'  => $stockQty,
                'damage_entry_quantity' => $damageQty,
                'quantity'              => $totalQty,
                'sub_total'             => $totalQty * $returnItem->price,
            ]);

            $totalQuantity += $totalQty;
            $totalAmount   += $totalQty * $returnItem->price;

            if ($damageQty > 0) {
                $damageItems[] = [
                    'item'     => $returnItem,
                    'quantity' => $damageQty,
                ];
            }

            // ----------------------------
            // STEP 2: UPDATE LINKED PURCHASE RETURN ITEM
            // ----------------------------
            if ($returnItem->purchase_return_item_id) {
                $purchaseReturnItem = PurchaseReturnItemModel::findOrFail($returnItem->purchase_return_item_id);

                $purchaseReturnItem->update([
                    'quantity'  => $totalQty,
                    'sub_total' => $totalQty * $purchaseReturnItem->purchase_price,
                ]);
            }
        }

        // ----------------------------
        // STEP 3: UPDATE MAIN SALES RETURN
        // ----------------------------
        $salesReturn->update([
            'quantity'  => $totalQuantity,
            'sub_total' => $totalAmount,
        ]);

        // ----------------------------
        // STEP 4: UPDATE MAIN PURCHASE RETURN
        // ----------------------------
        $purchaseReturn = null;
        if ($salesReturn->purchase_return_id) {
            $purchaseReturn = PurchaseReturnModel::with('purchaseReturnItems')
                ->lockForUpdate()
                ->findOrFail($salesReturn->purchase_return_id);

            $purchaseTotalQty = $purchaseReturn->purchaseReturnItems->sum('quantity');
            $purchaseTotalAmt = $purchaseReturn->purchaseReturnItems->sum('sub_total');

            $purchaseReturn->update([
                'quantity'  => $purchaseTotalQty,
                'sub_total' => $purchaseTotalAmt,
                'process'   => 'Approved',
            ]);
        }

        // ----------------------------
        // STEP 5: PROCESS SALES RETURN (stock & daily stock)
        // FIX: Only process stock_entry_quantity, NOT damage portion
        // ----------------------------
        $salesReturn->refresh();
        foreach ($salesReturn->salesReturnItems as $item) {
            // Skip items with zero stock entry
            if ($item->stock_entry_quantity <= 0) {
                continue;
            }

            $item->config_id = $salesReturn->config_id;

            // Create a stock-only object for stock history processing
            $stockItem = clone $item;
            $stockItem->quantity = $item->stock_entry_quantity;

            StockItemHistoryModel::openingStockQuantity(
                $stockItem,
                'sales-return',
                $domain
            );

            DailyStockService::maintainDailyStock(
                date: now()->toDateString(),
                field: 'sales_return_quantity',
                configId: $salesReturn->config_id,
                warehouseId: $item->warehouse_id,
                stockItemId: $item->stock_item_id,
                quantity: $item->stock_entry_quantity
            );

            $salesItem = SalesItemModel::find($item->sales_item_id);
            if ($salesItem) {
                $salesItem->update([
                    'return_quantity' => ($salesItem->return_quantity ?? 0) + $item->stock_entry_quantity,
                ]);
            }
        }

        // ----------------------------
        // STEP 6: PROCESS PURCHASE RETURN (stock & daily stock)
        // FIX: Only process stock portion, use atomic updates for remaining_quantity
        // ----------------------------
        if ($salesReturn->purchase_return_id && $purchaseReturn) {
            $purchaseReturn->refresh();
            foreach ($purchaseReturn->purchaseReturnItems as $item) {
                if ($item->quantity <= 0) {
                    continue;
                }

                $item->config_id = $purchaseReturn->config_id;

                StockItemHistoryModel::openingStockQuantity(
                    $item,
                    'purchase-return',
                    $domain
                );

                DailyStockService::maintainDailyStock(
                    date: now()->toDateString(),
                    field: 'purchase_return_quantity',
                    configId: $purchaseReturn->config_id,
                    warehouseId: $item->warehouse_id,
                    stockItemId: $item->stock_item_id,
                    quantity: $item->quantity
                );

                // Atomic update to prevent race conditions
                PurchaseItemModel::where('id', $item->purchase_item_id)->update([
                    'purchase_return_quantity' => DB::raw("COALESCE(purchase_return_quantity, 0) + {$item->quantity}"),
                    'remaining_quantity'       => DB::raw("COALESCE(remaining_quantity, 0) - {$item->quantity}"),
                ]);
            }
        }

        // ----------------------------
        // STEP 7: DAMAGE PROCESS
        // ----------------------------
        if (count($damageItems) > 0) {
            $this->processDamageItems($damageItems, $salesReturn, $domain);
        }

        // ----------------------------
        // FINALIZE SALES RETURN
        // ----------------------------
        $salesReturn->update([
            'process'        => 'Approved',
            'approved_by_id' => $domain['user_id'] ?? null,
        ]);

        return [
            'status'  => 200,
            'message' => 'Sales return processed successfully.',
        ];
    }

    /**
     * Create damage records for damaged return items.
     */
    private function processDamageItems(array $damageItems, SalesReturnModel $salesReturn, $domain): void
    {
        $damage = DamageModel::create([
            'config_id'     => $salesReturn->config_id,
            'created_by_id' => $domain['user_id'] ?? null,
            'quantity'      => 0,
            'sub_total'     => 0,
            'damage_type'   => 'Sales',
            'process'       => 'Created',
            'damage_mode'   => 'Damage',
        ]);

        $totalDamageQty = 0;
        $totalDamageAmt = 0;

        foreach ($damageItems as $d) {
            $returnItem = $d['item'];
            $qty        = $d['quantity'];

            $damageItem = DamageItemModel::create([
                'config_id'      => $salesReturn->config_id,
                'warehouse_id'   => $returnItem->warehouse_id,
                'damage_mode'    => 'Sales',
                'sales_item_id'  => $returnItem->sales_item_id,
                'damage_id'      => $damage->id,
                'quantity'       => $qty,
                'price'          => $returnItem->price,
                'purchase_price' => $returnItem->price,
                'sub_total'      => $qty * $returnItem->price,
                'process'        => 'Completed',
            ]);

            StockItemHistoryModel::openingStockQuantity(
                (object) [
                    'id'            => $damageItem->id,
                    'stock_item_id' => $returnItem->stock_item_id,
                    'name'          => $returnItem->item_name,
                    'config_id'     => $salesReturn->config_id,
                    'warehouse_id'  => $returnItem->warehouse_id,
                    'quantity'      => $qty,
                ],
                'damage',
                $domain
            );

            DailyStockService::maintainDailyStock(
                date: now()->toDateString(),
                field: 'damage_quantity',
                configId: $salesReturn->config_id,
                warehouseId: $returnItem->warehouse_id,
                stockItemId: $returnItem->stock_item_id,
                quantity: $qty
            );

            $totalDamageQty += $qty;
            $totalDamageAmt += $qty * $returnItem->price;
        }

        $damage->update([
            'quantity'  => $totalDamageQty,
            'sub_total' => $totalDamageAmt,
            'process'   => 'Approved',
        ]);
    }
}
