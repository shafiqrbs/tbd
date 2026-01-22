<?php


declare(strict_types=1);

namespace App\Services\PosSales;

use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\CustomerModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemModel;

final class ProcessSingleSaleService
{
    public function process(array $input, array $domain): SalesModel
    {
        return DB::transaction(function () use ($input, $domain) {
            $saleData = $input;
            $saleData['config_id'] = $domain['config_id'];
            $saleData['sales_form'] = 'Offline-sales';
            $saleData['process'] = 'Created';
            $saleData['sales_by_id'] = $saleData['sales_by_id'] ?? $domain['user_id'];
            $saleData['inv_config'] = $domain['inv_config'];
            $saleData['invoice'] = $input['invoice'];

            $sale = SalesModel::create($saleData);

            // If invoice provided, override
            if (!empty($input['invoice'])) {
                $sale->forceFill(['invoice' => $input['invoice']])->saveQuietly();
            }

            // Customer
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
                $sale->update(['customer_id' => $customer->id]);
            }

            // Items
            $items = $input['sales_items'] ?? $input['items'] ?? [];

            foreach ($items as &$item) {
                $stock = StockItemModel::find($item['stock_item_id']);
                if (!$stock) continue;

                $item['product_id'] = $stock->id;
                $item['unit_id'] = $stock->product->unit_id;
                $item['item_name'] = $stock->name;
                $item['uom'] = $stock->uom;
            }

            SalesItemModel::insertSalesItems($sale, $items, $domain['warehouse_id']);

            return $sale;
        });
    }
}
