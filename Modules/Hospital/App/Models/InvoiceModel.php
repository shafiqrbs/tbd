<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\App\Models\CustomerModel;
use Modules\Hospital\App\Entities\PatientPrescriptionMedicineDailyHistory;
use Modules\Inventory\App\Models\SalesModel;


class InvoiceModel extends Model
{
    use HasFactory;

    protected $table = 'hms_invoice';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];


    public static function findByIdOrUid($id)
    {
        return self::where('id', $id)
            ->orWhere('uid', $id)
            ->first();
    }

    public static function generateUniqueCode($length = 12)
    {
        do {
            // Generate a random 12-digit number
            $code = str_pad(random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (self::where('uid', $code)->exists());
        return $code;
    }
    public static function generateUniqueBarcode($length = 12)
    {
        do {
            // Generate a random 12-digit number
            $code = str_pad(random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (self::where('barcode', $code)->exists());
        return $code;
    }

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            if (empty($model->barcode)) {
                $model->barcode = self::generateUniqueBarcode(12);
                $model->uid = self::generateUniqueCode(12);
            }
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

    public function invoice_particular()
    {
        return $this->hasMany(InvoiceParticularModel::class, 'hms_invoice_id');
    }

    public function invoice_transaction()
    {
        return $this->hasMany(InvoiceTransactionModel::class, 'hms_invoice_id');
    }

    public function room()
    {
        return $this->hasOne(ParticularModel::class, 'id', 'room_id');
    }
    public function patient_payment_mode()
    {
        return $this->hasOne(OpdModel::class, 'id', 'patient_payment_mode_id');
    }
    public function patient_mode()
    {
        return $this->hasOne(OpdModel::class, 'id', 'patient_mode_id');
    }
    public function sales()
    {
        return $this->belongsTo(SalesModel::class, 'sales_id');
    }

    public function children()
    {
        return $this->hasOne(InvoiceModel::class, 'parent_id');
    }

    public function prescription_medicine()
    {
        return $this->hasMany(PatientPrescriptionMedicineModel::class, 'hms_invoice_id');
    }

    public function prescription_medicine_history()
    {
        return $this->hasMany(AdmissionPatientPrescriptionHistoryModel::class, 'hms_invoice_id');
    }

    public static function getCustomerSearch($domain,$request)
    {

        $term = trim($request['term']);
        $entities = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->join('cor_customers as customer','customer.id','=','hms_invoice.customer_id')
            ->select([
                'hms_invoice.id',
                'customer.id as customer_id',
                'customer.customer_id as patient_id',
                'customer.health_id',
                'customer.nid as nid',
                'hms_invoice.invoice as invoice',
                'customer.name as name',
                'customer.mobile as mobile',
            ]);
        $entities = $entities->where(function ($q) use ($term) {
            $q->where('hms_invoice.invoice', 'LIKE', "%{$term}%")
                ->orWhere('customer.customer_id', 'LIKE', "%{$term}%")
                ->orWhere('customer.name', 'LIKE', "%{$term}%")
                ->orWhere('customer.mobile', 'LIKE', "%{$term}%")
                ->orWhere('customer.nid', 'LIKE', "%{$term}%")
                ->orWhere('customer.health_id', 'LIKE', "%{$term}%");
        });
        $entities = $entities
            ->groupBy('customer.customer_id')
            ->orderBy('customer.customer_id','ASC')
            ->get();
        return $entities;

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
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->join('hms_particular_mode as patient_payment_mode','patient_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                'hms_invoice.id',
                'hms_invoice.uid',
                'hms_invoice.parent_id as parent_id',
                'prescription.id as prescription_id',
                'prescription.uid as prescription_uid',
                'prescription.created_by_id as prescription_created_by_id',
                'hms_invoice.invoice as invoice',
                'hms_invoice.barcode  as barcode',
                'customer.customer_id as patient_id',
                'customer.health_id',
                'doctor.name as doctor_name',
                'customer.name',
                'customer.mobile',
                'customer.address',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d-%m-%Y") as created_at'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%M-%Y") as appointment'),
                DB::raw('DATE_FORMAT(hms_invoice.admission_date, "%d-%M-%Y") as admission_date'),
                DB::raw('DATE_FORMAT(customer.dob, "%d-%M-%Y") as dob'),
                'hms_invoice.process as process',
                'vr.name as visiting_room',
                'vr.display_name as room_name',
                'patient_mode.name as patient_mode_name',
                'patient_payment_mode.name as patient_payment_mode_name',
                'patient_payment_mode.slug as patient_payment_mode_slug',
                'createdBy.name as created_by',
                'hms_invoice.sub_total as total',
                'hms_invoice.amount as amount',
                'hms_invoice.referred_mode as referred_mode',
                'prescription.diabetes as diabetes',
                'prescription.blood_pressure as blood_pressure',
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

        if (isset($request['prescription_mode']) && !empty($request['prescription_mode'])){
            if (!empty($request['prescription_mode'])) {
                if ($request['prescription_mode'] === 'prescription') {
                    $entities = $entities->whereNotNull('prescription.id');
                    $entities = $entities->where('hms_invoice.is_prescription',1);
                } elseif ($request['prescription_mode'] === 'non-prescription') {
                    $entities = $entities->where(function ($query) {
                        $query->whereNull('prescription.id')
                            ->orWhere('hms_invoice.is_prescription', 0);
                    });
                }elseif(in_array($request['prescription_mode'],['room','admission','referred'])) {
                    $entities = $entities->where('hms_invoice.referred_mode',$request['prescription_mode']);
                }
            }
        }

        if (isset($request['ipd_mode']) && !empty($request['ipd_mode'])){
            if ($request['ipd_mode'] === 'new' and !empty($request['referred_mode']) and $request['referred_mode']) {
                $entities = $entities->where('hms_invoice.process','ipd');
                $entities = $entities->where('hms_invoice.referred_mode', $request['referred_mode'])
                    ->whereNull('hms_invoice.parent_id')->whereDoesntHave('children');
            } elseif ($request['ipd_mode'] === 'confirmed') {
                $entities = $entities->where('hms_invoice.process','confirmed');
                $entities = $entities->whereNotNull('hms_invoice.parent_id');
            } elseif ($request['ipd_mode'] === 'admitted') {
                $entities = $entities->where('hms_invoice.process','admitted');
                $entities = $entities->whereNotNull('hms_invoice.parent_id');
            }
            $entities
                ->leftJoin('hms_particular as admit_consultant', 'admit_consultant.id', '=', 'hms_invoice.admit_consultant_id')
                ->leftJoin('hms_particular as admit_doctor', 'admit_doctor.id', '=', 'hms_invoice.admit_doctor_id')
                ->leftJoin('hms_particular_mode as admit_unit', 'admit_unit.id', '=', 'hms_invoice.admit_unit_id')
                ->leftJoin('hms_particular_mode as admit_department', 'admit_department.id', '=', 'hms_invoice.admit_department_id')
                ->addSelect([
                    'admit_consultant.name as admit_consultant_name',
                    'admit_doctor.name as admit_doctor_name',
                    'admit_unit.name as admit_unit_name',
                    'admit_department.name as admit_department_name',
                ]);
        }
        if (isset($request['patient_mode']) && !empty($request['patient_mode'])){
            if (is_array($request['patient_mode'])) {
                $entities = $entities->whereIn('patient_mode.slug', $request['patient_mode']);
            } else {
                $entities = $entities->where('patient_mode.slug', $request['patient_mode']);
            }
        }
        if (isset($request['is_vital']) && !empty($request['is_vital'])){
            $entities = $entities ->addSelect([
                'hms_invoice.weight as weight',
                'hms_invoice.height as height',
                'hms_invoice.bp  as bp',
                'hms_invoice.oxygen  as oxygen',
                'hms_invoice.temperature  as temperature',
                'hms_invoice.sat_with_O2  as sat_with_O2',
                'hms_invoice.sat_without_O2  as sat_without_O2',
                'hms_invoice.sat_liter  as sat_liter',
                'hms_invoice.respiration  as respiration',
                'hms_invoice.pulse  as pulse',
            ]);
            $entities = $entities->where('hms_invoice.is_vital',1);
            $entities = $entities->whereIn('hms_invoice.process', ['New','In-progress']);
        }

        if (isset($request['process']) && !empty($request['process'])){
            $entities = $entities->where('hms_invoice.process',$request['process']);
        }

        if (isset($request['room_id']) && !empty($request['room_id'])){
            $entities = $entities->where('hms_invoice.room_id',$request['room_id']);
        }

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $entities = $entities->where('hms_invoice.created_by_id',$request['created_by_id']);
        }

         if (isset($request['customer_id']) && !empty($request['customer_id'])){
            $entities = $entities->where('hms_invoice.customer_id',$request['customer_id']);
        }

        /*if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }*/

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

            $entity = self::where(function ($query) use ($id) {
                $query->where('hms_invoice.id', '=', $id)
                    ->orWhere('hms_invoice.uid', '=', $id);
            })
            ->leftjoin('cor_customers','cor_customers.id','=','hms_invoice.customer_id')
            ->leftjoin('cor_locations','cor_locations.id','=','cor_customers.upazilla_id')
            ->leftjoin('inv_sales','inv_sales.id','=','hms_invoice.sales_id')
            ->leftjoin('hms_prescription as prescription','prescription.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('users as doctor','doctor.id','=','prescription.created_by_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->leftjoin('hms_particular as room','room.id','=','hms_invoice.room_id')
            ->leftjoin('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('hms_particular_mode as particular_payment_mode','particular_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->leftjoin('hms_invoice as invoice_parent','invoice_parent.id','=','hms_invoice.parent_id')
            ->leftjoin('hms_particular_mode as parent_patient_mode','parent_patient_mode.id','=','invoice_parent.patient_mode_id')
            ->select([
                'hms_invoice.*',
                DB::raw('DATE_FORMAT(hms_invoice.updated_at, "%d-%m-%y") as created'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%m-%y") as appointment'),
                'hms_invoice.invoice as invoice',
                'parent_patient_mode.name as parent_patient_mode_name',
                'parent_patient_mode.slug as parent_patient_mode_slug',
                'hms_invoice.total as total',
                'hms_invoice.barcode',
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
                'cor_locations.upazila',
                'cor_locations.district',
                'cor_customers.country_id',
                'cor_customers.profession',
                'cor_customers.religion_id',
                'cor_customers.nid',
                'cor_customers.identity_mode',
                'cor_customers.address',
                'cor_customers.permanent_address',
                DB::raw('DATE_FORMAT(cor_customers.dob, "%d-%m-%y") as dob'),
                DB::raw('DATE_FORMAT(cor_customers.dob,"%Y-%m-%d") as date_of_birth'),
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
                'hms_invoice.referred_mode as referred_mode',
            ])
            ->with(['invoice_particular' => function ($query) {
                $query->select([
                    'hms_invoice_particular.id',
                    'hms_invoice_particular.hms_invoice_id',
                    'hms_invoice_particular.name as item_name',
                    'hms_invoice_particular.quantity',
                    'hms_invoice_particular.price',
                ]);
            }])->with(['prescription_medicine'])
            ->first();

        return $entity;
    }

    public static function getInvoiceBasicInfo($id)
    {
        $entity = self::where(function ($query) use ($id) {
            $query->where('hms_invoice.id', '=', $id)
                ->orWhere('hms_invoice.uid', '=', $id);
        })
            ->leftjoin('cor_customers','cor_customers.id','=','hms_invoice.customer_id')
            ->leftjoin('inv_sales','inv_sales.id','=','hms_invoice.sales_id')
            ->leftjoin('hms_prescription as prescription','prescription.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('users as doctor','doctor.id','=','prescription.created_by_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->leftjoin('hms_particular as room','room.id','=','hms_invoice.room_id')
            ->leftjoin('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('hms_particular_mode as particular_payment_mode','particular_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->leftJoin('hms_particular as admit_consultant', 'admit_consultant.id', '=', 'hms_invoice.admit_consultant_id')
            ->leftJoin('hms_particular as admit_doctor', 'admit_doctor.id', '=', 'hms_invoice.admit_doctor_id')
            ->leftJoin('hms_particular_mode as admit_unit', 'admit_unit.id', '=', 'hms_invoice.admit_unit_id')
            ->leftJoin('hms_particular_mode as admit_department', 'admit_department.id', '=', 'hms_invoice.admit_department_id')
            ->leftjoin('hms_invoice as invoice_parent','invoice_parent.id','=','hms_invoice.parent_id')
            ->leftjoin('hms_particular_mode as parent_patient_mode','parent_patient_mode.id','=','invoice_parent.patient_mode_id')

            ->select([
                'hms_invoice.id as invoice_id',
                DB::raw('DATE_FORMAT(hms_invoice.updated_at, "%d-%m-%y") as created'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%m-%y") as appointment'),
                'hms_invoice.invoice as invoice',
                'hms_invoice.parent_id as parent_id',
                'parent_patient_mode.name as parent_patient_mode_name',
                'parent_patient_mode.slug as parent_patient_mode_slug',
                'prescription.id as prescription_id',
                'hms_invoice.total as total',
                'admit_consultant.name as admit_consultant_name',
                'admit_doctor.name as admit_doctor_name',
                'admit_unit.name as admit_unit_name',
                'admit_unit.slug as admit_unit_slug',
                'admit_department.name as admit_department_name',
                'admit_department.slug as admit_department_slug',
                'cor_customers.name as name',
                'cor_customers.mobile as mobile',
                'cor_customers.id as customer_id',
                'cor_customers.customer_id as patient_id',
                'cor_customers.health_id as health_id',
                'cor_customers.gender as gender',
                'cor_customers.father_name',
                'cor_customers.mother_name',
                'cor_customers.upazilla_id',
                'cor_customers.nid',
                'cor_customers.identity_mode',
                DB::raw('DATE_FORMAT(cor_customers.dob, "%d-%m-%y") as dob'),
                'cor_customers.identity_mode as identity_mode',
                'createdBy.username as created_by_user_name',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'room.name as room_name',
                'patient_mode.name as patient_mode_name',
                'patient_mode.slug as patient_mode_slug',
                'particular_payment_mode.name as payment_mode_name',
                'particular_payment_mode.slug as payment_mode_slug',
                'hms_invoice.process as process',
                'hms_invoice.referred_mode as referred_mode',
            ])->with(['children:id'])->with(['prescription_medicine'])->first();

        return $entity;
    }

    public static function getIpdShow($id)
    {
        $entity = self::where(function ($query) use ($id) {
            $query->where('hms_invoice.id', '=', $id)
                ->orWhere('hms_invoice.uid', '=', $id);
        })
            ->leftjoin('cor_customers','cor_customers.id','=','hms_invoice.customer_id')
            ->leftjoin('cor_setting as religion','religion.id','=','cor_customers.religion_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->leftjoin('hms_prescription as prescription','prescription.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('hms_admission_patient_details as admission_patient','admission_patient.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('users as prescription_doctor','prescription_doctor.id','=','prescription.created_by_id')
            ->leftjoin('hms_particular as room','room.id','=','hms_invoice.room_id')
            ->leftjoin('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('hms_particular as admit_consultant','admit_consultant.id','=','hms_invoice.admit_consultant_id')
            ->leftjoin('hms_particular as admit_doctor','admit_doctor.id','=','hms_invoice.admit_doctor_id')
            ->leftjoin('hms_particular_mode as admit_unit','admit_unit.id','=','hms_invoice.admit_unit_id')
            ->leftjoin('hms_particular_mode as admit_department','admit_department.id','=','hms_invoice.admit_department_id')
            ->leftjoin('hms_particular_mode as particular_payment_mode','particular_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->leftjoin('hms_invoice as invoice_parent','invoice_parent.id','=','hms_invoice.parent_id')
            ->leftjoin('hms_particular_mode as parent_patient_mode','parent_patient_mode.id','=','invoice_parent.patient_mode_id')
            ->select([
                'hms_invoice.*',
                'parent_patient_mode.name as parent_patient_mode_name',
                'parent_patient_mode.slug as parent_patient_mode_slug',
                DB::raw('DATE_FORMAT(hms_invoice.updated_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%m-%Y") as appointment'),
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
                'religion.name as religion_name',
                'cor_customers.permanent_address',
                DB::raw('DATE_FORMAT(cor_customers.dob, "%m-%d-%Y") as dob'),
                'cor_customers.identity_mode as identity_mode',
                'hms_invoice.year as year',
                'hms_invoice.month as month',
                'hms_invoice.day as day',
                'createdBy.username as created_by_user_name',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'room.display_name as room_name',
                'patient_mode.name as mode_name',
                'particular_payment_mode.name as payment_mode_name',
                'hms_invoice.process as process',
                'admit_consultant.name as admit_consultant_name',
                'admit_unit.name as admit_unit_name',
                'admit_department.name as admit_department_name',
                'admit_doctor.name as admit_doctor_name',
                'prescription.id as prescription_id',
                'prescription.uid as prescription_uid',
                'prescription.json_content as json_content',
                'prescription_doctor.name as prescription_doctor_name',
                'admission_patient.vital_chart_json as vital_chart_json',
                'admission_patient.insulin_chart_json as insulin_chart_json',
            ])
            ->with(['invoice_particular' => function ($query) {
                $query->select([
                    'hms_invoice_particular.id',
                    'hms_invoice_particular.hms_invoice_id',
                    'hms_invoice_particular.name as item_name',
                    'hms_invoice_particular.quantity',
                    'hms_invoice_particular.price',
                    'hms_invoice_particular.sub_total',
                ]);
            }])
            ->with(['invoice_transaction' => function ($query) {
                $query->select([
                    'hms_invoice_transaction.id',
                    'hms_invoice_transaction.hms_invoice_id',
                    'hms_invoice_transaction.created_by_id',
                    'hms_invoice_transaction.mode',
                    'hms_invoice_transaction.sub_total',
                    'hms_invoice_transaction.process',
                    DB::raw('DATE_FORMAT(hms_invoice_transaction.created_at, "%d-%m-%y") as created'),
                ])->orderBy('hms_invoice_transaction.created_at','DESC');
            }])->with(['prescription_medicine'])->with('prescription_medicine_history')
            ->first();

        return $entity;
    }

    public static function getEditData($id,$domain)
    {
            $entity = self::where(function ($query) use ($id) {
                $query->where('hms_invoice.id', '=', $id)
                    ->orWhere('hms_invoice.uid', '=', $id);
            })
            ->leftjoin('cor_customers','cor_customers.id','=','hms_invoice.customer_id')
            ->leftjoin('inv_sales','inv_sales.id','=','hms_invoice.sales_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->leftjoin('particular as room','room.id','=','hms_invoice.room_id')
            ->leftjoin('particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('particular_payment_mode as patient_mode','particular_payment_mode.id','=','hms_invoice.particular_payment_mode_id')
            ->select([
                'hms_invoice.id as id',
                'hms_invoice.id as invoice_id',
                DB::raw('DATE_FORMAT(hms_invoice.updated_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment, "%d-%M-%Y") as created_date'),
                'inv_sales.invoice as invoice',
                'hms_invoice.total as total',
                'hms_invoice.guardian_name as guardian_name',
                'hms_invoice.guardian_mobile as guardian_mobile',
                'hms_invoice.comment',
                'cor_customers.name as name',
                'cor_customers.mobile as mobile',
                'cor_customers.customer_id as patient_id',
                'cor_customers.health_id as health_id',
                DB::raw('DATE_FORMAT(cor_customers.dob, "%d-%M-%Y") as dob'),
                'cor_customers.identity_mode as identity_mode',
                'cor_customers.address as address',
                'createdBy.username as created_by_user_name',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'hms_invoice.process as process_id',
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

    public static function getAllOpdRooms($domain)
    {
        $entities = ParticularModel::where([
            ['hms_particular.config_id', $domain['hms_config']],
            ['hms_particular_master_type.slug', 'opd-room'],
            ['hms_particular.status', 1],
        ])
            ->leftJoin('hms_invoice', function ($join) use ($domain) {
                $join->on('hms_invoice.room_id', '=', 'hms_particular.id')
                    ->where('hms_invoice.config_id', $domain['hms_config'])
                    ->whereDate('hms_invoice.created_at', Carbon::today()); // ✅ only today's invoices
            })
            ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->select([
                'hms_particular.id as id',
                'hms_particular.name',
                DB::raw('COUNT(hms_invoice.id) as invoice_count')
            ])
            ->groupBy('hms_particular.id', 'hms_particular.name')
            ->orderBy('hms_particular.name', 'ASC')
            ->get();

        return $entities;
    }

    public static function getOpdRooms($domain)
    {


        $date = new \DateTime();
        $start_date = $date->format('Y-m-d 00:00:00');
        $end_date   = $date->format('Y-m-d 23:59:59');

        $config = HospitalConfigModel::find($domain['hms_config']);
        $emergencyRoomId = $config->emergency_room_id ? $config->emergency_room_id:'';


        $entities = ParticularModel::where([
            ['hms_particular.config_id', $domain['hms_config']],
            ['hms_particular_master_type.slug', 'opd-room'],
            ['hms_particular.status', 1],
            ['hms_particular.id', '!=', $emergencyRoomId],
            ['hms_particular.opd_referred', '<>', 1]
        ])
            ->leftJoin('hms_invoice', function($join) use ($start_date, $end_date) {
                $join->on('hms_invoice.room_id', '=', 'hms_particular.id')
                    ->whereBetween('hms_invoice.created_at', [$start_date, $end_date]);
            })
            ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->select(
                'hms_particular.id as id',
                DB::raw("hms_particular.name as name"),
                DB::raw('COUNT(hms_invoice.id) as invoice_count')
            )
            ->groupBy('hms_particular.id', 'hms_particular.name')
            ->orderBy('hms_particular.name', 'ASC')
            ->get();

        $selected = ParticularModel::where([
            ['hms_particular.config_id', $domain['hms_config']],
            ['hms_particular_master_type.slug', 'opd-room'],
            ['hms_particular.status', 1],
            ['hms_particular.id', '!=', $emergencyRoomId],
            ['hms_particular.opd_referred', '<>', 1]
        ])
            ->leftJoin('hms_invoice', function($join) use ($start_date, $end_date) {
                $join->on('hms_invoice.room_id', '=', 'hms_particular.id')
                    ->whereBetween('hms_invoice.created_at', [$start_date, $end_date]);
            })
            ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->select(
                'hms_particular.id as id'
            )
            ->groupBy('hms_particular.id', 'hms_particular.name')
            ->orderBy(DB::raw('COUNT(hms_invoice.id)'), 'ASC')
            ->limit(1)
            ->get()->first()->id;

        return array('ipdRooms' => $entities ,'selectedRoom' => $selected);
    }

    public static function getIpdRooms($domain)
    {


        $date = new \DateTime();
        $start_date = $date->format('Y-m-d 00:00:00');
        $end_date   = $date->format('Y-m-d 23:59:59');

        $config = HospitalConfigModel::find($domain['hms_config']);
        $emergencyRoomId = $config->emergency_room_id ? $config->emergency_room_id:'';


        $entities = ParticularModel::where([
            ['hms_particular.config_id', $domain['hms_config']],
            ['hms_particular_master_type.slug', 'opd-room'],
            ['hms_particular.status', 1],
            ['hms_particular.id', '!=', $emergencyRoomId],
            ['hms_particular.opd_referred', '<>', 1]
        ])
            ->leftJoin('hms_invoice', function($join) use ($start_date, $end_date) {
                $join->on('hms_invoice.room_id', '=', 'hms_particular.id')
                    ->whereBetween('hms_invoice.created_at', [$start_date, $end_date]);
            })
            ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->select(
                'hms_particular.id as id',
                DB::raw("hms_particular.name as name"),
                DB::raw('COUNT(hms_invoice.id) as invoice_count')
            )
            ->groupBy('hms_particular.id', 'hms_particular.name')
            ->orderBy('hms_particular.name', 'ASC')
            ->get();

        $selected = ParticularModel::where([
            ['hms_particular.config_id', $domain['hms_config']],
            ['hms_particular_master_type.slug', 'opd-room'],
            ['hms_particular.status', 1],
            ['hms_particular.id', '!=', $emergencyRoomId],
            ['hms_particular.opd_referred', '<>', 1]
        ])
            ->leftJoin('hms_invoice', function($join) use ($start_date, $end_date) {
                $join->on('hms_invoice.room_id', '=', 'hms_particular.id')
                    ->whereBetween('hms_invoice.created_at', [$start_date, $end_date]);
            })
            ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->select(
                'hms_particular.id as id'
            )
            ->groupBy('hms_particular.id', 'hms_particular.name')
            ->orderBy(DB::raw('COUNT(hms_invoice.id)'), 'ASC')
            ->limit(1)
            ->get()->first()->id;

        return array('ipdRooms' => $entities ,'selectedRoom' => $selected);
    }

    public static function getNextOpdRoom($domain){

        $date = new \DateTime();
        $start_date = $date->format('Y-m-d 00:00:00');
        $end_date   = $date->format('Y-m-d 23:59:59');

        $selected = ParticularModel::where([
            ['hms_particular.config_id', $domain['hms_config']],
            ['hms_particular_master_type.slug', 'opd-room'],
            ['hms_particular.status', 1],
            ['hms_particular.opd_referred', '<>', 1]
        ])
            ->leftJoin('hms_invoice', function($join) use ($start_date, $end_date) {
                $join->on('hms_invoice.room_id', '=', 'hms_particular.id')
                    ->whereBetween('hms_invoice.created_at', [$start_date, $end_date]);
            })
            ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->select(
                'hms_particular.id as id',
                DB::raw("CONCAT(hms_particular.name, ' (', COUNT(hms_invoice.id), ')') as name"),
                DB::raw('COUNT(hms_invoice.id) as invoice_count')
            )
            ->groupBy('hms_particular.id', 'hms_particular.name')
            ->orderBy(DB::raw('COUNT(hms_invoice.id)'), 'ASC')
            ->limit(1)
            ->get()->first();

        return $selected;
    }

    public static function getOpdReferredRooms($domain)
    {
        $entities = ParticularModel::where([
            ['hms_particular.config_id', $domain['hms_config']],
            ['hms_particular_master_type.slug', 'opd-room'],
            ['hms_particular.status', 1],
            ['hms_particular.opd_referred', 1],
        ])
            ->leftJoin('hms_invoice', function ($join) use ($domain) {
                $join->on('hms_invoice.room_id', '=', 'hms_particular.id')
                    ->where('hms_invoice.config_id', $domain['hms_config'])
                    ->whereDate('hms_invoice.created_at', Carbon::today()); // ✅ only today's invoices
            })
            ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->select([
                'hms_particular.id as id',
                'hms_particular.name',
                DB::raw('COUNT(hms_invoice.id) as invoice_count')
            ])
            ->groupBy('hms_particular.id', 'hms_particular.name')
            ->orderBy('hms_particular.name', 'ASC')
            ->get();
        return $entities;
    }

    public static function getSummary($domain,$request){


        $summary = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->select([
                 DB::raw('COUNT(hms_invoice.id) as patient'),
                 DB::raw('SUM(hms_invoice.total)'),
            ]);

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $summary = $summary->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $summary = $summary->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }
        $summary = $summary->get();

        $userBase = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->join('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->select([
                DB::raw('hms_invoice.created_by_id as created_by_id'),
                DB::raw('createdBy.name as name'),
                DB::raw('COUNT(hms_invoice.id) as patient'),
                DB::raw('SUM(hms_invoice.total) as total'),
            ]);

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $userBase = $userBase->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $userBase = $userBase->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }
        $userBase->groupBy('hms_invoice.created_by_id');
        $userBase = $userBase->get();

        $roomBase = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->join('hms_particular as room','room.id','=','hms_invoice.room_id')
            ->select([
                DB::raw('room.name as name'),
                DB::raw('COUNT(hms_invoice.id) as patient'),
                DB::raw('SUM(hms_invoice.total) as total'),
            ]);

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $roomBase = $roomBase->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $roomBase = $roomBase->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }
        $roomBase->groupBy('room.name');
        $roomBase = $roomBase->get();


        $paymentMode = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->leftjoin('hms_particular_mode as particular_payment_mode','particular_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                DB::raw('particular_payment_mode.name as name'),
                DB::raw('COUNT(hms_invoice.id) as patient'),
                DB::raw('SUM(hms_invoice.total) as total'),
            ]);

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $paymentMode = $paymentMode->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $paymentMode = $paymentMode->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }
        $paymentMode->groupBy('particular_payment_mode.name');
        $paymentMode = $paymentMode->get();

        $patientMode = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->leftjoin('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->select([
                DB::raw('patient_mode.name as name'),
                DB::raw('COUNT(hms_invoice.id) as patient'),
                DB::raw('SUM(hms_invoice.total) as total'),
            ]);

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $patientMode = $patientMode->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $patientMode = $patientMode->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }
        $patientMode->groupBy('patient_mode.name');
        $patientMode = $patientMode->get();

        $doctorMode = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->join('hms_prescription as prescription','prescription.hms_invoice_id','=','hms_invoice.id')
            ->join('users as doctor','doctor.id','=','prescription.created_by_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->select([
                DB::raw('doctor.name as name'),
                DB::raw('COUNT(hms_invoice.id) as patient'),
                DB::raw('SUM(hms_invoice.total) as total'),
            ]);

        if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $doctorMode = $doctorMode->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }
        $doctorMode->groupBy('doctor.id');
        $doctorMode = $doctorMode->get();

        $records =['summary'=>$summary,'userBase'=>$userBase,'roomBase'=>$roomBase,'paymentMode'=>$paymentMode,'patientMode'=>$patientMode,'doctorMode'=>$doctorMode];
        return $records;
    }




}
