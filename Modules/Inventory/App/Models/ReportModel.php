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


}
