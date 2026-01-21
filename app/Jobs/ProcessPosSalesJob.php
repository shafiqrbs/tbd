<?php
namespace App\Jobs;

use App\Enums\PosSaleProcess;
use App\Services\DailyStockService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Accounting\App\Models\AccountJournalModel;
use Modules\Core\App\Models\CustomerModel;
use Modules\Inventory\App\Models\PosSaleModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;

class ProcessPosSalesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $syncId;
    public array $domain;

    public function __construct(int $syncId, array $domain)
    {
        $this->syncId = $syncId;
        $this->domain = $domain;
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $sync = PosSaleModel::lockForUpdate()->find($this->syncId);

            if (!$sync || $sync->process !== PosSaleProcess::PENDING) {
                Log::warning("POS sync not pending or missing", ['sync_id' => $this->syncId]);
                return;
            }

            $sync->update(['process' => PosSaleProcess::PROCESSING]);
        });

        $sync = PosSaleModel::find($this->syncId);
        if (!$sync) return;

        try {
            $salesData = collect($sync->content);

            $salesData->chunk(100)->each(function ($chunk) {
                foreach ($chunk as $sale) {
                    try {
                        $sales = $this->processSingleSale($sale, $this->domain);
                        $this->approveSales($sales, $this->domain);

                    } catch (\Throwable $e) {
                        Log::error('Failed to process single sale', [
                            'error' => $e->getMessage(),
                            'sale' => $sale
                        ]);
                    }
                }
            });

            $sync->update(['process' => PosSaleProcess::COMPLETED]);
        } catch (\Throwable $e) {
            $sync->update(['process' => PosSaleProcess::FAILED]);
            Log::error('POS Sync Failed', [
                'sync_id' => $this->syncId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function processSingleSale(array $sale, array $domain): ?SalesModel
    {
        return DB::transaction(function () use ($sale, $domain) {
            $input = $sale;
            $input['config_id'] = $domain['config_id'];
            $input['sales_form'] = 'External-sales';
            $input['process'] = 'Created';
            $input['sales_by_id'] = $input['sales_by_id'] ?? $domain['user_id'];
            $input['inv_config'] = $domain['inv_config'];
            $input['invoice'] = $sale['invoice'];

            // Create Sales Record
            $sales = SalesModel::create($input);

            if (!empty($sale['invoice'])) {
                $sales->forceFill([
                    'invoice' => $sale['invoice'],
                ])->saveQuietly();
            }

            // Customer handling
            $customer = null;
            if (isset($input['customer_name'], $input['customer_mobile'])) {
                $customer = CustomerModel::uniqueCustomerCheck($domain['domain_id'], $input['customer_mobile'], $input['customer_name'])
                    ?? CustomerModel::insertSalesCustomer($domain, $input);
            } elseif (!empty($input['customer_id'])) {
                $customer = CustomerModel::find($input['customer_id']);
            } else {
                $customer = CustomerModel::where('is_default_customer', 1)
                    ->where('domain_id', $domain['domain_id'])
                    ->first();
            }

            if ($customer) {
                $sales->update(['customer_id' => $customer->id]);
            }

            // Process Sales Items
            $items = $input['sales_items'] ?? $input['items'] ?? [];
            foreach ($items as &$item) {
                $stock = StockItemModel::find($item['stock_item_id']);
                if (!$stock) continue;

                $item['product_id'] = $stock->id;
                $item['unit_id'] = $stock->product->unit_id;
                $item['item_name'] = $stock->name;
                $item['uom'] = $stock->uom;
            }

            if (!empty($items)) {
                SalesItemModel::insertSalesItems($sales, $items, $domain['warehouse_id']);
            }

            return $sales;
        });
    }

    private function approveSales(SalesModel $sales, array $domain): void
    {
        DB::transaction(function () use ($sales, $domain) {
            $sales->refresh();
            $sales->update(['approved_by_id' => $domain['user_id'], 'process' => 'Approved']);

            foreach ($sales->salesItems as $item) {
                StockItemHistoryModel::openingStockQuantity($item, 'sales', $domain);

                DailyStockService::maintainDailyStock(
                    date: now()->format('Y-m-d'),
                    field: 'sales_quantity',
                    configId: $domain['config_id'],
                    warehouseId: $item->warehouse_id,
                    stockItemId: $item->stock_item_id,
                    quantity: $item->quantity
                );

                if ($item->purchase_item_id) {
                    PurchaseItemModel::updateSalesQuantity($item->purchase_item_id, $item->quantity);
                }
            }

            AccountJournalModel::insertSalesAccountJournal($domain, $sales->id);
        });
    }
}
