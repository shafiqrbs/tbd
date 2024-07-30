<?php

namespace Modules\Inventory\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Ramsey\Collection\Collection;

class InvoiceBatchModel extends Model
{
    use HasFactory;

    protected $table = 'inv_invoice_batch';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'config_id',
        'customer_id',
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $model->invoice = self::quickRandom();
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $model->invoice = self::quickRandom();
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function quickRandom($length = 12)
    {
        $pool = '0123456789';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    public function invoiceBatchItems()
    {
        return $this->hasMany(InvoiceBatchItemModel::class, 'invoice_batch_id');
    }


    public function invoiceBatchTransactions()
    {
        return $this->hasMany(InvoiceBatchTransactionModel::class, 'invoice_batch_id');
    }


    public function batchInvoices()
    {
        return $this->hasMany(SalesModel::class, 'invoice_batch_id');
    }


    public static function insertBatch($config,$customer,$items)
    {


        $entity = self::create(
            [
                'config_id' => $config,
                'customer_id' => $customer,
            ]
        );
        $ids = $items;
        DB::table('inv_sales')
            ->whereIn('id',$ids)
            ->update(['invoice_batch_id' => $entity->id]);
        $batch = self::insertSalesItems($entity);
        return $batch;

    }

    public static function insertSalesItems($entity)
    {
        $timestamp = Carbon::now();
        $items = self::getSalesBatchRecords($entity);
        foreach ($items as $record) {
            InvoiceBatchItemModel::create(
                [
                    'invoice_batch_id' => $entity->id,
                    'stock_item_id' => $record->stock_item_id,
                    'name' => $record->name,
                    'uom' => $record->uom,
                    'quantity' =>  $record->quantity,
                    'price' => $record->sales_price,
                    'sales_price' => $record->sales_price,
                    'sub_total' => $record->sub_total,
                ]
            );
        }
        return $entity;
    }

    public static function getSalesBatchRecords($entity)
    {
        $results = DB::table('inv_sales_item as item')
            ->selectRaw('stock_item_id,name,uom,SUM(quantity) as quantity, SUM(item.sub_total) as sub_total, (SUM(item.sub_total)/SUM(quantity)) as sales_price')
            ->join('inv_sales', 'item.sale_id', '=', 'inv_sales.id')
            ->where('inv_sales.invoice_batch_id', $entity->id)
            ->groupBy('stock_item_id','uom','name')
            ->get();
        return $results;
    }

    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;
        $entities = self::where([['inv_invoice_batch.config_id',$domain['config_id']]])
            ->leftjoin('users as createdBy','createdBy.id','=','inv_invoice_batch.created_by_id')
            ->leftjoin('cor_customers','cor_customers.id','=','inv_invoice_batch.customer_id')
            ->select([
                'inv_invoice_batch.id',
                DB::raw('DATE_FORMAT(inv_invoice_batch.created_at, "%d-%m-%Y") as created'),
                'inv_invoice_batch.invoice as invoice',
                'inv_invoice_batch.sub_total as sub_total',
                'inv_invoice_batch.total as total',
                'inv_invoice_batch.amount as amount',
                'inv_invoice_batch.discount as discount',
                'inv_invoice_batch.received as received',
                DB::raw('COALESCE(inv_invoice_batch.received, 0) as received'),
                DB::raw('(inv_invoice_batch.total - COALESCE(inv_invoice_batch.received, 0)) as due'),
                'inv_invoice_batch.discount_calculation as discount_calculation',
                'inv_invoice_batch.discount_type as discount_type',
                'cor_customers.id as customer_id',
                'cor_customers.name as customer_name',
                'cor_customers.mobile as customer_mobile',
                'createdBy.username as created_by_user',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'inv_invoice_batch.process as process',
                'cor_customers.address as customer_address',
                'cor_customers.balance as balance',
            ]) ->with(['invoiceBatchItems' => function ($query){
               // $query->leftjoin('inv_stock','inv_stock.id','=','inv_invoice_batch_item.stock_item_id');
                $query->select([
                    'id',
                    'invoice_batch_id',
                    'name as name',
                    'name as item_name',
                    'uom as uom',
                    'quantity as quantity',
                    'sales_price as sales_price',
                    'price as price',
                    'sub_total as sub_total',
                ]);
            }]);

        if (isset($request['term']) && !empty($request['term'])){
            $entities = $entities->whereAny(['inv_invoice_batch.invoice','cor_customers.name','cor_customers.mobile','createdBy.username','inv_invoice_batch.total'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['customer_id']) && !empty($request['customer_id'])){
            $entities = $entities->where('inv_invoice_batch.customer_id',$request['customer_id']);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && empty($request['end_date'])){
            $start_date = $request['start_date'].' 00:00:00';
            $end_date = $request['start_date'].' 23:59:59';
            $entities = $entities->whereBetween('inv_invoice_batch.created_at',[$start_date, $end_date]);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && isset($request['end_date']) && !empty($request['end_date'])){
            $start_date = $request['start_date'].' 00:00:00';
            $end_date = $request['end_date'].' 23:59:59';
            $entities = $entities->whereBetween('inv_invoice_batch.created_at',[$start_date, $end_date]);
        }

        $total  = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('inv_invoice_batch.updated_at','DESC')
            ->get();

        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }

    public static function getShow($id,$domain)
    {
        $entity = self::where([
            ['inv_invoice_batch.config_id', '=', $domain['config_id']],
            ['inv_invoice_batch.id', '=', $id]
        ])
            ->leftjoin('cor_customers','cor_customers.id','=','inv_invoice_batch.customer_id')
            ->leftjoin('users as createdBy','createdBy.id','=','inv_invoice_batch.created_by_id')
            ->leftjoin('users as salesBy','salesBy.id','=','inv_invoice_batch.sales_by_id')
            ->select([
                'inv_invoice_batch.id',
                DB::raw('DATE_FORMAT(inv_invoice_batch.updated_at, "%d-%m-%Y") as created'),
                'inv_invoice_batch.invoice as invoice',
                'inv_invoice_batch.sub_total as sub_total',
                'inv_invoice_batch.total as total',
                'inv_invoice_batch.amount as amount',
                'inv_invoice_batch.discount as discount',
                'inv_invoice_batch.discount_calculation as discount_calculation',
                'inv_invoice_batch.discount_type as discount_type',
                'cor_customers.id as customer_id',
                'cor_customers.name as customer_name',
                'cor_customers.mobile as customerMobile',
                'createdBy.username as createdByUser',
                'createdBy.name as createdByName',
                'createdBy.id as createdById',
                'salesBy.id as salesById',
                'salesBy.username as salesByUser',
                'salesBy.name as salesByName',
                'transactionMode.name as modeName',
                'transactionMode.name as modeName',
            ])
            ->with('invoiceBatchTransactions')
            ->with('batchInvoices')
            ->with(['batchInvoices' => function ($query){
                $query->select(['*'])
                    ->with(['salesItems' => function ($query) {
                        $query->select(['*']);
                    }]);
            }])
            ->with(['invoiceBatchItems' => function ($query){
                $query->select([
                    'id',
                    'invoice_batch_id',
                    'name as name',
                    'name as item_name',
                    'uom as uom',
                    'quantity as quantity',
                    'sales_price as sales_price',
                    'price as price',
                    'sub_total as sub_total',
                ]);
            }])
            ->first();

        return $entity;
    }


    public static function getEditData($id,$domain)
    {
        $entity = self::where([
            ['inv_invoice_batch.config_id', '=', $domain['config_id']],
            ['inv_invoice_batch.id', '=', $id]
        ])
            ->leftjoin('cor_customers','cor_customers.id','=','inv_invoice_batch.customer_id')
            ->leftjoin('users as createdBy','createdBy.id','=','inv_invoice_batch.created_by_id')
            ->leftjoin('users as salesBy','salesBy.id','=','inv_invoice_batch.sales_by_id')
            ->select([
                'inv_invoice_batch.id',
                DB::raw('DATE_FORMAT(inv_invoice_batch.created_at, "%d-%m-%Y") as created'),
                'inv_invoice_batch.invoice as invoice',
                'inv_invoice_batch.sub_total as sub_total',
                'inv_invoice_batch.total as total',
                'inv_invoice_batch.amount as amount',
                'inv_invoice_batch.discount as discount',
                'inv_invoice_batch.discount_calculation as discount_calculation',
                'inv_invoice_batch.discount_type as discount_type',
                'cor_customers.id as customer_id',
                'cor_customers.name as customer_name',
                'cor_customers.mobile as customer_mobile',
                'createdBy.username as created_by_user',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'inv_invoice_batch.process as process',
                'cor_customers.address as customer_address',
                'cor_customers.balance as balance',
            ])
            ->with('invoiceBatchTransactions')
            ->with('batchInvoices')
            ->with(['batchInvoices' => function ($query){
                $query->select(['*'])
                    ->with(['salesItems' => function ($query) {
                    $query->select(['*']);
                }]);
            }])
            ->with(['invoiceBatchItems' => function ($query){
                $query->select([
                    'id',
                    'invoice_batch_id',
                    'name as name',
                    'name as item_name',
                    'uom as uom',
                    'quantity as quantity',
                    'sales_price as sales_price',
                    'price as price',
                    'sub_total as sub_total',
                ]);
            }])
            ->first();
        return $entity;
    }


}
