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


    public function invoice_details()
    {
        return $this->hasOne(InvoiceModel::class, 'id', 'hms_invoice_id');
    }

    public function invoice_transaction()
    {
        return $this->hasOne(InvoiceTransactionModel::class, 'id', 'prescription_id');
    }

    public function invoice_particular()
    {
        return $this->hasMany(InvoiceParticularModel::class, 'hms_invoice_id');
    }

    public function prescription_medicine()
    {
        return $this->hasMany(PatientPrescriptionMedicineModel::class, 'hms_invoice_id');
    }

    public function invoice()
    {
        return $this->belongsTo(InvoiceModel::class, 'hms_invoice_id');
    }

    public static function getRecords($request,$domain)
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
            ->join('hms_particular_mode as patient_payment_mode','patient_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                'hms_invoice.id',
                'hms_invoice.parent_id as parent_id',
                'prescription.id as prescription_id',
                'prescription.created_by_id as prescription_created_by_id',
                'hms_invoice.invoice as invoice',
                'customer.customer_id as patient_id',
                'customer.health_id',
                'doctor.name as doctor_name',
                'customer.name',
                'customer.mobile',
                'customer.address',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d-%m-%Y") as created_at'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%M-%Y") as appointment'),
                DB::raw('DATE_FORMAT(customer.dob, "%d-%M-%Y") as dob'),
                'hms_invoice.process as process',
                'vr.name as visiting_room',
                'patient_mode.name as patient_mode_name',
                'patient_payment_mode.name as patient_payment_mode_name',
                'patient_payment_mode.slug as patient_payment_mode_slug',
                'createdBy.name as created_by',
                'hms_invoice.sub_total as total',
                'hms_invoice.referred_mode as referred_mode',
                'prescription.diabetes as diabetes',
                'prescription.blood_pressure as blood_pressure',
                'prescription.weight as weight',
                'prescription.height as height',
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
                } elseif ($request['prescription_mode'] === 'non-prescription') {
                    $entities = $entities->whereNull('prescription.id');
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

        if (isset($request['process']) && !empty($request['process'])){
            $entities = $entities->where('hms_invoice.process',$request['process']);
        }

        if (isset($request['room_id']) && !empty($request['room_id'])){
            $entities = $entities->where('hms_invoice.room_id',$request['room_id']);
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

    public static function getPatientPrescription($domain,$id,$prescription)
    {

        $entities = InvoiceModel::where([
            ['hms_invoice.config_id', $domain['hms_config']],
            ['hms_invoice.customer_id', $id],
        ])
            ->where(function ($query) use ($prescription) {
                $query->where('hms_prescription.id', '<>', $prescription)
                    ->orWhere('hms_prescription.uid', '<>', $prescription);
            })
            ->join('hms_prescription','hms_prescription.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_prescription.created_by_id')
            ->leftjoin('cor_customers as customer','customer.id','=','hms_invoice.customer_id')
            ->select([
                'hms_invoice.id as invoice_id',
                'hms_invoice.invoice as patient_invoice',
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
                'vr.id as visiting_room_id',
                'createdBy.name as created_by',
            ])->get()->toArray();
        return $entities;
    }

    public static function getVisitingRooms($domain)
    {
        $entities = ParticularModel::where([
            ['hms_particular.config_id', $domain['hms_config']],
            ['hms_particular_master_type.slug', 'opd-room'],
            ])
            ->join('hms_invoice', 'hms_invoice.room_id', '=', 'hms_particular.id')
            ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->select([
                'hms_particular.id as id',
                'hms_particular.name',
                DB::raw('COUNT(hms_invoice.id) as invoice_count')
            ])
            ->groupBy('hms_particular.id')
            ->get();

        $selected = ParticularModel::where([
            ['hms_particular.config_id', $domain['hms_config']],
            ['hms_particular_master_type.slug', 'opd-room']])
            ->join('hms_invoice', 'hms_invoice.room_id', '=', 'hms_particular.id')
            ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->select([
                'hms_particular.id as id',
            ])
            ->groupBy('hms_particular.id')
            ->orderBy(DB::raw('COUNT(hms_invoice.id)'), 'ASC')
            ->limit(1)
            ->get()->first()->id;

        return array('entities' => $entities ,'selected' => $selected);
    }

    public static function getShow($id)
    {

        $entity = self::where(function ($query) use ($id) {
            $query->where('hms_prescription.id', '=', $id)
                ->orWhere('hms_prescription.uid', '=', $id);
        })
            ->join('hms_invoice','hms_invoice.id','=','hms_prescription.hms_invoice_id')
            ->leftjoin('hms_invoice_patient_referred','hms_invoice_patient_referred.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('hms_particular as referred_room','referred_room.id','=','hms_invoice_patient_referred.opd_room_id')
            ->leftjoin('users as doctor','doctor.id','=','hms_prescription.prescribe_doctor_id')
            ->leftjoin('cor_user_profiles as profiles','profiles.id','=','doctor.id')
            ->leftjoin('cor_setting as designation','designation.id','=','profiles.designation_id')
            ->join('cor_customers','cor_customers.id','=','hms_invoice.customer_id')
            ->leftjoin('inv_sales','inv_sales.id','=','hms_invoice.sales_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->leftjoin('hms_particular as room','room.id','=','hms_invoice.room_id')
            ->leftjoin('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('hms_particular_mode as particular_payment_mode','particular_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                'hms_prescription.id as id',
                'hms_prescription.uid as prescription_uid',
                'hms_invoice.id as invoice_id',
                DB::raw('DATE_FORMAT(hms_invoice.updated_at, "%d-%m-%y %H:%i %p") as created'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%m-%y") as appointment'),
                'hms_invoice.invoice as invoice',
                'hms_invoice.total as total',
                'hms_invoice.barcode',
                'hms_invoice.is_vital',
                'hms_invoice.weight as weight',
                'hms_invoice.height as height',
                'hms_invoice.bp as bp',
                'hms_invoice.oxygen  as oxygen',
                'hms_invoice.temperature  as temperature',
                'hms_invoice.sat_with_O2  as sat_with_O2',
                'hms_invoice.sat_liter  as sat_liter',
                'hms_invoice.sat_without_O2  as sat_without_O2',
                'hms_invoice.respiration  as respiration',
                'hms_invoice.pulse  as pulse',
                'hms_invoice.comment',
                'cor_customers.id as customer_id',
                'cor_customers.name as name',
                'cor_customers.mobile as mobile',
                'hms_invoice.guardian_name as guardian_name',
                'hms_invoice.guardian_mobile as guardian_mobile',
                'cor_customers.customer_id as patient_id',
                'cor_customers.health_id as health_id',
                'cor_customers.gender as gender',
                'hms_invoice.year as year',
                'hms_invoice.month as month',
                'hms_invoice.day as day',
                'doctor.name as doctor_name','doctor.employee_id as employee_id',
                DB::raw("CONCAT('".url('')."/uploads/core/user/signature/', profiles.signature_path) AS signature_path"),
                'profiles.id as profiles_id',
                'designation.name as designation_name',
                'hms_prescription.blood_pressure as blood_pressure',
                'hms_prescription.diabetes as diabetes',
                'hms_prescription.json_content as json_content',
                DB::raw('DATE_FORMAT(hms_prescription.follow_up_id, "%d-%m-%y") as follow_up_date'),
                DB::raw('DATE_FORMAT(cor_customers.dob, "%d-%m-%y") as dob'),
                'cor_customers.identity_mode as identity_mode',
                'cor_customers.nid as nid',
                'cor_customers.address as address',
                'createdBy.username as created_by_user_name',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'room.name as room_name',
                'patient_mode.name as mode_name',
                'particular_payment_mode.name as payment_mode_name',
                'hms_invoice.process as process',
                'hms_invoice_patient_referred.id as patient_referred_id',
                'hms_invoice_patient_referred.referred_mode as referred_mode',
                'hms_invoice_patient_referred.hospital as referred_hospital',
                'referred_room.name as referred_room',
                'hms_invoice_patient_referred.comment as referred_comment',
                'hms_invoice_patient_referred.json_content as referred_json_content',
            ])
            ->with(['invoice_particular' => function ($query) {
                $query->select([
                    'hms_invoice_particular.id',
                    'hms_invoice_particular.hms_invoice_id',
                    'hms_invoice_particular.name as item_name',
                    'hms_invoice_particular.quantity',
                    'hms_invoice_particular.price',
                ]);
            }])
            ->with(['prescription_medicine'])
            ->first();

        return $entity;
    }


}
