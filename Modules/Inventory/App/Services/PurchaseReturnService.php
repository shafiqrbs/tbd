<?php

namespace Modules\Inventory\App\Services;

use App\Services\DailyStockService;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseReturnModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesReturnItemModel;
use Modules\Inventory\App\Models\SalesReturnModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;

class PurchaseReturnService
{
    /**
     * Approve a purchase return directly (deduct stock immediately).
     */
    public function approvePurchase(int $id, $domain): array
    {
        $purchaseReturn = PurchaseReturnModel::with('purchaseReturnItems')
            ->lockForUpdate()
            ->findOrFail($id);

        // Prevent duplicate processing
        if ($purchaseReturn->process !== 'Created') {
            return [
                'status'  => 409,
                'message' => 'Purchase return has already been processed.',
            ];
        }

        $purchaseReturn->purchaseReturnItems->each(function ($item) use ($purchaseReturn, $domain) {
            $item->config_id = $purchaseReturn->config_id;

            StockItemHistoryModel::openingStockQuantity($item, 'purchase-return', $domain);

            DailyStockService::maintainDailyStock(
                date: now()->toDateString(),
                field: 'purchase_return_quantity',
                configId: $purchaseReturn->config_id,
                warehouseId: $item->warehouse_id ?? $domain['warehouse_id'],
                stockItemId: $item->stock_item_id,
                quantity: $item->quantity
            );

            // Atomic increment instead of overwrite
            PurchaseItemModel::where('id', $item->purchase_item_id)->update([
                'purchase_return_quantity' => DB::raw("COALESCE(purchase_return_quantity, 0) + {$item->quantity}"),
                'remaining_quantity'       => DB::raw("COALESCE(remaining_quantity, 0) - {$item->quantity}"),
            ]);
        });

        $purchaseReturn->update([
            'process'        => 'Approved',
            'approved_by_id' => $domain['user_id'],
        ]);

        return [
            'status'  => 200,
            'message' => 'Purchase return approved successfully.',
        ];
    }

    /**
     * Send purchase return to vendor by creating a vendor sales return.
     */
    public function approveVendor(int $id, $domain): array
    {
        $purchaseReturn = PurchaseReturnModel::with(['vendor', 'purchaseReturnItems'])
            ->lockForUpdate()
            ->findOrFail($id);

        // Prevent duplicate processing
        if ($purchaseReturn->process !== 'Created') {
            return [
                'status'  => 409,
                'message' => 'Purchase return has already been processed.',
            ];
        }

        $purchaseReturnItems = $purchaseReturn->purchaseReturnItems;

        if ($purchaseReturnItems->isEmpty()) {
            return [
                'status'  => 422,
                'message' => 'No items found for this purchase return.',
            ];
        }

        $purchaseReturnStockItemIds = $purchaseReturnItems->pluck('stock_item_id')->toArray();

        // Load all stock items in ONE query
        $stockItems = StockItemModel::whereIn('id', $purchaseReturnStockItemIds)
            ->with('parentStock')
            ->get()
            ->keyBy('id');

        if (!$purchaseReturn->vendor || !$purchaseReturn->vendor->customer_id) {
            return [
                'status'  => 422,
                'message' => 'Vendor has no linked customer. Cannot create sales return.',
            ];
        }

        $salesReturnData = [
            'customer_id'        => $purchaseReturn->vendor->customer_id,
            'created_by_id'      => $domain['user_id'],
            'sub_total'          => $purchaseReturn->sub_total,
            'process'            => 'Created',
            'purchase_return_id' => $purchaseReturn->id,
            'quantity'           => $purchaseReturn->quantity,
        ];

        $salesReturn = SalesReturnModel::create($salesReturnData);

        $salesReturnItemData = [];
        $salesConfig = null;

        foreach ($purchaseReturnItems as $item) {
            $purchaseStock = $stockItems[$item->stock_item_id] ?? null;

            if (!$purchaseStock) {
                throw new \Exception("Stock item not found for purchase return item {$item->id}");
            }

            $salesStock = $purchaseStock->parentStock;

            if (!$salesStock) {
                throw new \Exception("Parent stock not found for item {$item->id}");
            }

            $parentSalesItemId = PurchaseItemModel::where('id', $item->purchase_item_id)
                ->value('parent_sales_item_id');
            $findParentSalesItem = SalesItemModel::find($parentSalesItemId);

            if (!$findParentSalesItem) {
                throw new \Exception("Parent sales item not found for item {$item->id}");
            }

            $salesConfig = $salesStock->config_id;

            $salesReturnItemData[] = [
                'item_name'               => $salesStock->name,
                'uom'                     => $salesStock->uom,
                'stock_item_id'           => $salesStock->id,
                'request_quantity'        => $item->quantity,
                'stock_entry_quantity'    => $item->quantity,
                'quantity'                => $item->quantity,
                'damage_entry_quantity'   => 0,
                'price'                   => $item->purchase_price,
                'sub_total'               => $item->sub_total,
                'warehouse_id'            => $findParentSalesItem->warehouse_id,
                'sales_item_id'           => $parentSalesItemId,
                'purchase_return_item_id' => $item->id,
                'sales_return_id'         => $salesReturn->id,
                'status'                  => 1,
            ];
        }

        SalesReturnItemModel::insert($salesReturnItemData);

        $salesReturn->update(['config_id' => $salesConfig]);

        $purchaseReturn->update([
            'process'        => 'Send-to-vendor',
            'approved_by_id' => $domain['user_id'],
        ]);

        return [
            'status'  => 200,
            'message' => 'Purchase return sent to vendor successfully.',
        ];
    }
}
