<?php


declare(strict_types=1);

namespace App\Services\PosSales;

use App\Services\DailyStockService;
use Modules\Accounting\App\Models\AccountJournalModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;

final class SaleApprovalService
{
    public function approve(SalesModel $sale, array $domain): void
    {
        $sale->refresh();

        $sale->update([
            'approved_by_id' => $domain['user_id'],
            'process' => 'Approved'
        ]);

        foreach ($sale->salesItems as $item) {
            StockItemHistoryModel::openingStockQuantity($item, 'sales', $domain);

            DailyStockService::maintainDailyStock(
                date: now()->format('Y-m-d'),
                field: 'sales_quantity',
                configId: $domain['config_id'],
                warehouseId: $item->warehouse_id,
                stockItemId: $item->stock_item_id,
                quantity: (int)$item->quantity
            );
            if ($item->purchase_item_id) {
                PurchaseItemModel::updateSalesQuantity($item->purchase_item_id, $item->quantity);
            }
        }
        AccountJournalModel::insertPosSalesAccountJournal($domain, $sale->id);
    }
}
