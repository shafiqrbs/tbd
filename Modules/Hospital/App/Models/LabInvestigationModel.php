<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\CustomerModel;
use Modules\Hospital\App\Http\Requests\ParticularInlineRequest;
use function Doctrine\Common\Collections\orderBy;


class LabInvestigationModel extends Model
{
    use HasFactory;

    protected $table = 'hms_invoice';
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

    public function customer()
    {
        return $this->hasOne(CustomerModel::class, 'id', 'customer_id');
    }

    public function invoice()
    {
        return $this->hasOne(OpdModel::class, 'id', 'sales_id');
    }


    public function invoice_transaction()
    {
        return $this->hasMany(
            InvoiceTransactionModel::class,
            'hms_invoice_id',             // foreign key in hms_invoice_particular
            'id'                          // local key in hms_invoice
        );
    }

    public function invoice_particular()
    {
        return $this->hasMany(
            InvoiceParticularModel::class,
            'hms_invoice_id',             // foreign key in hms_invoice_particular
            'id'                          // local key in hms_invoice
        );
    }

    public function room()
    {
        return $this->hasOne(ParticularModel::class, 'id', 'room_id');
    }

    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $entities = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->leftjoin('hms_prescription as prescription','prescription.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('users as doctor','doctor.id','=','prescription.created_by_id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->join('cor_customers as customer','customer.id','=','hms_invoice.customer_id')
            ->select([
                'hms_invoice.id',
                'hms_invoice.uid',
                'prescription.created_by_id as prescription_created_by_id',
                'hms_invoice.invoice as invoice',
                'customer.customer_id as patient_id',
                'doctor.name as doctor_name',
                'customer.name',
                'customer.mobile',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d-%m-%Y") as created_at'),
                'hms_invoice.process as process',
                'vr.name as visiting_room',
                'createdBy.name as created_by',
                'hms_invoice.sub_total as total',

            ])->whereHas('invoice_particular', function($query) {
                $query->join('hms_particular','hms_particular.id','=','hms_invoice_particular.particular_id')
                    ->join('hms_particular_type','hms_particular_type.id','=','hms_particular.particular_type_id')
                    ->join('hms_particular_master_type','hms_particular_master_type.id','=','hms_particular_type.particular_master_type_id')
                    ->where('hms_particular_master_type.slug','investigation')
                    ->where('hms_particular.is_available',1);
            });

        if (isset($request['term']) && !empty($request['term'])){
            $term = trim($request['term']);
            $entities = $entities->where(function ($q) use ($term) {
                $q->where('hms_invoice.invoice', 'LIKE', "%{$term}%")
                    ->orWhere('customer.customer_id', 'LIKE', "%{$term}%")
                    ->orWhere('customer.name', 'LIKE', "%{$term}%")
                    ->orWhere('customer.mobile', 'LIKE', "%{$term}%")
                    ->orWhere('customer.nid', 'LIKE', "%{$term}%")
                    ->orWhere('customer.health_id', 'LIKE', "%{$term}%");
            });
        }

        if (isset($request['process']) && !empty($request['process'])){
            $entities = $entities->where('hms_invoice.process',$request['process']);
        }
        if (isset($request['customer_id']) && !empty($request['customer_id'])){
            $entities = $entities->where('hms_invoice.customer_id',$request['customer_id']);
        }
        if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
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

    public static function getLabReports($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $entities = InvoiceParticularModel::where([
            ['hms_invoice.config_id', $domain['hms_config']]
        ])
            ->join('hms_invoice as hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_particular.hms_invoice_id')
            ->leftJoin('users as createdBy', 'createdBy.id', '=', 'hms_invoice.created_by_id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->join('cor_customers as customer', 'customer.id', '=', 'hms_invoice.customer_id')
            ->join('hms_particular as hms_particular', 'hms_particular.id', '=', 'hms_invoice_particular.particular_id')
            ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->leftJoin('inv_category as inv_category', 'inv_category.id', '=', 'hms_particular.category_id') // ✅ fixed here
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')

            ->select([
                'hms_invoice_particular.id',
                'hms_invoice_particular.uid',
                'hms_invoice_particular.barcode',
                'hms_invoice_particular.name as investigation',
                'hms_invoice_particular.process',
                'inv_category.name as category_name',
                'hms_invoice.id as invoice_id',
                'hms_invoice.uid as invoice_uid',
                'hms_invoice.invoice as invoice',
                'customer.customer_id as patient_id',
                'patient_mode.name as mode',
                'customer.name',
                'customer.mobile',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice_particular.created_at, "%d-%m-%Y") as created_at'),
                'vr.display_name as room',
                'createdBy.name as created_by'
            ])
            ->where('hms_particular_master_type.slug', 'investigation')
            ->whereNotNull('hms_invoice_particular.invoice_transaction_id')
            ->where('hms_invoice_particular.is_available', 1)
            ->where('hms_invoice_particular.mode','investigation')
            ->where('hms_invoice_particular.status',1);

        if (isset($request['term']) && !empty($request['term'])){
            $term = trim($request['term']);
            $entities = $entities->where(function ($q) use ($term) {
                $q->where('hms_invoice.invoice', 'LIKE', "%{$term}%")
                    ->orWhere('customer.customer_id', 'LIKE', "%{$term}%")
                    ->orWhere('customer.name', 'LIKE', "%{$term}%")
                    ->orWhere('customer.mobile', 'LIKE', "%{$term}%")
                    ->orWhere('customer.nid', 'LIKE', "%{$term}%")
                    ->orWhere('customer.health_id', 'LIKE', "%{$term}%")
                    ->orWhere('hms_invoice.uid', 'LIKE', "%{$term}%")
                    ->orWhere('hms_invoice_particular.uid', 'LIKE', "%{$term}%");
            });
        }

        if (isset($request['process']) && !empty($request['process']) && $request['process'] != "all"){
            $entities = $entities->where('hms_invoice_particular.process',$request['process']);
        }

        if (isset($request['customer_id']) && !empty($request['customer_id'])){
            $entities = $entities->where('hms_invoice.customer_id',$request['customer_id']);
        }
        if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_particular.created_at',[$start_date, $end_date]);
        }
        $total  = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('hms_invoice.updated_at','DESC')
            ->get();
        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }

