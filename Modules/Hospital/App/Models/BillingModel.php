<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\CustomerModel;


class BillingModel extends Model
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
        $total = 0;
        $entities=[];
        if ((isset($request['term']) && !empty($request['term'])) || isset($request['created'])) {
            $entities = InvoiceModel::where([['hms_invoice.config_id', $domain['hms_config']]])
                ->leftjoin('hms_prescription as prescription', 'prescription.hms_invoice_id', '=', 'hms_invoice.id')
                ->leftjoin('users as doctor', 'doctor.id', '=', 'prescription.created_by_id')
                ->leftjoin('hms_particular as vr', 'vr.id', '=', 'hms_invoice.room_id')
                ->leftjoin('users as createdBy', 'createdBy.id', '=', 'hms_invoice.created_by_id')
                ->join('cor_customers as customer', 'customer.id', '=', 'hms_invoice.customer_id')
                ->join('hms_particular_mode as patient_mode', 'patient_mode.id', '=', 'hms_invoice.patient_mode_id')
                ->select([
                    'hms_invoice.id',
                    'hms_invoice.uid',
                    'hms_invoice.barcode',
                    'prescription.created_by_id as prescription_created_by_id',
                    'hms_invoice.invoice as invoice',
                    'customer.customer_id as patient_id',
                    'doctor.name as doctor_name',
                    'customer.name',
                    'customer.mobile',
                    'patient_mode.name as patient_mode_name',
                    'patient_mode.slug as patient_mode_slug',
                    DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                    DB::raw('DATE_FORMAT(hms_invoice.created_at, "%Y-%m-%d") as created_at'),
                    'hms_invoice.process as process',
                    'vr.display_name as display_name',
                    'createdBy.name as created_by',
                    'hms_invoice.sub_total as total',
                    'hms_invoice.amount as amount',
                ]);

            if (isset($request['term']) && !empty($request['term'])) {
                $term = trim($request['term']);
                $entities = $entities->where(function ($q) use ($term) {
                    $q->where('hms_invoice.invoice', 'LIKE', "%{$term}%")
                        ->orWhere('hms_invoice.uid', 'LIKE', "%{$term}%")
                        ->orWhere('customer.customer_id', 'LIKE', "%{$term}%")
                        ->orWhere('customer.name', 'LIKE', "%{$term}%")
                        ->orWhere('customer.mobile', 'LIKE', "%{$term}%")
                        ->orWhere('customer.nid', 'LIKE', "%{$term}%")
                        ->orWhere('customer.health_id', 'LIKE', "%{$term}%");
                });
            }

            if (isset($request['admission_billing']) && !empty($request['admission_billing']) && $request['admission_billing'] == 'billing') {
                $entities = $entities->whereNotNull('parent_id');
                $entities = $entities->where('hms_invoice.process','billing');
            }
            if (isset($request['patient_mode']) && !empty($request['patient_mode'])) {
                if (is_array($request['patient_mode'])) {
                    $entities = $entities->whereIn('patient_mode.slug', $request['patient_mode']);
                } else {
                    $entities = $entities->where('patient_mode.slug', $request['patient_mode']);
                }
            }
            if (isset($request['created'])) {
                $date = !empty($request['created'])
                    ? new \DateTime($request['created'])
                    : new \DateTime();
                $start_date = $date->format('Y-m-d 00:00:00');
                $end_date = $date->format('Y-m-d 23:59:59');
                $entities = $entities->whereBetween('hms_invoice.updated_at', [$start_date, $end_date]);
            }
            $total = $entities->count();
            $entities = $entities->skip($skip)
                ->take($perPage)
                ->groupBy('hms_invoice.id')
                ->orderBy('hms_invoice.updated_at', 'DESC')
                ->get();
        }
        $data = array('count' => $total,'entities' => $entities);
        return $data;
    }

    public static function getFinalBillRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $entities = InvoiceModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->leftjoin('hms_prescription as prescription','prescription.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('users as doctor','doctor.id','=','prescription.created_by_id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->join('cor_customers as customer','customer.id','=','hms_invoice.customer_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->select([
                'hms_invoice.id',
                'hms_invoice.uid',
                'hms_invoice.barcode',
                'prescription.created_by_id as prescription_created_by_id',
                'hms_invoice.invoice as invoice',
                'customer.customer_id as patient_id',
                'doctor.name as doctor_name',
                'customer.name',
                'customer.mobile',
                'patient_mode.name as patient_mode_name',
                'patient_mode.slug as patient_mode_slug',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d-%m-%Y") as created_at'),
                DB::raw('DATE_FORMAT(hms_invoice.admission_date, "%d-%m-%Y") as admission_date'),
                'hms_invoice.process as process',
                'vr.display_name as display_name',
                'createdBy.name as created_by',
                'hms_invoice.sub_total as total',
                'hms_invoice.amount as amount',
                'hms_invoice.admission_day as admission_day',
                'hms_invoice.consume_day as consume_day',
                'hms_invoice.remaining_day as remaining_day',
            ]);

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

        $entities = $entities->whereIn('hms_invoice.process', ['done','admitted']);
        $entities = $entities->whereIn('hms_invoice.release_mode', ['discharge','death','referred']);

        if (isset($request['room_id']) && !empty($request['room_id'])){
            $entities = $entities->where('hms_invoice.room_id',$request['room_id']);
        }

        if (isset($request['customer_id']) && !empty($request['customer_id'])){
            $entities = $entities->where('hms_invoice.customer_id',$request['customer_id']);
        }

        if (isset($request['created']) && !empty($request['created'])){
            $date = !empty($request['created'])
                ? new \DateTime($request['created'])
                : new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date   = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice.created_at', [$start_date, $end_date]);
        }

        $total  = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->groupBy('hms_invoice.id')
            ->orderBy('hms_invoice.updated_at','DESC')
            ->get();
        $data = array('count' => $total,'entities' => $entities);
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
                'room.display_name as room_name',
                'room.price as room_price',
                'patient_mode.name as mode_name',
                'patient_mode.slug as mode_slug',
                'particular_payment_mode.name as payment_mode_name',
                'patient_mode.slug as patient_mode_slug',
                'hms_invoice.process as process',
                'admit_consultant.name as admit_consultant_name',
                'admit_unit.name as admit_unit_name',
                'admit_department.name as admit_department_name',
                'admit_doctor.name as admit_doctor_name',
                'prescription.id as prescription_id',
                DB::raw('DATE_FORMAT(prescription.created_at, "%d-%m-%y") as prescription_created'),
                'prescription_doctor.employee_id as prescription_doctor_id',
                'prescription_doctor.name as prescription_doctor_name',
                'hms_invoice.admission_day as room_admission_day',
                'hms_invoice.consume_day as room_consume_day',
                'hms_invoice.remaining_day as room_remaining_day',
            ])
            ->with(['invoice_transaction' => function ($query) {
                $query->select([
                    'hms_invoice_transaction.id as hms_invoice_transaction_id',
                    'hms_invoice_transaction.hms_invoice_id',
                    'hms_invoice_transaction.created_by_id',
                    'hms_invoice_transaction.mode',
                    'hms_invoice_transaction.sub_total',
                    'hms_invoice_transaction.total',
                    'hms_invoice_transaction.amount',
                    'hms_invoice_transaction.process',
                    DB::raw('DATE_FORMAT(hms_invoice_transaction.created_at, "%d-%m-%y") as created'),
                ])->where('hms_invoice_transaction.process','Done')->whereIn('hms_invoice_transaction.mode', ['investigation', 'admission', 'room'])->orderBy('hms_invoice_transaction.created_at','DESC');
            }])
            ->with(['invoice_particular' => function ($query) {
                $query->select([
                    'hms_invoice_particular.id',
                    'hms_invoice_particular.id as particular_id',
                    'hms_invoice_particular.particular_id as particular_investigation_id',
                    'hms_invoice_particular.hms_invoice_id',
                    'hms_invoice_particular.name',
                    'hms_invoice_particular.price',
                    'hms_invoice_particular.quantity',
                    'hms_invoice_particular.sub_total',
                    'hms_invoice_particular.process',
                ])->whereNull('hms_invoice_particular.invoice_transaction_id')
                    ->whereNull('hms_invoice_particular.patient_waiver_id')
                    ->where('hms_invoice_particular.status',0)
                    ->where('hms_invoice_particular.is_available',1)
                    ->whereIn('hms_invoice_particular.mode', ['investigation'])
                    ->groupBy('hms_invoice_particular.particular_id')
                    ->orderBy('hms_invoice_particular.name','ASC');
            }])
            ->first();

        return $entity;
    }

    public static function getAdmissionBilling($id)
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
                'room.display_name as room_name',
                'room.price as room_price',
                'patient_mode.name as mode_name',
                'patient_mode.slug as mode_slug',
                'particular_payment_mode.name as payment_mode_name',
                'patient_mode.slug as patient_mode_slug',
                'hms_invoice.process as process',
                'admit_consultant.name as admit_consultant_name',
                'admit_unit.name as admit_unit_name',
                'admit_department.name as admit_department_name',
                'admit_doctor.name as admit_doctor_name',
                'prescription.id as prescription_id',
                DB::raw('DATE_FORMAT(prescription.created_at, "%d-%m-%y") as prescription_created'),
                'prescription_doctor.employee_id as prescription_doctor_id',
                'prescription_doctor.name as prescription_doctor_name',
                'hms_invoice.admission_day as room_admission_day',
                'hms_invoice.consume_day as room_consume_day',
                'hms_invoice.remaining_day as room_remaining_day',
            ])
            ->with(['invoice_transaction' => function ($query) {
                $query->select([
                    'hms_invoice_transaction.id as hms_invoice_transaction_id',
                    'hms_invoice_transaction.hms_invoice_id',
                    'hms_invoice_transaction.created_by_id',
                    'hms_invoice_transaction.mode',
                    'hms_invoice_transaction.sub_total',
                    'hms_invoice_transaction.total',
                    'hms_invoice_transaction.amount',
                    'hms_invoice_transaction.process',
                    DB::raw('DATE_FORMAT(hms_invoice_transaction.created_at, "%d-%m-%y") as created'),
                ])->where('hms_invoice_transaction.process','New')->whereIn('hms_invoice_transaction.mode', ['ipd'])->orderBy('hms_invoice_transaction.created_at','DESC');
            }])
            ->with(['invoice_particular' => function ($query) {
                $query->select([
                    'hms_invoice_particular.id',
                    'hms_invoice_particular.id as particular_id',
                    'hms_invoice_particular.hms_invoice_id',
                    'hms_invoice_particular.name',
                    'hms_invoice_particular.price',
                    'hms_invoice_particular.quantity',
                    'hms_invoice_particular.sub_total',
                    'hms_invoice_particular.process',
                ])->whereIn('hms_invoice_particular.mode', ['admission','ipd','room'])->where('status',0)->orderBy('hms_invoice_particular.created_at','DESC');
            }])
            ->first();

        return $entity;
    }

    public static function generateReport($reportId)
    {
        $entity = InvoiceParticularModel::find($reportId);
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

    public static function getFinalBillShow($id)
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
                'room.display_name as room_name',
                'room.price as room_price',
                'patient_mode.name as mode_name',
                'patient_mode.slug as mode_slug',
                'particular_payment_mode.name as payment_mode_name',
                'patient_mode.slug as patient_mode_slug',
                'hms_invoice.process as process',
                'admit_consultant.name as admit_consultant_name',
                'admit_unit.name as admit_unit_name',
                'admit_department.name as admit_department_name',
                'admit_doctor.name as admit_doctor_name',
                'prescription.id as prescription_id',
                DB::raw('DATE_FORMAT(prescription.created_at, "%d-%m-%y") as prescription_created'),
                'prescription_doctor.employee_id as prescription_doctor_id',
                'prescription_doctor.name as prescription_doctor_name',
                'hms_invoice.admission_day as room_admission_day',
                'hms_invoice.consume_day as room_consume_day',
                'hms_invoice.remaining_day as room_remaining_day',
            ])
            ->with(['invoice_transaction' => function ($query) {
                $query->select([
                    'hms_invoice_transaction.id as hms_invoice_transaction_id',
                    'hms_invoice_transaction.hms_invoice_id',
                    'hms_invoice_transaction.created_by_id',
                    'hms_invoice_transaction.mode',
                    'hms_invoice_transaction.sub_total',
                    'hms_invoice_transaction.total',
                    'hms_invoice_transaction.amount',
                    'hms_invoice_transaction.process',
                    DB::raw('DATE_FORMAT(hms_invoice_transaction.created_at, "%d-%m-%y") as created'),
                ])->where('hms_invoice_transaction.process','Done')->whereIn('hms_invoice_transaction.mode', ['investigation', 'admission', 'room'])->orderBy('hms_invoice_transaction.created_at','ASC');
            }])
            ->first();

        return $entity;
    }


}
