<?php

namespace Modules\Inventory\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Ramsey\Collection\Collection;

class SalesModel extends Model
{
    use HasFactory;

    protected $table = 'inv_sales';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

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

    public function salesItems()
    {
        return $this->hasMany(SalesItemModel::class, 'sale_id');
    }

    public function insertSalesItems($sales,$items)
    {
        $timestamp = Carbon::now();
        foreach ($items as &$record) {
            $record['sale_id'] = $sales->id;
            $record['created_at'] = $timestamp;
            $record['updated_at'] = $timestamp;
        }
        SalesItemModel::insert($items);
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
            ->select([
                'inv_sales.id',
                DB::raw('DATE_FORMAT(inv_sales.created_at, "%d-%m-%Y") as created'),
                'inv_sales.invoice as invoice',
                'inv_sales.sub_total as sub_total',
                'inv_sales.total as total',
                'inv_sales.payment as payment',
                'inv_sales.discount as discount',
                'inv_sales.discount_calculation as discount_calculation',
                'inv_sales.discount_type as discount_type',
                'cor_customers.id as customerId',
                'cor_customers.name as customerName',
                'cor_customers.mobile as customerMobile',
                'createdBy.username as createdByUser',
                'createdBy.name as createdByName',
                'createdBy.id as createdById',
                'salesBy.id as salesById',
                'salesBy.username as salesByUser',
                'salesBy.name as salesByName',
                'inv_sales.process as process',
                'acc_transaction_mode.name as mode_name',
                'cor_customers.address as customer_address',
                'cor_customers.balance as balance',
            ])->with('salesItems');

        if (isset($request['term']) && !empty($request['term'])){
            $entities = $entities->whereAny(['inv_sales.name','inv_sales.slug','inv_category.name','uti_product_unit.name','inv_brand.name','inv_sales.sales_price','uti_settings.name'],'LIKE','%'.$request['term'].'%');
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
        $entity = self::where([['inv_sales.config_id',$domain['config_id'],'inv_sales.id',$id]])
            ->leftjoin('cor_customers','cor_customers.id','=','inv_sales.customer_id')
            ->leftjoin('users as createdBy','createdBy.id','=','inv_sales.created_by_id')
            ->leftjoin('users as salesBy','salesBy.id','=','inv_sales.sales_by_id')
            ->leftjoin('acc_transaction_mode as transactionMode','transactionMode.id','=','inv_sales.transaction_mode_id')
            ->leftjoin('uti_transaction_method as method','method.id','=','acc_transaction_mode.method_id')
            ->select([
                'inv_sales.id',
                '(DATE_FORMAT(inv_sales.updated_at,\'%Y-%m\')) as created',
                'inv_sales.invoice as invoice',
                'inv_sales.sub_total as sub_total',
                'inv_sales.total as total',
                'inv_sales.payment as payment',
                'inv_sales.discount as discount',
                'inv_sales.discount_calculation as discount_calculation',
                'inv_sales.discount_type as discount_type',
                'cor_customers.id as customerId',
                'cor_customers.name as customerName',
                'cor_customers.mobile as customerMobile',
                'createdBy.username as createdByUser',
                'createdBy.name as createdByName',
                'createdBy.id as createdById',
                'salesBy.id as salesById',
                'salesBy.username as salesByUser',
                'salesBy.name as salesByName',
                'transactionMode.name as modeName',
                'transactionMode.name as modeName',

            ])->with(['salesItems' => function ($query){
                $query->select([
                    'id',
                    'sale_id',
                    'unit_id'
                ])->with([
                    'unit'
                ]);
            }])->first();

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
                'cor_customers.id as customerId',
                'cor_customers.name as customerName',
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
            ->with(['salesItems' => function ($query) {
                $query->select([
                    'id',
                    'sale_id',
                    'unit_id',
                    'item_name',
                    'quantity',
                    'sales_price',
                    'purchase_price',
                    'price',
                    'sub_total',
                ])->with('unit');
            }])
            ->first();

        return $entity;
    }


}
