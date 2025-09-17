<?php

namespace Modules\Inventory\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RequisitionModel extends Model
{
    protected $table = 'inv_requisition';
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
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function quickRandom($length = 12)
    {
        $pool = '0123456789';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    public function requisitionItems()
    {
        return $this->hasMany(RequisitionItemModel::class, 'requisition_id');
    }

    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $entities = self::where([['inv_requisition.customer_config_id',$domain['config_id']]])
            ->leftjoin('users as createdBy','createdBy.id','=','inv_requisition.created_by_id')
            ->leftjoin('cor_vendors','cor_vendors.id','=','inv_requisition.vendor_id')
            ->select([
                'inv_requisition.id',
                'inv_requisition.invoice',
                DB::raw('DATE_FORMAT(inv_requisition.created_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(inv_requisition.expected_date, "%d-%m-%Y") as expected_date'),
                'inv_requisition.sub_total as sub_total',
                'inv_requisition.total as total',
                'inv_requisition.payment as payment',
                'inv_requisition.discount as discount',
                'inv_requisition.discount_calculation as discount_calculation',
                'inv_requisition.discount_type as discount_type',
                'inv_requisition.approved_by_id',
                'cor_vendors.id as vendor_id',
                'cor_vendors.name as vendor_name',
                'cor_vendors.mobile as vendor_mobile',
                'createdBy.username as createdByUser',
                'createdBy.name as createdByName',
                'createdBy.id as createdById',
                'inv_requisition.process as process',
                'cor_vendors.address as vendor_address',
            ])->with(['requisitionItems' => function ($query){
                    $query->select([
                        'inv_requisition_item.id',
                        'inv_requisition_item.requisition_id',
                        'inv_stock.name as item_name',
                        'inv_requisition_item.quantity',
                        'inv_requisition_item.purchase_price',
                        'inv_requisition_item.sales_price',
                        'inv_requisition_item.sub_total',
                        'inv_requisition_item.unit_name as unit_name',
                    ])
                        ->join('inv_stock','inv_stock.id','=','inv_requisition_item.customer_stock_item_id')
                        ->join('inv_product','inv_product.id','=','inv_stock.product_id');
//                        ->leftjoin('inv_particular','inv_particular.id','=','inv_product.unit_id');
                }]);

        if (isset($request['term']) && !empty($request['term'])){
            $entities = $entities->whereAny(['inv_requisition.sub_total','inv_requisition.invoice','cor_vendors.mobile','createdBy.username','cor_vendors.name','createdBy.name'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['vendor_id']) && !empty($request['vendor_id'])){
            $entities = $entities->where('inv_requisition.vendor_id',$request['vendor_id']);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && empty($request['end_date'])){
            $start_date = $request['start_date'].' 00:00:00';
            $end_date = $request['start_date'].' 23:59:59';
            $entities = $entities->whereBetween('inv_requisition.created_at',[$start_date, $end_date]);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && isset($request['end_date']) && !empty($request['end_date'])){
            $start_date = $request['start_date'].' 00:00:00';
            $end_date = $request['end_date'].' 23:59:59';
            $entities = $entities->whereBetween('inv_requisition.created_at',[$start_date, $end_date]);
        }

        $total  = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('inv_requisition.id','DESC')
            ->get();
        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }

    public static function getShow($id,$domain)
    {
        $data = self::where('inv_requisition.id',$id)
            ->leftjoin('users as createdBy','createdBy.id','=','inv_requisition.created_by_id')
            ->leftjoin('cor_vendors','cor_vendors.id','=','inv_requisition.vendor_id')
            ->select([
                'inv_requisition.id',
                'inv_requisition.invoice',
                DB::raw('DATE_FORMAT(inv_requisition.created_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(inv_requisition.expected_date, "%d-%m-%Y") as expected_date'),
                DB::raw('DATE_FORMAT(inv_requisition.invoice_date, "%d-%m-%Y") as invoice_date'),
                'inv_requisition.sub_total as sub_total',
                'inv_requisition.remark',
                'inv_requisition.total as total',
                'inv_requisition.payment as payment',
                'inv_requisition.discount as discount',
                'inv_requisition.discount_calculation as discount_calculation',
                'inv_requisition.discount_type as discount_type',
                'inv_requisition.approved_by_id',
                'cor_vendors.id as vendor_id',
                'cor_vendors.name as vendor_name',
                'cor_vendors.mobile as vendor_mobile',
                'createdBy.username as createdByUser',
                'createdBy.name as createdByName',
                'createdBy.id as createdById',
                'inv_requisition.process as process',
                'cor_vendors.address as vendor_address',
            ])->with(['requisitionItems' => function ($query){
                $query->select([
                    'inv_requisition_item.id',
                    'inv_requisition_item.requisition_id',
                    'inv_stock.id as product_id',
                    'inv_stock.display_name as display_name',
                    'inv_requisition_item.quantity',
                    'inv_requisition_item.purchase_price',
                    'inv_requisition_item.sales_price',
                    'inv_requisition_item.sub_total',
                    'inv_requisition_item.warehouse_id',
                    'inv_requisition_item.unit_name as unit_name',
                ])
                    ->join('inv_stock','inv_stock.id','=','inv_requisition_item.customer_stock_item_id')
                    ->join('inv_product','inv_product.id','=','inv_stock.product_id');
//                        ->leftjoin('inv_particular','inv_particular.id','=','inv_product.unit_id');
            }])->first();
        return $data;
    }

}
