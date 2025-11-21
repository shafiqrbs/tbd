<?php

namespace Modules\Hospital\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Hospital\App\Entities\InvoiceParticular;


class PatientWaiverModel extends Model
{
    use HasFactory;

    protected $table = 'hms_patient_waiver';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public static function generateUniqueCode($length = 12)
    {
        do {
            // Generate a random 12-digit number
            $code = str_pad(random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (self::where('uid', $code)->exists());
        return $code;
    }
    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            if (empty($model->uid)) {
                $model->uid = self::generateUniqueCode(12);
            }
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function findByIdOrUid($id)
    {
        return self::where('id', $id)
            ->orWhere('uid', $id)
            ->first();
    }

    public function items()
    {
        return $this->hasMany(InvoiceParticularModel::class, 'patient_waiver_id');
    }

    public function invoice_particular()
    {
        return $this->hasMany(InvoiceParticularModel::class, 'patient_waiver_id');
    }

    public static function getPatientDetails($id)
    {
        $entity = InvoiceModel::where(function ($query) use ($id) {
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
            ])
            ->with(['invoice_particular' => function ($query) {
                $query->select([
                    'hms_invoice_particular.id',
                    'hms_invoice_particular.hms_invoice_id',
                    'hms_invoice_particular.name as item_name',
                    'hms_invoice_particular.quantity',
                    'hms_invoice_particular.price',
                    'hms_invoice_particular.sub_total',
                    'hms_invoice_particular.process',
                    'hms_invoice_particular.is_waiver',
                ])->where('hms_invoice_particular.mode','investigation')
                  ->whereNull('hms_invoice_particular.patient_waiver_id');
            }])->first();
        return $entity;
    }

    public static function getWaiverDetailsxx($id)
    {
        $entity = self::join('hms_invoice as hms_invoice', 'hms_patient_waiver.hms_invoice_id', '=', 'hms_invoice.id')
            ->leftjoin('users as checked_by','checked_by.id','=','hms_patient_waiver.checked_by_id')
            ->leftjoin('users as approved_by','approved_by.id','=','hms_patient_waiver.approved_by_id')
            ->leftjoin('users as created_by','created_by.id','=','hms_patient_waiver.created_by_id')

            ->join('cor_customers as customer','customer.id','=','hms_invoice.customer_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->join('hms_particular_mode as patient_payment_mode','patient_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->where('hms_patient_waiver.uid', $id)
            ->select([
                'hms_patient_waiver.id as id',
                'hms_patient_waiver.uid as uid',
                'hms_invoice.uid as invoice_uid',
                'checked_by.name as checked_by_name',
                'approved_by.name as approved_by_name',
                'created_by.name as created_by_name',
                'customer.customer_id as patient_id',
                'customer.health_id',
                'customer.name',
                'customer.mobile',
                'customer.address',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d-%m-%Y") as created_at'),
                DB::raw('DATE_FORMAT(hms_invoice.admission_date, "%d-%m-%Y %H:%i %p") as admission_date'),
                DB::raw('DATE_FORMAT(customer.dob, "%d-%M-%Y") as dob'),
                'hms_invoice.process as process',
                'hms_invoice.admission_day',
                'hms_invoice.consume_day',
                'hms_invoice.remaining_day',
                'hms_invoice.total as total',
                'hms_invoice.total as amount',
                'vr.display_name as room_name',
                'patient_mode.name as patient_mode_name',
                'patient_payment_mode.name as patient_payment_mode_name',

            ])->with(['invoice_particular' => function ($query) {
                $query->select([
                    'hms_invoice_particular.id',
                    'hms_invoice_particular.patient_waiver_id',
                    'hms_invoice_particular.name as item_name',
                    'hms_invoice_particular.quantity',
                    'hms_invoice_particular.price',
                    'hms_invoice_particular.sub_total',
                    'hms_invoice_particular.process',
                    'hms_invoice_particular.is_waiver',
                ]);
            }])->first();
        return $entity;


    }

    public static function getWaiverDetails($id)
    {
        $entity = self::join('hms_invoice as hms_invoice', 'hms_patient_waiver.hms_invoice_id', '=', 'hms_invoice.id')
            ->leftjoin('users as checked_by','checked_by.id','=','hms_patient_waiver.checked_by_id')
            ->leftjoin('users as approved_by','approved_by.id','=','hms_patient_waiver.approved_by_id')
            ->leftjoin('users as created_by','created_by.id','=','hms_patient_waiver.created_by_id')
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
            ->where('hms_patient_waiver.uid', $id)
            ->select([
                'hms_patient_waiver.*',
                'checked_by.name as checked_by_name',
                'approved_by.name as approved_by_name',
                'created_by.name as created_by_name',
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
            ])
            ->with(['invoice_particular' => function ($query) {
                $query->select([
                    'hms_invoice_particular.id',
                    'hms_invoice_particular.patient_waiver_id',
                    'hms_invoice_particular.name as item_name',
                    'hms_invoice_particular.quantity',
                    'hms_invoice_particular.price',
                    'hms_invoice_particular.sub_total',
                    'hms_invoice_particular.process',
                    'hms_invoice_particular.is_waiver',
                    'diagnostic_room.name as diagnostic_room_name',
                ])->leftjoin('hms_particular as hms_particular','hms_particular.id','=','hms_invoice_particular.particular_id')
                ->leftjoin('hms_particular_mode as diagnostic_room','diagnostic_room.id','=','hms_particular.diagnostic_room_id')
                    ->where('hms_invoice_particular.mode','investigation')
                    ->whereNotNull('hms_invoice_particular.patient_waiver_id');
            }])->first();;
        return $entity;


    }

    public static function getRecords($request,$domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)$request['offset'] : 50;
        $skip = $page * $perPage;

        $entitiesQuery = PatientWaiverModel::join('hms_invoice as hms_invoice', 'hms_patient_waiver.hms_invoice_id', '=', 'hms_invoice.id')
            ->leftjoin('users as checked_by','checked_by.id','=','hms_patient_waiver.checked_by_id')
            ->leftjoin('users as approved_by','approved_by.id','=','hms_patient_waiver.approved_by_id')
            ->leftjoin('users as created_by','created_by.id','=','hms_patient_waiver.created_by_id')
            ->join('cor_customers as customer','customer.id','=','hms_invoice.customer_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->join('hms_particular_mode as patient_payment_mode','patient_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->where('hms_invoice.config_id', $domain['hms_config'])
            ->select([
                'hms_patient_waiver.uid as uid',
                'hms_invoice.uid as invoice_uid',
                'checked_by.name as checked_by_name',
                'approved_by.name as approved_by_name',
                'created_by.name as created_by_name',
                'customer.customer_id as patient_id',
                'customer.health_id',
                'customer.name',
                'customer.mobile',
                'customer.address',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d-%m-%Y") as created_at'),
                DB::raw('DATE_FORMAT(hms_invoice.admission_date, "%d-%m-%Y %H:%i %p") as admission_date'),
                DB::raw('DATE_FORMAT(customer.dob, "%d-%M-%Y") as dob'),
                'hms_invoice.process as process',
                'hms_invoice.admission_day',
                'hms_invoice.consume_day',
                'hms_invoice.remaining_day',
                'hms_invoice.total as total',
                'hms_invoice.total as amount',
                'vr.display_name as room_name',
                'patient_mode.name as patient_mode_name',
                'patient_payment_mode.name as patient_payment_mode_name',

            ])->orderBy('hms_invoice.updated_at', 'DESC');

        // ✅ Use clone for total count before skip/take
        if (isset($request['mode']) && !empty($request['mode']) and $request['mode'] == "opd_investigation") {
            $entitiesQuery = $entitiesQuery->where('hms_invoice.process', 'closed');
            $entitiesQuery = $entitiesQuery->where('patient_mode.slug', 'opd');
        }elseif (isset($request['mode']) && !empty($request['mode']) and $request['mode'] == "ipd_investigation") {
            $entitiesQuery = $entitiesQuery->where('hms_invoice.process', 'admitted');
            $entitiesQuery = $entitiesQuery->where('patient_mode.slug', 'ipd');
        }elseif (isset($request['mode']) && !empty($request['mode']) and $request['mode'] == "ipd_room") {
            $entitiesQuery = $entitiesQuery->where('hms_invoice.process', 'admitted');
            $entitiesQuery = $entitiesQuery->where('patient_mode.slug', 'ipd');
        }
        $entities = $entitiesQuery
            ->skip($skip)
            ->take($perPage)
            ->get();
        $total  = $entities->count();
        return [
            'count' => $total,
            'entities' => $entities
        ];

    }

    public static function getInvoices($request,$domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)$request['offset'] : 50;
        $skip = $page * $perPage;

        $entitiesQuery = InvoiceModel::join('cor_customers as customer','customer.id','=','hms_invoice.customer_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->join('hms_particular_mode as patient_payment_mode','patient_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->where('hms_invoice.config_id', $domain['hms_config'])
            ->select([
                'hms_invoice.id as hms_invoice_id',
                'hms_invoice.id',
                'hms_invoice.uid',
                'customer.customer_id as patient_id',
                'customer.health_id',
                'customer.name',
                'customer.mobile',
                'customer.address',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d-%m-%Y") as created_at'),
                DB::raw('DATE_FORMAT(hms_invoice.admission_date, "%d-%m-%Y %H:%i %p") as admission_date'),
                DB::raw('DATE_FORMAT(customer.dob, "%d-%M-%Y") as dob'),
                'hms_invoice.process as process',
                'hms_invoice.admission_day',
                'hms_invoice.consume_day',
                'hms_invoice.remaining_day',
                'hms_invoice.total as total',
                'hms_invoice.total as amount',
                'vr.display_name as room_name',
                'patient_mode.name as patient_mode_name',
                'patient_payment_mode.name as patient_payment_mode_name'
            ])
            ->orderBy('hms_invoice.updated_at', 'DESC');

        // ✅ Use clone for total count before skip/take

        if (isset($request['mode']) && !empty($request['mode']) and $request['mode'] == "opd_investigation") {
            $entitiesQuery = $entitiesQuery->where('hms_invoice.process', 'closed');
            $entitiesQuery = $entitiesQuery->whereIn('patient_mode.slug', ['opd','emergency']);
        }elseif (isset($request['mode']) && !empty($request['mode']) and $request['mode'] == "ipd_investigation") {
            $entitiesQuery = $entitiesQuery->whereIn('hms_invoice.process', ['admitted', 'done']);
            $entitiesQuery = $entitiesQuery->where('patient_mode.slug', 'ipd');
        }elseif (isset($request['mode']) && !empty($request['mode']) and $request['mode'] == "ipd_room") {
            $entitiesQuery = $entitiesQuery->where('hms_invoice.process', 'admitted');
            $entitiesQuery = $entitiesQuery->where('patient_mode.slug', 'ipd');
        }
        $entities = $entitiesQuery
            ->skip($skip)
            ->take($perPage)
            ->get();
        $total  = $entities->count();
        return [
            'count' => $total,
            'entities' => $entities
        ];

    }

    public static function getInvoiceParticular($id,$mode)
    {
        $entity = InvoiceParticularModel::join('hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_particular.hms_invoice_id')
            ->join('hms_particular', 'hms_particular.id', '=', 'hms_invoice_particular.particular_id')
            ->where(function ($query) use ($id) {
                $query->where('hms_invoice.id', '=', $id)
                    ->orWhere('hms_invoice.uid', '=', $id);
            })
            ->where('hms_invoice_particular.mode', $mode)
            ->where('hms_invoice_particular.process', 'New')
            ->whereNull('hms_invoice_particular.invoice_transaction_id')
            ->where('hms_invoice_particular.status', 0)
            ->where('hms_particular.is_available', 1)
            ->select([
                'hms_invoice_particular.id',
                'hms_invoice_particular.hms_invoice_id',
                'hms_invoice_particular.particular_id',
                'hms_invoice_particular.name as item_name',
                'hms_invoice_particular.quantity',
                DB::raw('COALESCE(hms_invoice_particular.price, 0) as price'),
                DB::raw('COALESCE(hms_invoice_particular.sub_total, 0) as sub_total'),

            ])
            ->get();

        return $entity;
    }

    public static function getInvoiceRoomParticular($id,$mode)
    {
        $entity = InvoiceParticularModel::join('hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_particular.hms_invoice_id')
            ->join('hms_particular', 'hms_particular.id', '=', 'hms_invoice_particular.particular_id')
            ->where(function ($query) use ($id) {
                $query->where('hms_invoice.id', '=', $id)
                    ->orWhere('hms_invoice.uid', '=', $id);
            })
            ->where('hms_invoice_particular.mode', $mode)
            ->where('hms_invoice_particular.process', 'New')
            ->whereNull('hms_invoice_particular.invoice_transaction_id')
            ->where('hms_invoice_particular.status', 0)
            ->select([
                'hms_invoice_particular.id',
                'hms_invoice_particular.hms_invoice_id',
                'hms_invoice_particular.particular_id',
                'hms_invoice_particular.name as item_name',
                'hms_invoice_particular.quantity',
                DB::raw('COALESCE(hms_invoice_particular.price, 0) as price'),
                DB::raw('COALESCE(hms_invoice_particular.sub_total, 0) as sub_total'),

            ])
            ->get();

        return $entity;
    }

    public static function insertInvoiceTransaction($entity,$hms_invoice_id,$mode,$investigations)
    {
        $date =  new \DateTime("now");
        if (!empty($investigations) && is_array($investigations)) {
            $invoiceTransaction = InvoiceTransactionModel::updateOrCreate(
                [
                    'hms_invoice_id'=> $hms_invoice_id,
                    'patient_waiver_id'=> $entity->id,
                ],
                [
                    'created_by_id'=> $entity->created_by_id,
                    'mode'    => $mode,
                    'updated_at'    => $date,
                    'created_at'    => $date,
                ]
            );
            InvoiceParticularModel::where('hms_invoice_id', $invoiceTransaction->hms_invoice_id)
                ->whereIn('id', $investigations)
                ->update([
                    'patient_waiver_id'      => $invoiceTransaction->patient_waiver_id,
                    'invoice_transaction_id' => $invoiceTransaction->id,
                  //  'price'                  => 0,
                    'is_waiver'               => 1,
                ]);
        }
    }



}
