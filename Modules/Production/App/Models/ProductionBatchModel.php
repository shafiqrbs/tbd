<?php

namespace Modules\Production\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ProductionBatchModel extends Model
{
    use HasFactory;

    protected $table = 'pro_batch';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'issue_date',
        'receive_date',
        'process',
        'mode',
        'invoice',
        'created_by_id',
        'code'
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            $model->issue_date = $date;
            $model->receive_date = $date;
        });
        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }
    public static function getRecords($request,$domain){

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;

        $entity = self::where('pro_config.domain_id',$domain['global_id'])
            ->join('pro_setting_type','pro_setting_type.id','=','pro_setting.setting_type_id')
            ->join('pro_config','pro_config.id','=','pro_setting.config_id')
            ->select([
                'pro_setting.id',
                'pro_setting.name',
                'pro_setting.slug',
                'pro_setting.status',
                DB::raw('DATE_FORMAT(pro_setting.created_at, "%d-%M-%Y") as created'),
                'pro_setting_type.name as setting_type_name',
                'pro_setting_type.slug as setting_type_slug',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $entity = $entity->whereAny(['pro_setting.name','pro_setting.slug','pro_setting_type.slug'],'LIKE','%'.trim($request['term']).'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $entity = $entity->where('pro_setting.name','LIKE','%'.trim($request['name']));
        }

        if (isset($request['setting_type_id']) && !empty($request['setting_type_id'])){
            $entity = $entity->where('pro_setting.setting_type_id',$request['setting_type_id']);
        }

        $total  = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')
            ->get();

        $data = array('count'=>$total,'entities' => $entities);
        return $data;
    }

    public static function getShow($id,$domain)
    {
        $entity = self::where([
            ['pro_batch.config_id', '=', $domain['pro_config']],
            ['pro_batch.id', '=', $id]
        ])
//            ->leftjoin('cor_customers','cor_customers.id','=','inv_sales.customer_id')
//            ->leftjoin('users as createdBy','createdBy.id','=','inv_sales.created_by_id')
//            ->leftjoin('users as salesBy','salesBy.id','=','inv_sales.sales_by_id')
//            ->leftjoin('acc_transaction_mode as transactionMode','transactionMode.id','=','inv_sales.transaction_mode_id')
//            ->leftjoin('uti_transaction_method as method','method.id','=','acc_transaction_mode.method_id')
            ->select([
                'pro_batch.id',
                DB::raw('DATE_FORMAT(pro_batch.created_at, "%d-%m-%Y") as created_date'),
                'pro_batch.invoice as invoice',
                'pro_batch.code',
                'pro_batch.mode',
                'pro_batch.process',
                'pro_batch.status',
                'pro_batch.issue_date',
                'pro_batch.receive_date',
                'pro_batch.remark',
            ])
            /*->with(['salesItems' => function ($query) {
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
            }])*/
            ->first();

        return $entity;
    }



}