    public static function getShow($id)
    {
        $entity = self::where([
            ['hms_invoice.uid', '=', $id]
        ])
            ->leftjoin('cor_customers','cor_customers.id','=','hms_invoice.customer_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->leftjoin('hms_prescription as prescription','prescription.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('users as prescription_doctor','prescription_doctor.id','=','prescription.created_by_id')
            ->leftjoin('hms_particular as room','room.id','=','hms_invoice.room_id')
            ->leftjoin('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('hms_particular as admit_consultant','admit_consultant.id','=','hms_invoice.admit_consultant_id')
            ->leftjoin('hms_particular as admit_doctor','admit_doctor.id','=','hms_invoice.admit_doctor_id')
            ->leftjoin('hms_particular_mode as admit_unit','admit_unit.id','=','hms_invoice.admit_unit_id')
            ->leftjoin('hms_particular_mode as admit_department','admit_department.id','=','hms_invoice.admit_department_id')
            ->leftjoin('hms_particular_mode as particular_payment_mode','particular_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                'hms_invoice.*',
                DB::raw('DATE_FORMAT(hms_invoice.updated_at, "%d-%m-%y") as created'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%m-%y") as appointment'),
                'hms_invoice.invoice as invoice',
                'hms_invoice.total as total',
                'hms_invoice.comment',
                'hms_invoice.guardian_name as guardian_name',
                'hms_invoice.guardian_mobile as guardian_mobile',
                'cor_customers.name as name',
                'cor_customers.mobile as mobile',
                'cor_customers.id as customer_id',
                'cor_customers.customer_id as patient_id',
                'cor_customers.health_id as health_id',
                'cor_customers.gender as gender',
                'cor_customers.father_name',
                'cor_customers.mother_name',
                'cor_customers.upazilla_id',
                'cor_customers.country_id',
                'cor_customers.profession',
                'cor_customers.religion_id',
                'cor_customers.nid',
                'cor_customers.identity_mode',
                'cor_customers.address',
                'cor_customers.permanent_address',
                DB::raw('DATE_FORMAT(cor_customers.dob, "%d-%m-%y") as dob'),
                'cor_customers.identity_mode as identity_mode',
                'hms_invoice.year as year',
                'hms_invoice.month as month',
                'hms_invoice.day as day',
                'createdBy.username as created_by_user_name',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'room.name as room_name',
                'patient_mode.name as mode_name',
                'particular_payment_mode.name as payment_mode_name',
                'hms_invoice.process as process',
                'admit_consultant.name as admit_consultant_name',
                'admit_unit.name as admit_unit_name',
                'admit_department.name as admit_department_name',
                'admit_doctor.name as admit_doctor_name',
                'prescription.id as prescription_id',
                DB::raw('DATE_FORMAT(prescription.created_at, "%d-%m-%Y") as prescription_created'),
                'prescription_doctor.employee_id as prescription_doctor_id',
                'prescription_doctor.name as prescription_doctor_name',
            ])
            ->with([
                'invoice_transaction' => function ($query) {
                    $query->select([
                        'hms_invoice_transaction.id',
                        'hms_invoice_transaction.hms_invoice_id',
                        'hms_invoice_transaction.mode',
                        'hms_invoice_transaction.process',
                        'hms_invoice_transaction.total',
                        'hms_invoice_transaction.created_at'
                    ])
                        ->where('hms_invoice_transaction.mode', 'investigation')
                        ->where('hms_invoice_transaction.process', 'Done')
                        ->whereHas('items', function ($itemQuery) {
                            $itemQuery->join('hms_particular as hp', 'hp.id', '=', 'hms_invoice_particular.particular_id')
                                ->where('hms_invoice_particular.status', 1)
                                ->where('hp.is_available', 1);
                        }, '>=', 1)
                        ->orderBy('hms_invoice_transaction.created_at', 'DESC')
                        ->with([
                            'items' => function ($query) {
                                $query->select([
                                    'hms_invoice_particular.invoice_transaction_id as invoice_transaction_id',
                                    'hms_invoice_particular.uid as invoice_particular_id',
                                    'hms_invoice_particular.hms_invoice_id',
                                    'hms_invoice_particular.name as item_name',
                                    'hms_invoice_particular.quantity',
                                    'hms_invoice_particular.price',
                                    'hms_particular.is_available',
                                    'hms_particular.display_name',
                                    'hms_invoice_particular.sample_collected_name',
                                    'hms_invoice_particular.report_delivered_name',
                                    'hms_invoice_particular.assign_labuser_name',
                                    'hms_invoice_particular.assign_doctor_name',
                                    'hms_invoice_particular.process',
                                    'hms_invoice_particular.barcode',
                                    'hms_invoice_particular.uid',
                                    DB::raw('DATE_FORMAT(hms_invoice_particular.collection_date, "%d-%m-%Y") as collection_date'),
                                ])
                                    ->join('hms_particular as hms_particular', 'hms_particular.id', '=', 'hms_invoice_particular.particular_id')
                                    ->join('hms_particular_type as hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
                                    ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
                                    ->where('hms_particular_master_type.slug', 'investigation')
                                    ->where('hms_invoice_particular.status', 1)
                                    ->where('hms_particular.is_available', 1);
                            }
                        ]);
                }
            ])
            ->first();

        return $entity;
    }

    public static function generateReport($reportId)
    {
         $entity = InvoiceParticularModel::with('particular')->where(['uid' => $reportId])->first();
         $date =  new \DateTime("now");
         if($entity->particular->is_custom_report == 1){
             InvoiceParticularTestReportModel::updateOrCreate(
                 [
                     'invoice_particular_id'     => $entity->id,
                 ],
                 [
                     'updated_at'    => $date,
                     'created_at'    => $date,
                 ]
             );
         }else{
             $investigation = $entity->particular_id;
             $reportElements = InvestigationReportFormatModel::where('particular_id',$investigation)->get();
             foreach ($reportElements as $row):
                 $exist = InvoicePathologicalReportModel::where([
                     ['invoice_particular_id', $entity->id],
                     ['particular_id', $investigation],
                     ['investigation_report_format_id', $row->id],
                 ])->first();
                 if(empty($exist)){
                     $input =[
                         'invoice_particular_id' => $entity->id,
                         'particular_id' => $investigation,
                         'investigation_report_format_id' => $row->id,
                         'name' => $row->name,
                         'reference_value' => $row->reference_value,
                         'unit' => $row->unit,
                         'sample_value' => $row->sample_value
                     ];
                     InvoicePathologicalReportModel::create($input);
                 }
             endforeach;
         }


    }


}
