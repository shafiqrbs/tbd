<?php

namespace Modules\Inventory\App\Models;

use App\Helpers\DateHelper;
use App\Helpers\NumberHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Ramsey\Collection\Collection;
use function Symfony\Component\Form\ChoiceList\groupBy;

class ReportModel extends Model
{
    use HasFactory;

    protected $table = 'inv_sales';
    public $timestamps = true;
    protected $guarded = ['id'];


    public static function dailySummaryReport($inventoryConfigId, $request =[])
    {

        // 1. Fetch Sales Data Grouped By Date, Product, Customer (and optional Warehouse)

        if (isset($request['start_date']) && !empty($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
        }


        $topSalesItem = DB::table('inv_sales as s')->where('s.config_id', $inventoryConfigId)
            ->where('s.process', 'Approved')
            ->join('inv_sales_item as si', 's.id', '=', 'si.sale_id')
            ->selectRaw('ROUND(SUM(si.quantity)) as totalQuantity ,SUM(si.sub_total)  as totalAmount , SUM(si.sub_total)  as salesPrice ,  si.name as name')
            ->groupBy('si.stock_item_id')
            ->orderBy('quantity','DESC')
            ->limit(10)
            ->when($start_date, function ($q) use ($start_date) { $q->whereDate('s.created_at', '>=', $start_date); })
            ->when($end_date, function ($q) use ($end_date) { $q->whereDate('s.created_at', '<=', $end_date); })
            ->get();

        $sales = DB::table('inv_sales as s')
            ->where('s.config_id', $inventoryConfigId)
            ->where('s.process', 'Approved')
            ->when($start_date, function ($q) use ($start_date) { $q->whereDate('s.created_at', '>=', $start_date); })
            ->when($end_date, function ($q) use ($end_date) { $q->whereDate('s.created_at', '<=', $end_date); })
            ->selectRaw('
                COUNT(s.id) as totalInvoices,
                SUM(s.sub_total) as totalSales,
                SUM(s.total) as total,
                SUM(s.payment) as totalPayment,
                SUM(s.total - s.payment) as totalDue,
                SUM(s.discount) as totalDiscount
            ')->first();


        $purchase = DB::table('inv_purchase as s')
            ->where('s.config_id', $inventoryConfigId)
            ->where('s.process', 'Approved')
            ->selectRaw('COUNT(s.id) as totalInvoices,SUM(s.sub_total) as totalPurchase,SUM(s.total) as total,SUM(s.payment) as totalPayment,(SUM(s.total) - SUM(s.payment)) as totalDue, SUM(s.discount) as totalDiscount')
            ->when($start_date, function ($q) use ($start_date) { $q->whereDate('s.created_at', '>=', $start_date); })
            ->when($end_date, function ($q) use ($end_date) { $q->whereDate('s.created_at', '<=', $end_date); })
            ->first();

        $transactionModes = DB::table('inv_sales as s')
            ->where('s.config_id', $inventoryConfigId)
            ->where('s.process', 'Approved')
            ->when($start_date, function ($q) use ($start_date) { $q->whereDate('s.created_at', '>=', $start_date); })
            ->when($end_date, function ($q) use ($end_date) { $q->whereDate('s.created_at', '<=', $end_date); })
            ->join('inv_payment_transaction as pt', 's.id', '=', 'pt.sale_id')
            ->join('acc_transaction_mode as tm', 'tm.id', '=', 'pt.transaction_mode_id')
            ->selectRaw('tm.name as method,SUM(pt.amount) as amount')
            ->groupBy('pt.transaction_mode_id')
            ->get();

        $method = DB::table('inv_sales as s')->where('s.config_id', $inventoryConfigId)
            ->where('s.process', 'Approved')
            ->when($start_date, function ($q) use ($start_date) { $q->whereDate('s.created_at', '>=', $start_date); })
            ->when($end_date, function ($q) use ($end_date) { $q->whereDate('s.created_at', '<=', $end_date); })
            ->join('inv_payment_transaction as pt', 's.id', '=', 'pt.sale_id')
            ->join('acc_transaction_mode as tm', 'tm.id', '=', 'pt.transaction_mode_id')
            ->join('acc_setting as acs', 'acs.id', '=', 'tm.method_id')
            ->selectRaw('acs.name as name, SUM(pt.amount) as amount, 0 as count')
            ->groupBy('tm.method_id')
            ->get();

        $damage = DB::table('inv_damage_item as s')->where('s.config_id', $inventoryConfigId)
            ->when($start_date, function ($q) use ($start_date) { $q->whereDate('s.created_at', '>=', $start_date); })
            ->when($end_date, function ($q) use ($end_date) { $q->whereDate('s.created_at', '<=', $end_date); })
            ->selectRaw('SUM(s.sub_total) as sub_total')
            ->first();

        $salesReturn = DB::table('inv_sales_return_item as s')
            ->join('inv_sales_return as sr', 'sr.id', '=', 's.sales_return_id')
            ->where('sr.config_id', $inventoryConfigId)
            ->where('sr.process', 'Approved')
            ->when($start_date, function ($q) use ($start_date) { $q->whereDate('sr.updated_at', '>=', $start_date); })
            ->when($end_date, function ($q) use ($end_date) { $q->whereDate('sr.updated_at', '<=', $end_date); })
            ->selectRaw('SUM(s.sub_total) as sub_total')
            ->first();

        $purchaseReturn = DB::table('inv_purchase_return_item as s')
            ->join('inv_purchase_return as sr', 'sr.id', '=', 's.purchase_return_id')
            ->where('sr.config_id', $inventoryConfigId)
            ->where('sr.process', 'Approved')
            ->when($start_date, function ($q) use ($start_date) { $q->whereDate('sr.updated_at', '>=', $start_date); })
            ->when($end_date, function ($q) use ($end_date) { $q->whereDate('sr.updated_at', '<=', $end_date); })
            ->selectRaw('SUM(s.sub_total) as sub_total')
            ->first();


        $prevDateSub = DB::table('inv_stock_item_history')
            ->selectRaw('MAX(id) as id')
            ->where('config_id', $inventoryConfigId)
            ->whereDate('created_at', '<', $start_date)
            ->groupBy('stock_item_id');

        $totalOpeningBalance = DB::table('inv_stock_item_history as h')
            ->joinSub($prevDateSub, 'pd', function ($join) { $join->on('h.id', '=', 'pd.id');})
            ->sum('h.closing_balance');


        $totalOpeningQuantity = DB::table('inv_stock_item_history as h')
            ->joinSub($prevDateSub, 'pd', function ($join) { $join->on('h.id', '=', 'pd.id');})
            ->sum('h.opening_quantity');

        $todayOpening = DB::table('inv_stock_item_history')
            ->where('config_id', $inventoryConfigId)
            ->where('mode', 'opening')
            ->whereDate('created_at', $start_date)
            ->sum('closing_balance');

        $stocks = [
            'totalOpeningQuantity' => $totalOpeningQuantity,
            'totalOpeningBalance' => $totalOpeningBalance,
            'totalOpeningBalance_format' => NumberHelper::formatAmount($totalOpeningBalance),
        ];

        $salesOverview = [];
        $salesOverview['totalInvoices'] = $sales->totalInvoices;
        $salesOverview['totalSales'] = $sales->totalSales;
        $salesOverview['total'] = $sales->total;
        $salesOverview['totalPurchase'] = $purchase->totalPurchase;
        $salesOverview['totalDiscount'] = $sales->totalDiscount;
        $salesOverview['totalOpeningBalance'] = $totalOpeningBalance;
        $salesOverview['totalStock'] = $totalOpeningBalance + $purchase->totalPurchase;
        $salesOverview['wastage'] = $damage->sub_total;
        $salesOverview['return'] = $purchaseReturn->sub_total;
        $salesOverview['today_opening'] = $todayOpening;
        $salesOverview['totalClosingBalance'] = (($totalOpeningBalance + $purchase->totalPurchase)-($sales->totalSales+$damage->sub_total+$purchaseReturn->sub_total));

        $data = [
            'sales' => $salesOverview,
            'purchase' => $purchase ,
            'methods' => $method,
            'transactionModes' => $transactionModes,
            'topSalesItem' => $topSalesItem,
            'damage' => $damage->sub_total,
            'salesReturn' => $salesReturn->sub_total,
            'stocks' => $stocks
        ];
        return $data;


    }

    public function formatAmount($amount)
    {
        return rtrim(rtrim(number_format($amount, 2), '0'), '.');
    }

    public static function dailySalesReport(array $params, int $domain_id, int $inventoryConfigId)
    {

        $startDate = $dates['first_date'] ?? null;
        $endDate = $dates['last_date'] ?? null;
        $customerId = $params['customer_id'] ?? null;
        $warehouseId = $params['warehouse_id'] ?? null;
        $customerGroup = $params['customer_group'] ?? null;
        $groupByWarehouse = !empty($warehouseId);

        // 2. Fetch Sales Data Grouped By Date, Product, Customer (and optional Warehouse)
        $sales = DB::table('inv_sales as s')
            ->join('inv_sales_item as si', 's.id', '=', 'si.sale_id')
            ->join('cor_customers as c', 's.customer_id', '=', 'c.id')
            ->join('inv_stock as stock', 'si.stock_item_id', '=', 'stock.id')
            ->selectRaw('
            DATE(s.created_at) as date,
            si.stock_item_id,
            stock.name as product_name,
            c.name as customer_name' .
                ($groupByWarehouse ? ', si.warehouse_id' : '') . ',
            SUM(si.quantity) as total_quantity,
            SUM(si.sub_total) as total_amount
        ')
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('s.created_at', [$startDate, $endDate]))
            ->when($customerId, fn($q) => $q->where('s.customer_id', $customerId))
            ->when($warehouseId, fn($q) => $q->where('si.warehouse_id', $warehouseId))
            ->when($customerGroup, fn($q) => $q->where('c.customer_group', $customerGroup))
            ->where('s.config_id', $inventoryConfigId)
            ->groupBy(
                'date',
                'si.stock_item_id',
                'stock.name',
                'c.name',
                ...($groupByWarehouse ? ['si.warehouse_id'] : [])
            )
            ->orderBy('date')
            ->get();
        return $report;
    }


    /**
     * Fetch vendor-wise purchases with eager loaded items.
     * $filters array can contain 'vendor_id' or other filters.
     */
    public static function purchaseStockReport($configId,array $request = [])
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        $entities = PurchaseItemModel::where('inv_purchase.config_id',$configId)
            ->whereNotNull('inv_purchase.approved_by_id')
            ->join('inv_purchase', 'inv_purchase.id', '=', 'inv_purchase_item.purchase_id')
            ->join('inv_stock', 'inv_stock.id', '=', 'inv_purchase_item.stock_item_id')
            ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
            ->leftJoin('inv_category', 'inv_category.id', '=', 'inv_product.category_id')
            ->leftJoin('cor_warehouses', 'cor_warehouses.id', '=', 'inv_purchase_item.warehouse_id')
            ->leftJoin('inv_particular', 'inv_particular.id', '=', 'inv_product.unit_id')
            ->select([
                'inv_purchase_item.id',
                DB::raw('DATE_FORMAT(inv_purchase_item.created_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(inv_purchase_item.expired_date, "%d-%m-%Y") as expired_date'),
                'inv_purchase_item.purchase_id',
                'inv_purchase.invoice',
                'inv_purchase.grn',
                'inv_stock.name as item_name',
                'inv_category.name as category_name',
                'inv_purchase_item.quantity as quantity',
                'inv_purchase_item.opening_quantity as opening_quantity',
                'inv_purchase_item.sales_quantity as sales_quantity',
                'inv_purchase_item.sales_return_quantity as sales_return_quantity',
                'inv_purchase_item.purchase_return_quantity as purchase_return_quantity',
                'inv_purchase_item.damage_quantity as damage_quantity',
                'inv_purchase_item.bonus_quantity as bonus_quantity',
                'inv_purchase_item.remaining_quantity as remaining_quantity',
                'inv_purchase_item.warehouse_transfer_quantity as warehouse_transfer_quantity',
                'inv_purchase_item.purchase_price',
                'inv_purchase_item.sales_price',
                'inv_purchase_item.sub_total',
                'inv_purchase_item.bonus_quantity',
                'inv_purchase_item.parent_sales_item_id',
                'inv_particular.name as unit_name',
                'cor_warehouses.name as warehouse_name',
                'cor_warehouses.location as warehouse_location',
                'cor_warehouses.id as warehouse_id',
            ]);

        $total = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('inv_purchase.id', 'DESC')
            ->get();
        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }

    /**
     * Fetch vendor-wise purchases with eager loaded items.
     * $filters array can contain 'vendor_id' or other filters.
     */
    public static function categorySummaryReport($inventoryConfigId,array $request = [])
    {

        if (isset($request['start_date']) && !empty($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
        }

        $categories = StockItemModel::query()
            ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
            ->leftJoin('inv_category', 'inv_category.id', '=', 'inv_product.category_id')
            ->where('inv_product.config_id', $inventoryConfigId)
            ->where('inv_category.status', 1)
            ->select([
                'inv_category.id',
                'inv_category.name',
                'inv_category.slug',
                DB::raw('COUNT(inv_stock.id) as item')
            ])
            ->groupBy(
                'inv_category.id',
                'inv_category.name',
                'inv_category.slug'
            )
            ->orderBy('inv_category.id');

        $categories =  $categories->get();


        $sales = DB::table('inv_sales_item')
            ->where('s.config_id', $inventoryConfigId)
            ->where('s.process', 'Approved')
            ->join('inv_sales as s', 's.id', '=', 'inv_sales_item.sale_id')
            ->join('inv_stock', 'inv_stock.id', '=', 'inv_sales_item.stock_item_id')
            ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
            ->join('inv_category', 'inv_category.id', '=', 'inv_product.category_id')
            ->selectRaw('inv_category.id as id , inv_category.name as name')
            ->selectRaw('ROUND(COALESCE(SUM(inv_sales_item.sub_total), 0), 2) as total')
            ->when($start_date, function ($q) use ($start_date) { $q->whereDate('s.updated_at', '>=', $start_date); })
            ->when($end_date, function ($q) use ($end_date) { $q->whereDate('s.updated_at', '<=', $end_date); })
            ->groupBy('inv_category.id')
            ->get();

        $purchase = DB::table('inv_purchase_item as s')
            ->where('s.config_id', $inventoryConfigId)
            ->whereNotNull('s.approved_by_id')
            ->join('inv_stock', 'inv_stock.id', '=', 's.stock_item_id')
            ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
            ->join('inv_category', 'inv_category.id', '=', 'inv_product.category_id')
            ->selectRaw('inv_category.id as id , inv_category.name as name')
            ->selectRaw('ROUND(COALESCE(SUM(s.sub_total), 0), 2) as total')
            ->when($start_date, function ($q) use ($start_date) { $q->whereDate('s.updated_at', '>=', $start_date); })
            ->when($end_date, function ($q) use ($end_date) { $q->whereDate('s.updated_at', '<=', $end_date); })
            ->groupBy('inv_category.id')
            ->get();

        $damage = DB::table('inv_damage_item as s')->where('s.config_id', $inventoryConfigId)
            ->join('inv_stock', 'inv_stock.id', '=', 's.stock_item_id')
            ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
            ->join('inv_category', 'inv_category.id', '=', 'inv_product.category_id')
            ->when($start_date, function ($q) use ($start_date) { $q->whereDate('s.updated_at', '>=', $start_date); })
            ->when($end_date, function ($q) use ($end_date) { $q->whereDate('s.updated_at', '<=', $end_date); })
            ->selectRaw('inv_category.id as id , inv_category.name as name')
            ->selectRaw('ROUND(COALESCE(SUM(s.sub_total), 0), 2) as total')
            ->groupBy('inv_category.id')
            ->get();

        $salesReturn = DB::table('inv_sales_return_item as s')
            ->join('inv_sales_return as sr', 'sr.id', '=', 's.sales_return_id')
            ->join('inv_stock', 'inv_stock.id', '=', 's.stock_item_id')
            ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
            ->join('inv_category', 'inv_category.id', '=', 'inv_product.category_id')
            ->where('sr.config_id', $inventoryConfigId)
            ->where('sr.process', 'Approved')
            ->when($start_date, function ($q) use ($start_date) { $q->whereDate('sr.updated_at', '>=', $start_date); })
            ->when($end_date, function ($q) use ($end_date) { $q->whereDate('sr.updated_at', '<=', $end_date); })
            ->selectRaw('inv_category.id as id , inv_category.name as name')
            ->selectRaw('ROUND(COALESCE(SUM(s.sub_total), 0), 2) as total')
            ->groupBy('inv_category.id')
            ->get();

        $purchaseReturn = DB::table('inv_purchase_return_item as s')
            ->join('inv_purchase_return as sr', 'sr.id', '=', 's.purchase_return_id')
            ->join('inv_stock', 'inv_stock.id', '=', 's.stock_item_id')
            ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
            ->join('inv_category', 'inv_category.id', '=', 'inv_product.category_id')
            ->where('sr.config_id', $inventoryConfigId)
            ->where('sr.process', 'Approved')
            ->when($start_date, function ($q) use ($start_date) { $q->whereDate('sr.updated_at', '>=', $start_date); })
            ->when($end_date, function ($q) use ($end_date) { $q->whereDate('sr.updated_at', '<=', $end_date); })
            ->selectRaw('inv_category.id as id , inv_category.name as name')
            ->selectRaw('ROUND(COALESCE(SUM(s.sub_total), 0), 2) as total')
            ->groupBy('inv_category.id')
            ->get();

        $prevDateSub = DB::table('inv_stock_item_history')
            ->selectRaw('MAX(id) as id')
            ->where('config_id', $inventoryConfigId)
            ->whereDate('created_at', '<', $start_date)
            ->groupBy('stock_item_id');

        $categoryWiseOpening = DB::table('inv_stock_item_history as h')
            ->joinSub($prevDateSub, 'pd', function ($join) {
                $join->on('h.id', '=', 'pd.id');
            })
            ->join('inv_stock as s', 's.id', '=', 'h.stock_item_id')
            ->join('inv_product as p', 'p.id', '=', 's.product_id')
            ->join('inv_category as c', 'c.id', '=', 'p.category_id')
            ->select(
                'c.id as category_id',
                'c.name as category_name',
                DB::raw('ROUND(COALESCE(SUM(h.closing_balance),0),2) as opening')
            )
            ->groupBy('c.id', 'c.name')
            ->get();

        // Convert to keyed arrays for easy lookup
        $salesData = $sales->keyBy('id')->toArray();
        $purchaseData = $purchase->keyBy('id')->toArray();
        $damageData = $damage->keyBy('id')->toArray();
        $salesReturnData = $salesReturn->keyBy('id')->toArray();
        $purchaseReturnData = $purchaseReturn->keyBy('id')->toArray();
        $openingData = $categoryWiseOpening->keyBy('category_id')->toArray();


        $results = [];

        foreach ($categories as $category) {

            $categoryId = $category->id;

            // Get values or default to 0
            $opening = $openingData[$categoryId]->opening ?? 0;
            $sale = $salesData[$categoryId]->total ?? 0;
            $purch = $purchaseData[$categoryId]->total ?? 0;
            $dmg = $damageData[$categoryId]->total ?? 0;
            $salesRet = $salesReturnData[$categoryId]->total ?? 0;
            $purchaseRet = $purchaseReturnData[$categoryId]->total ?? 0;

            // Calculate closing stock

            $receive = ($opening + $purch + $salesRet);
            $issue = ($sale + $purchaseRet + $dmg);
            $closing = (($opening + $purch + $salesRet) - ($sale + $purchaseRet + $dmg));

            $results[] = [
                'id' => $categoryId,
                'name' => $category->name,
                'slug' => $category->slug,
                'items' => $category->item,
                'opening_balance' => round($opening, 2),
                'receive_amount' => round($receive, 2),
                'issue_amount' => round($issue, 2),
                'closing_balance' => round($closing, 2),
            ];
        }
        $data = array('count' => 0, 'entities' => $results);
        return $data;

    }

    /**
     * Fetch vendor-wise purchases with eager loaded items.
     * $filters array can contain 'vendor_id' or other filters.
     */
    public static function damageItemReport($configId,array $request = [])
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        $entities = DamageItemModel::where('inv_damage_item.config_id',$configId)
            ->join('inv_purchase_item', 'inv_purchase_item.id', '=', 'inv_damage_item.purchase_item_id')
            ->join('inv_purchase', 'inv_purchase.id', '=', 'inv_purchase_item.purchase_id')
            ->join('inv_stock', 'inv_stock.id', '=', 'inv_damage_item.stock_item_id')
            ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
            ->leftJoin('inv_category', 'inv_category.id', '=', 'inv_product.category_id')
            ->leftJoin('cor_warehouses', 'cor_warehouses.id', '=', 'inv_purchase_item.warehouse_id')
            ->leftJoin('inv_particular', 'inv_particular.id', '=', 'inv_product.unit_id')
            ->select([
                'inv_damage_item.id',
                DB::raw('DATE_FORMAT(inv_purchase_item.created_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(inv_purchase_item.expired_date, "%d-%m-%Y") as expired_date'),
                'inv_purchase_item.purchase_id',
                'inv_purchase.grn',
                'inv_purchase.invoice',
                'inv_stock.name as item_name',
                'inv_category.name as category_name',
                'inv_damage_item.quantity as quantity',
                'inv_damage_item.price',
                'inv_purchase_item.sub_total',
                'inv_particular.name as unit_name',
                'cor_warehouses.name as warehouse_name',
            ]);

        $total = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('inv_damage_item.id', 'DESC') // fixed
            ->get();
        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }


}
