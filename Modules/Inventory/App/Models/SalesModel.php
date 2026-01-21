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

class SalesModel extends Model
{
    use HasFactory;

    protected $table = 'inv_sales';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'customer_id',
        'config_id',
        'invoice_batch_id',
        'created_by_id',
        'sales_by_id',
        'module',
        'sub_total',
        'total',
        'is_domain_sales_completed',
        'approved_by_id',
        'discount',
        'discount_calculation',
        'discount_type',
        'payment',
        'invoice_date',
        'invoice',
        'narration',
        'process',
        'transaction_mode_id',
        'sales_form',
        'expected_date',
        'child_purchase_id'
    ];



    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $model->invoice = self::salesEventListener($model)['generateId'];
            $model->code = self::salesEventListener($model)['code'];
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function quickRandom($length = 12)
    {
        $pool = '0123456789';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    public static function salesEventListener($model)
    {
        $patternCodeService = app(GeneratePatternCodeService::class);
        $params = [
            'config' => $model->config_id,
            'table' => 'inv_sales',
            'prefix' => 'INV-',
        ];
        return $patternCodeService->invoiceNo($params);
    }

    public function salesItems()
    {
        return $this->hasMany(SalesItemModel::class, 'sale_id');
    }

    public function customerDomain(){
        return $this->belongsTo(CustomerModel::class , 'customer_id');
    }

    public function insertSalesItems($sales,$items)
    {
        $timestamp = Carbon::now();
        foreach ($items as &$record) {
            $record['sale_id'] = $sales->id;
            $record['created_at'] = $timestamp;
            $record['updated_at'] = $timestamp;
        }

    }

    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $entities = self::where([['inv_sales.config_id',$domain['config_id']]])
            ->leftjoin('users as createdBy','createdBy.id','=','inv_sales.created_by_id')
            ->leftjoin('users as salesBy','salesBy.id','=','inv_sales.sales_by_id')
            ->leftjoin('acc_transaction_mode','acc_transaction_mode.id','=','inv_sales.transaction_mode_id')
            ->leftjoin('cor_customers','cor_customers.id','=','inv_sales.customer_id')
            ->leftjoin('cor_setting','cor_setting.id','=','cor_customers.customer_group_id')
            ->select([
                'inv_sales.id',
                DB::raw('DATE_FORMAT(inv_sales.created_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(inv_sales.expected_date, "%d-%m-%Y") as expected_date'),
                'inv_sales.invoice as invoice',
                'inv_sales.sub_total as sub_total',
                'inv_sales.total as total',
                'inv_sales.approved_by_id',
                'inv_sales.payment as payment',
                'inv_sales.discount as discount',
                'inv_sales.is_domain_sales_completed',
                'inv_sales.discount_calculation as discount_calculation',
                'inv_sales.discount_type as discount_type',
                'inv_sales.invoice_batch_id',
                DB::raw("CONCAT(UCASE(LEFT(sales_form,1)), LCASE(SUBSTRING(sales_form,2))) as sales_form"),
                'cor_customers.id as customerId',
                'cor_customers.name as customerName',
                'cor_customers.mobile as customerMobile',
                'createdBy.username as createdByUser',
                'createdBy.name as createdByName',
                'cor_setting.name as customer_group',
                'createdBy.id as createdById',
                'salesBy.id as salesById',
                'salesBy.username as salesByUser',
                'salesBy.name as salesByName',
                'inv_sales.process as process',
                'acc_transaction_mode.name as mode_name',
                'cor_customers.address as customer_address',
                'cor_setting.name as customer_group',
                'cor_customers.balance as balance',
            ])->with(['salesItems' => function ($query) {
                $query->select([
                    'inv_sales_item.id',
                    'inv_sales_item.sale_id',
                    'inv_sales_item.stock_item_id as product_id',
                    'inv_sales_item.unit_id',
                    'inv_sales_item.name as item_name',
                    'inv_sales_item.name as name',
                    'inv_sales_item.uom as uom',
                    'inv_sales_item.quantity',
                    'inv_sales_item.sales_price',
                    'inv_sales_item.purchase_price',
                    'inv_sales_item.price',
                    'inv_sales_item.sub_total',
                    'inv_sales_item.bonus_quantity',
                    DB::raw("CONCAT(cor_warehouses.name, ' (', cor_warehouses.location, ')') as warehouse_name"),
                    'cor_warehouses.name as warehouse',
                    'cor_warehouses.location as warehouse_location',
                    'cor_warehouses.id as warehouse_id'
                ])->leftjoin('cor_warehouses','cor_warehouses.id','=','inv_sales_item.warehouse_id');
            }]);

        if (isset($request['term']) && !empty($request['term'])){
            $entities = $entities->whereAny(['inv_sales.invoice','cor_customers.name','cor_customers.mobile','salesBy.username','createdBy.username','acc_transaction_mode.name','inv_sales.total'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['customer_id']) && !empty($request['customer_id'])){
            $entities = $entities->where('inv_sales.customer_id',$request['customer_id']);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && empty($request['end_date'])){
            $start_date = $request['start_date'].' 00:00:00';
            $end_date = $request['start_date'].' 23:59:59';
            $entities = $entities->whereBetween('inv_sales.created_at',[$start_date, $end_date]);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && isset($request['end_date']) && !empty($request['end_date'])){
            $start_date = $request['start_date'].' 00:00:00';
            $end_date = $request['end_date'].' 23:59:59';
            $entities = $entities->whereBetween('inv_sales.created_at',[$start_date, $end_date]);
        }

        $total  = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('inv_sales.updated_at','DESC')
            ->get();

        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }


    public static function getShow($id,$domain)
    {
        $entity = self::where([
            ['inv_sales.config_id', '=', $domain['config_id']],
            ['inv_sales.id', '=', $id]
        ])
            ->leftjoin('cor_customers','cor_customers.id','=','inv_sales.customer_id')
            ->leftjoin('users as createdBy','createdBy.id','=','inv_sales.created_by_id')
            ->leftjoin('users as salesBy','salesBy.id','=','inv_sales.sales_by_id')
            ->leftjoin('acc_transaction_mode as transactionMode','transactionMode.id','=','inv_sales.transaction_mode_id')
//            ->leftjoin('uti_transaction_method as method','method.id','=','acc_transaction_mode.method_id')
            ->select([
                'inv_sales.id',
                DB::raw('DATE_FORMAT(inv_sales.updated_at, "%d-%m-%Y") as created'),
                'inv_sales.invoice as invoice',
                'inv_sales.sub_total as sub_total',
                'inv_sales.total as total',
                'inv_sales.payment as payment',
                'inv_sales.discount as discount',
                'inv_sales.discount_calculation as discount_calculation',
                'inv_sales.discount_type as discount_type',
                'cor_customers.id as customer_id',
                'cor_customers.name as customer_name',
                'cor_customers.mobile as customer_mobile',
                'createdBy.username as created_by_user_name',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'salesBy.id as sales_by_id',
                'salesBy.username as sales_by_username',
                'salesBy.name as sales_by_name',
                'transactionMode.name as mode_name',
                'inv_sales.transaction_mode_id as transaction_mode_id',
                'inv_sales.process as process_id',
            ])
            ->with(['salesItems' => function ($query) {
                $query->select([
                    'inv_sales_item.id',
                    'inv_sales_item.sale_id',
                    'inv_sales_item.stock_item_id as product_id',
                    'inv_sales_item.uom',
                    'inv_sales_item.name as item_name',
                    'inv_sales_item.quantity',
                    'inv_sales_item.sales_price',
                    'inv_sales_item.purchase_price',
                    'inv_sales_item.price',
                    'inv_sales_item.sub_total',
                ]);
            }])
            ->first();

        return $entity;
    }


    public static function getEditData($id,$domain)
    {
        $entity = self::where([
            ['inv_sales.config_id', '=', $domain['config_id']],
            ['inv_sales.id', '=', $id]
        ])
            ->leftjoin('cor_customers','cor_customers.id','=','inv_sales.customer_id')
            ->leftjoin('users as createdBy','createdBy.id','=','inv_sales.created_by_id')
            ->leftjoin('users as salesBy','salesBy.id','=','inv_sales.sales_by_id')
            ->leftjoin('acc_transaction_mode as transactionMode','transactionMode.id','=','inv_sales.transaction_mode_id')
            ->leftjoin('uti_settings','uti_settings.id','=','inv_sales.process')
            ->select([
                'inv_sales.id',
                DB::raw('DATE_FORMAT(inv_sales.updated_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(inv_sales.updated_at, "%d-%M-%Y") as created_date'),
                'inv_sales.invoice as invoice',
                'inv_sales.sub_total as sub_total',
                'inv_sales.total as total',
                'inv_sales.narration',
                'inv_sales.payment as payment',
                'inv_sales.discount as discount',
                'inv_sales.discount_calculation as discount_calculation',
                'inv_sales.discount_type as discount_type',
                'cor_customers.id as customer_id',
                'cor_customers.name as customer_name',
                'cor_customers.mobile as customer_mobile',
                'createdBy.username as created_by_user_name',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'salesBy.id as sales_by_id',
                'salesBy.username as sales_by_username',
                'salesBy.name as sales_by_name',
                'transactionMode.name as mode_name',
                'inv_sales.transaction_mode_id as transaction_mode_id',
                'inv_sales.process as process_id',
                'uti_settings.name as process_name',
                'cor_customers.address as customer_address',
            ])
            ->with(['salesItems' => function ($query) {
                $query->select([
                    'inv_sales_item.id',
                    'inv_sales_item.sale_id',
                    'inv_sales_item.stock_item_id as product_id',
                    'inv_sales_item.unit_id',
                    'inv_sales_item.name as item_name',
                    'inv_sales_item.name as name',
                    'inv_sales_item.uom as uom',
                    'inv_sales_item.quantity',
                    'inv_sales_item.sales_price',
                    'inv_sales_item.purchase_price',
                    'inv_sales_item.price',
                    'inv_sales_item.sub_total',
                    'inv_sales_item.bonus_quantity',
                    DB::raw("CONCAT(cor_warehouses.name, ' (', cor_warehouses.location, ')') as warehouse_name"),
                    'cor_warehouses.name as warehouse',
                    'cor_warehouses.location as warehouse_location',
                    'cor_warehouses.id as warehouse_id'
                ])->leftjoin('cor_warehouses','cor_warehouses.id','=','inv_sales_item.warehouse_id');
            }])
            ->first();

        return $entity;
    }

    /*public static function dailySalesReport(array $params, int $domain_id, int $inventoryConfigId){
        // Get date range for calculations
        $dates = !empty($params['month']) && !empty($params['year'])
            ? DateHelper::getFirstAndLastDate((int)$params['year'], (int)$params['month'])
            : null;

        $startDate = $dates['first_date'] ?? null;
        $endDate = $dates['last_date'] ?? null;
        $customerId = $params['customer_id'] ?? null;
        $warehouseId = $params['warehouse_id'] ?? null;
        $customerGroup = $params['customer_group'] ?? null;

        $sales = DB::table('inv_sales as s')
            ->join('inv_sales_item as si', 's.id', '=', 'si.sale_id')
            ->join('cor_customers as c', 's.customer_id', '=', 'c.id')
            ->join('inv_stock as stock', 'si.stock_item_id', '=', 'stock.id')
            ->selectRaw('
                    DATE(s.created_at) as date,
                    si.stock_item_id,
                    si.warehouse_id,
                    stock.name as product_name,
                    c.name as customer_name,
                    SUM(si.quantity) as total_quantity,
                    SUM(si.sub_total) as total_amount
                ')
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('s.created_at', [$startDate, $endDate]))
            ->when($customerId, fn($q) => $q->where('s.customer_id', $customerId))
            ->when($warehouseId, fn($q) => $q->where('si.warehouse_id', $warehouseId))
            ->when($customerGroup, fn($q) => $q->where('c.customer_group', $customerGroup))
            ->where('s.config_id', $inventoryConfigId)
            ->groupBy('date', 'si.stock_item_id', 'si.warehouse_id', 'stock.name', 'c.name')
            ->orderBy('date')
            ->get();

// Step 1: Build key list for (date_stock_warehouse)
        $dailyKeys = $sales->map(fn($row) => [
            'inv_date' => $row->date,
            'stock_item_id' => $row->stock_item_id,
            'warehouse_id' => $row->warehouse_id,
        ])->unique();

// Step 2: Fetch opening & receive (stock) values
        $stockOpenings = DB::table('inv_daily_stock')
            ->select('inv_date', 'stock_item_id', 'warehouse_id', 'opening_quantity', 'total_in_quantity','closing_quantity')
            ->where('config_id', $inventoryConfigId)
            ->where(function ($query) use ($dailyKeys) {
                foreach ($dailyKeys as $key) {
                    $query->orWhere(function ($q) use ($key) {
                        $q->whereDate('inv_date', $key['inv_date'])
                            ->where('stock_item_id', $key['stock_item_id'])
                            ->where('warehouse_id', $key['warehouse_id']);
                    });
                }
            })
            ->get()
            ->keyBy(fn($row) => $row->inv_date . '_' . $row->stock_item_id . '_' . $row->warehouse_id);

        // Step 3: Prepare final report
        $report = [];

        foreach ($sales as $row) {
            $date = $row->date;
            $product = $row->product_name;
            $customer = $row->customer_name;
            $warehouseId = $row->warehouse_id;

            $stockKey = $row->date . '_' . $row->stock_item_id . '_' . $warehouseId;

            $openingQty = $stockOpenings[$stockKey]->opening_quantity ?? 0;
            $totalInQty = $stockOpenings[$stockKey]->total_in_quantity ?? 0;
            $closingQty = $stockOpenings[$stockKey]->closing_quantity ?? 0;
            $receiveQty = $totalInQty-$openingQty ?? 0;

            if (!isset($report[$date])) {
                $report[$date] = [];
            }

            if (!isset($report[$date][$product])) {
                $report[$date][$product] = [
                    'opening' => $openingQty,
                    'receive' => $receiveQty,
                    'total_in_qty' => $totalInQty,
                    'total_out_qty' => 0,
                    'total_amount' => 0,
                    'closing' => $closingQty,
                    'customers' => [],
                ];
            }

            $report[$date][$product]['customers'][$customer] = [
                'qty' => $row->total_quantity,
                'amount' => $row->total_amount,
            ];

            $report[$date][$product]['total_out_qty'] += $row->total_quantity;
            $report[$date][$product]['total_amount'] += $row->total_amount;
        }
        return $report;
    }*/

    public static function dailySalesReport(array $params, int $domain_id, int $inventoryConfigId)
    {
        // 1. Get Date Range
        $dates = !empty($params['month']) && !empty($params['year'])
            ? DateHelper::getFirstAndLastDate((int)$params['year'], (int)$params['month'])
            : null;

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

        // 3. Build keys for stock filtering
        $dailyKeys = $sales->map(fn($row) => [
            'inv_date' => $row->date,
            'stock_item_id' => $row->stock_item_id,
            'warehouse_id' => $groupByWarehouse ? $row->warehouse_id : null,
        ])->unique();

        // 4. Fetch Stock Openings
        $rawStock = DB::table('inv_daily_stock')
            ->select('inv_date', 'stock_item_id', 'warehouse_id', 'opening_quantity', 'total_in_quantity', 'closing_quantity')
            ->where('config_id', $inventoryConfigId)
            ->where(function ($query) use ($dailyKeys, $groupByWarehouse) {
                foreach ($dailyKeys as $key) {
                    $query->orWhere(function ($q) use ($key, $groupByWarehouse) {
                        $q->whereDate('inv_date', $key['inv_date'])
                            ->where('stock_item_id', $key['stock_item_id']);

                        if ($groupByWarehouse) {
                            $q->where('warehouse_id', $key['warehouse_id']);
                        }
                    });
                }
            })
            ->get();

        // 5. Sum Stock Data into Indexable Keys
        $stockOpenings = [];

        foreach ($rawStock as $row) {
            $key = $row->inv_date . '_' . $row->stock_item_id;
            if ($groupByWarehouse) {
                $key .= '_' . $row->warehouse_id;
            }

            if (!isset($stockOpenings[$key])) {
                $stockOpenings[$key] = [
                    'opening' => 0,
                    'receive' => 0,
                    'closing' => 0,
                ];
            }

            $stockOpenings[$key]['opening'] += $row->opening_quantity ?? 0;
            $stockOpenings[$key]['receive'] += $row->total_in_quantity ?? 0;
            $stockOpenings[$key]['closing'] += $row->closing_quantity ?? 0;
        }

        // 6. Final Report Build
        $report = [];

        foreach ($sales as $row) {
            $date = $row->date;
            $product = $row->product_name;
            $customer = $row->customer_name;
            $whId = $groupByWarehouse ? $row->warehouse_id : null;

            $stockKey = $date . '_' . $row->stock_item_id . ($groupByWarehouse ? '_' . $whId : '');

            $stockData = $stockOpenings[$stockKey] ?? ['opening' => 0, 'receive' => 0, 'closing' => 0];

            $openingQty = $stockData['opening'];
            $totalInQty = $stockData['receive'];
            $closingQty = $stockData['closing'];
            $receiveQty = $totalInQty - $openingQty;

            if (!isset($report[$date])) {
                $report[$date] = [];
            }

            if (!isset($report[$date][$product])) {
                $report[$date][$product] = [
                    'opening' => $openingQty,
                    'receive' => $receiveQty,
                    'total_in_qty' => $totalInQty,
                    'total_out_qty' => 0,
                    'total_amount' => 0,
                    'closing' => $closingQty,
                    'customers' => [],
                ];
            }

            $report[$date][$product]['customers'][$customer] = [
                'qty' => $row->total_quantity,
                'amount' => $row->total_amount,
            ];

            $report[$date][$product]['total_out_qty'] += $row->total_quantity;
            $report[$date][$product]['total_amount'] += $row->total_amount;
        }

        return $report;
    }


}
