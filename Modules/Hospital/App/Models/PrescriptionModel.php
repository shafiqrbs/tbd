<?php

namespace Modules\Hospital\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class PrescriptionModel extends Model
{
    use HasFactory;

    protected $table = 'hms_prescription';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

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

    public function invoice_details()
    {
        return $this->hasOne(InvoiceModel::class, 'id', 'hms_invoice_id');
    }


    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;
        $entities = InvoiceModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->join('inv_sales as inv_sales','inv_sales.id','=','hms_invoice.sales_id')
            ->join('hms_prescription','hms_prescription.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->leftjoin('users as createdBy','createdBy.id','=','inv_sales.created_by_id')
            ->join('cor_customers as customer','customer.id','=','inv_sales.customer_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->join('hms_particular_mode as patient_payment_mode','patient_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                'hms_invoice.id',
                'hms_prescription.id as prescription_id',
                'customer.name',
                'customer.mobile',
                'customer.gender',
                'customer.customer_id as patient_id',
                'customer.health_id',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d-%m-%Y") as created_at'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%M-%Y") as appointment'),
                'hms_invoice.process as process',
                'vr.name as visiting_room',
                'inv_sales.invoice as invoice',
                'patient_mode.name as patient_mode_name',
                'patient_payment_mode.name as patient_payment_mode_name',
                'createdBy.name as created_by',
                'hms_invoice.sub_total as total',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $entities = $entities->whereAny(['hms_invoice.invoice','cor_customers.name','cor_customers.mobile','salesBy.username','createdBy.username','acc_transaction_mode.name','hms_invoice.total'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['customer_id']) && !empty($request['customer_id'])){
            $entities = $entities->where('hms_invoice.customer_id',$request['customer_id']);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && empty($request['end_date'])){
            $start_date = $request['start_date'].' 00:00:00';
            $end_date = $request['start_date'].' 23:59:59';
            $entities = $entities->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && isset($request['end_date']) && !empty($request['end_date'])){
            $start_date = $request['start_date'].' 00:00:00';
            $end_date = $request['end_date'].' 23:59:59';
            $entities = $entities->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }

        $total  = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('hms_invoice.updated_at','DESC')
            ->get();

        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }



}
