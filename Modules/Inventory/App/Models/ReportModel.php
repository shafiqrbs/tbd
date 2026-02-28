<?php

namespace Modules\Inventory\App\Models;

use App\Helpers\DateHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Ramsey\Collection\Collection;

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
       // dd($start_date,$end_date);

        $topSalesItem = DB::table('inv_sales as s')->where('s.config_id', $inventoryConfigId)
            ->join('inv_sales_item as si', 's.id', '=', 'si.sale_id')
            ->selectRaw('ROUND(SUM(si.quantity)) as totalQuantity ,SUM(si.sub_total)  as totalAmount , SUM(si.sub_total)  as salesPrice ,  si.name as name')
            ->groupBy('si.stock_item_id')
            ->orderBy('quantity','DESC')
            ->limit(10)
            ->when($start_date && $end_date, fn($q) => $q->whereBetween('s.created_at', [$start_date, $end_date]))
            ->get();

        $sales = DB::table('inv_sales as s')->where('s.config_id', $inventoryConfigId)
            ->selectRaw('ROUND(COUNT(s.id)) as totalInvoices,ROUND(SUM(s.sub_total)) as totalSales,ROUND(SUM(s.total)) as total,ROUND(SUM(s.payment)) as totalPayment,ROUND(SUM(s.total) - SUM(s.payment)) as totalDue, ROUND(SUM(s.discount)) as totalDiscount')
            ->when($start_date && $end_date, fn($q) => $q->whereBetween('s.created_at', [$start_date, $end_date]))
            ->first();

        $purchase = DB::table('inv_purchase as s')->where('s.config_id', $inventoryConfigId)
            ->selectRaw('COUNT(s.id) as totalInvoices,SUM(s.sub_total) as totalPurchase,SUM(s.total) as total,SUM(s.payment) as totalPayment,(SUM(s.total) - SUM(s.payment)) as totalDue, SUM(s.discount) as totalDiscount')
            ->when($start_date && $end_date, fn($q) => $q->whereBetween('s.created_at', [$start_date, $end_date]))
            ->get();

        $transactionModes = DB::table('inv_sales as s')->where('s.config_id', $inventoryConfigId)
           // ->when($start_date && $end_date, fn($q) => $q->whereBetween('s.created_at', [$start_date, $end_date]))
            ->join('inv_payment_transaction as pt', 's.id', '=', 'pt.sale_id')
            ->join('acc_transaction_mode as tm', 'tm.id', '=', 'pt.transaction_mode_id')
            ->selectRaw('tm.name as method,SUM(pt.amount) as amount')
            ->groupBy('pt.transaction_mode_id')
            ->get();

        $method = DB::table('inv_sales as s')->where('s.config_id', $inventoryConfigId)
           // ->when($start_date && $end_date, fn($q) => $q->whereBetween('s.created_at', [$start_date, $end_date]))
            ->join('inv_payment_transaction as pt', 's.id', '=', 'pt.sale_id')
            ->join('acc_transaction_mode as tm', 'tm.id', '=', 'pt.transaction_mode_id')
            ->join('acc_setting as acs', 'acs.id', '=', 'tm.method_id')
            ->selectRaw('acs.name as name, SUM(pt.amount) as amount, 0 as count')
            ->groupBy('tm.method_id')
            ->get();

        $data = ['sales' => $sales,'purchase' => $purchase , 'methods' => $method,'transactionModes' => $transactionModes,'topSalesItem' => $topSalesItem];
        return $data;


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
            ->join('inv_purchase', 'inv_purchase.id', '=', 'inv_purchase_item.purchase_id')
            ->join('inv_stock', 'inv_stock.id', '=', 'inv_purchase_item.stock_item_id')
            ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
            ->leftJoin('inv_category', 'inv_category.id', '=', 'inv_product.category_id')
            ->leftJoin('cor_warehouses', 'cor_warehouses.id', '=', 'inv_purchase_item.warehouse_id')
            ->leftJoin('inv_particular', 'inv_particular.id', '=', 'inv_product.unit_id')
            ->select([
                'inv_purchase_item.id',
                'inv_purchase_item.created_at',
                'inv_purchase_item.expired_date',
                'inv_purchase_item.purchase_id',
                'inv_purchase.invoice',
                'inv_purchase.grn',
                'inv_stock.name as item_name',
                'inv_category.name as category_name',
                'inv_purchase_item.quantity as quantity',
                'inv_purchase_item.opening_quantity as opening_quantity',
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


}
