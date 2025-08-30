<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RequisitionBoardModel extends Model
{
    protected $table = 'inv_requisition_board';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
    ];

    public function requisition_matrix(){
        return $this->hasMany(RequisitionMatrixBoardModel::class,'requisition_board_id');
    }

    public static function boot() {

        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $entities = self::where([['inv_requisition_board.config_id',$domain['config_id']],['inv_requisition_board.status',true]])
            ->leftjoin('users as createdBy','createdBy.id','=','inv_requisition_board.created_by_id')
            ->leftjoin('users as approvedBy','approvedBy.id','=','inv_requisition_board.approved_by_id')
            ->select([
                'inv_requisition_board.id',
                'inv_requisition_board.created_by_id',
                'inv_requisition_board.approved_by_id',
                'inv_requisition_board.batch_no',
                'inv_requisition_board.total',
                'inv_requisition_board.status',
                'inv_requisition_board.process',
                DB::raw('DATE_FORMAT(inv_requisition_board.created_at, "%d-%m-%Y") as created_date'),
                DB::raw('DATE_FORMAT(inv_requisition_board.generate_date, "%d-%m-%Y") as generate_date'),
                'createdBy.username as created_username',
                'createdBy.name as created_name',
                'approvedBy.username as approved_username',
                'approvedBy.name as approved_name',
            ])->with(['requisition_matrix' => function ($query){
                $query->select([
                    'inv_requisition_matrix_board.id',
                    'inv_requisition_matrix_board.requisition_board_id',
                    'inv_requisition_matrix_board.vendor_config_id',
                    'inv_requisition_matrix_board.customer_config_id',
                    'cor_customers.name as customer_name',
                    'inv_requisition_matrix_board.vendor_stock_item_id',
                    'inv_requisition_matrix_board.customer_stock_item_id',
                    'inv_requisition_matrix_board.customer_name',
                    'inv_requisition_matrix_board.barcode',
                    'inv_requisition_matrix_board.unit_name',
                    'inv_requisition_matrix_board.display_name',
                    'inv_requisition_matrix_board.purchase_price',
                    'inv_requisition_matrix_board.sales_price',
                    'inv_requisition_matrix_board.quantity',
                    'inv_requisition_matrix_board.requested_quantity',
                    'inv_requisition_matrix_board.approved_quantity',
                    'inv_requisition_matrix_board.received_quantity',
                    'inv_requisition_matrix_board.vendor_stock_quantity',
                    'inv_requisition_matrix_board.sub_total',
                    'inv_requisition_matrix_board.status',
                    'inv_requisition_matrix_board.process',
                    'inv_requisition_matrix_board.expected_date',
                    'inv_requisition_matrix_board.generate_date'
                ])
                    ->join('cor_customers','cor_customers.id','=','inv_requisition_matrix_board.customer_id');
            }]);

        if (isset($request['term']) && !empty($request['term'])){
            $entities = $entities->whereAny(['inv_requisition_board.total','inv_requisition_board.process','inv_requisition_board.batch_no'],'LIKE','%'.$request['term'].'%');
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && empty($request['end_date'])){
            $start_date = $request['start_date'].' 00:00:00';
            $end_date = $request['start_date'].' 23:59:59';
            $entities = $entities->whereBetween('inv_requisition_board.generate_date',[$start_date, $end_date]);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && isset($request['end_date']) && !empty($request['end_date'])){
            $start_date = $request['start_date'].' 00:00:00';
            $end_date = $request['end_date'].' 23:59:59';
            $entities = $entities->whereBetween('inv_requisition_board.generate_date',[$start_date, $end_date]);
        }

        $total  = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('inv_requisition_board.id','DESC')
            ->get();
        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }

}
