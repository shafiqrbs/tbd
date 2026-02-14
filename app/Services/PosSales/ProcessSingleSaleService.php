<?php
declare(strict_types=1);

namespace App\Services\PosSales;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\TransactionModeModel;
use Modules\Core\App\Models\CustomerModel;
use Modules\Inventory\App\Models\PaymentTransactionModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemModel;

final class ProcessSingleSaleService
{
    public function process(array $input, array $domain): SalesModel
    {
        return DB::transaction(function () use ($input, $domain) {

            $input = $this->normalizeKeys($input);

            $saleData = $input;
            $saleData['config_id'] = $domain['config_id'];
            $saleData['sales_form'] = 'Offline-sales';
            $saleData['process'] = 'Created';
            $saleData['sales_by_id'] = $saleData['sales_by_id'] ?? $domain['user_id'];
            $saleData['inv_config'] = $domain['inv_config'];
            $saleData['invoice'] = $input['invoice'];

            $sale = SalesModel::create($saleData);

            // Force invoice if exists
            if (!empty($input['invoice'])) {
                $sale->forceFill(['invoice' => $input['invoice']])->saveQuietly();
            }

            // CUSTOMER
            $customer = null;

            if (isset($input['customer_name'], $input['customer_mobile'])) {
                $customer = CustomerModel::uniqueCustomerCheck(
                    $domain['domain_id'],
                    $input['customer_mobile'],
                    $input['customer_name']
                )
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

            // SALES ITEMS
            $items = $input['sales_items'] ?? $input['items'] ?? [];

            // Group duplicate items
            $items = collect($items)
                ->groupBy('stock_item_id')
                ->map(function ($group) {
                    $first = $group->first();
                    $first['quantity'] = $group->sum('quantity');
                    $first['sub_total'] = $group->sum('sub_total');
                    return $first;
                })
                ->values()
                ->toArray();

            foreach ($items as $index => $item) {
                $stock = StockItemModel::find($item['stock_item_id']);
                if (!$stock) continue;

                $items[$index]['product_id'] = $stock->id;
                $items[$index]['unit_id'] = $stock->product->unit_id;
                $items[$index]['item_name'] = $stock->name;
                $items[$index]['uom'] = $stock->uom;
            }

            SalesItemModel::insertSalesItems(
                $sale,
                $items,
                $domain['warehouse_id']
            );

            // PAYMENTS
            $payments = $input['payments'] ?? [];

            foreach ($payments as $payment) {

                if (
                    empty($payment['transaction_mode_id']) ||
                    empty($payment['amount']) ||
                    $payment['amount'] <= 0
                ) {
                    continue;
                }

                $transactionMode = TransactionModeModel::find($payment['transaction_mode_id']);
                if (!$transactionMode) continue;

                PaymentTransactionModel::updateOrCreate(
                    [
                        'sale_id' => $sale->id,
                        'transaction_mode_id' => $transactionMode->id,
                    ],
                    [
                        'amount' => (float)$payment['amount'],
                        'config_id' => $domain['config_id']
                    ]
                );
            }

            // FINAL PAYMENT UPDATE
            $paid = PaymentTransactionModel::where('sale_id', $sale->id)
                ->sum('amount');

            $sale->update([
                'payment' => round($paid, 2),
            ]);

            return $sale;
        });
    }

    private function normalizeKeys(array $input): array
    {
        $map = [
            'customerName' => 'customer_name',
            'customerMobile' => 'customer_mobile',
            'salesItems' => 'sales_items',
        ];

        foreach ($map as $from => $to) {
            if (isset($input[$from]) && !isset($input[$to])) {
                $input[$to] = $input[$from];
            }
        }

        return $input;
    }
}

