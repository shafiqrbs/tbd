<?php

namespace Modules\Inventory\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Ramsey\Collection\Collection;

class PurchaseModel extends Model
{
    use HasFactory;

    protected $table = 'inv_purchase';
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

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItemModel::class, 'purchase_id');
    }

    public function insertPurchaseItems($sales,$items)
    {
        $timestamp = Carbon::now();
        foreach ($items as &$record) {
            $record['purchase_id'] = $sales->id;
            $record['created_at'] = $timestamp;
            $record['updated_at'] = $timestamp;
        }
        PurchaseItemModel::insert($items);
    }

    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $entities = self::where([['inv_purchase.config_id',$domain['config_id']]])
            ->leftjoin('users as createdBy','createdBy.id','=','inv_purchase.created_by_id')
            ->leftjoin('acc_transaction_mode','acc_transaction_mode.id','=','inv_purchase.transaction_mode_id')
            ->leftjoin('cor_vendors','cor_vendors.id','=','inv_purchase.customer_id')
            ->select([
                'inv_purchase.id',
                DB::raw('DATE_FORMAT(inv_purchase.created_at, "%d-%m-%Y") as created'),
                'inv_purchase.invoice as invoice',
                'inv_purchase.sub_total as sub_total',
                'inv_purchase.total as total',
                'inv_purchase.payment as payment',
                'inv_purchase.discount as discount',
                'inv_purchase.discount_calculation as discount_calculation',
                'inv_purchase.discount_type as discount_type',
                'cor_vendors.id as customerId',
                'cor_vendors.name as customerName',
                'cor_vendors.mobile as customerMobile',
                'createdBy.username as createdByUser',
                'createdBy.name as createdByName',
                'createdBy.id as createdById',
                'inv_purchase.process as process',
                'acc_transaction_mode.name as mode_name',
                'cor_vendors.address as customer_address',
                'cor_vendors.balance as balance',
            ])->with('salesItems');

        if (isset($request['term']) && !empty($request['term'])){
            $entities = $entities->whereAny(['inv_purchase.name','inv_purchase.slug','inv_category.name','uti_product_unit.name','inv_brand.name','inv_purchase.sales_price','uti_settings.name'],'LIKE','%'.$request['term'].'%');
        }

        $total  = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('inv_purchase.updated_at','DESC')
            ->get();
        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }


    public static function getShow($id,$domain)
    {
        $entity = self::where([['inv_purchase.config_id',$domain['config_id'],'inv_purchase.id',$id]])
            ->leftjoin('cor_vendors','cor_vendors.id','=','inv_purchase.customer_id')
            ->leftjoin('users as createdBy','createdBy.id','=','inv_purchase.created_by_id')
            ->leftjoin('acc_transaction_mode as transactionMode','transactionMode.id','=','inv_purchase.transaction_mode_id')
            ->leftjoin('uti_transaction_method as method','method.id','=','acc_transaction_mode.method_id')
            ->select([
                'inv_purchase.id',
                '(DATE_FORMAT(inv_purchase.updated_at,\'%Y-%m\')) as created',
                'inv_purchase.invoice as invoice',
                'inv_purchase.sub_total as sub_total',
                'inv_purchase.total as total',
                'inv_purchase.payment as payment',
                'inv_purchase.discount as discount',
                'inv_purchase.discount_calculation as discount_calculation',
                'inv_purchase.discount_type as discount_type',
                'cor_vendors.id as customerId',
                'cor_vendors.name as customerName',
                'cor_vendors.mobile as customerMobile',
                'createdBy.username as createdByUser',
                'createdBy.name as createdByName',
                'createdBy.id as createdById',
                'transactionMode.name as modeName',
                'transactionMode.name as modeName',

            ])->with(['salesItems' => function ($query){
                $query->select([
                    'id',
                    'purchase_id',
                    'unit_id'
                ])->with([
                    'unit'
                ]);
            }])->first();

        return $entity;
    }

}
