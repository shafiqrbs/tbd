<?php

namespace Modules\Hospital\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use function Symfony\Component\TypeInfo\int;


class IpdModel extends Model
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


    public static function salesEventListener($model)
    {
        $patientMode = ParticularModeModel::find($model->patinet_mode_id);
        $mode = ($patientMode) ? $patientMode->short_code:'IPD';
        $patternCodeService = app(GeneratePatternCodeService::class);
        $params = [
            'config' => $model->config_id,
            'table' => 'hms_invoice',
            'prefix' => "{$mode}-",
        ];
        return $patternCodeService->invoiceNo($params);
    }


    public static function getIpdAdmissionShow($id)
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
                    'hms_invoice_particular.process',
                ])->where('is_admission',1);
            }])->first();

        return $entity;
    }


    public static function insertHmsInvoice($domain,$parent,$entity,$data)
    {

        $config = HospitalConfigModel::find($domain['hms_config']);
        $payment_payment_mode_id = $parent->patient_payment_mode_id;

        $invoice = self::salesEventListener($entity)['generateId'];
        $code = self::salesEventListener($entity)['code'];
        $amount = self::insertInvoiceParticular($config,$entity);
        if ($entity) {
            $entity->update([
                'invoice' => $invoice,
                'code' => $code,
                'patient_payment_mode_id' => $payment_payment_mode_id ?? null,
                'free_identification' => $parent->free_identification ?? null,
                'day' => $parent->day ?? null,
                'month' => $parent->month ?? null,
                'year' => $parent->year ?? null,
                'guardian_name' => $parent->guardian_name ?? null,
                'guardian_mobile' => $parent->guardian_mobile ?? null,
                'admit_consultant_id' => $config->consultant_by_id ?? null,
                'process' => 'confirmed',
                'sub_total' => $amount,
                'total' => $amount,
            ]);
            $parent->update([
                'process' => 'closed',
            ]);
        }
        return $entity->id;
    }

    public static function insertInvoiceParticular($config,$entity)
    {

        $date =  new \DateTime("now");
        $invoiceTransaction = InvoiceTransactionModel::create(
            [
                'invoice_id' => $entity->id,
                'created_by_id'    => $entity->created_by_id,
                'mode' => 'admission',
                'updated_at'    => $date,
                'created_at'    => $date,
            ]
        );

        $admissionFee = ParticularModel::find($config['admission_fee_id']);
        $minimumDaysRoomRent = ($config->minimum_days_room_rent) ? $config->minimum_days_room_rent:3;
        $roomRent = ParticularModel::find($entity->room_id);
        InvoiceParticularModel::updateOrCreate(
            [
                'hms_invoice_id' => $entity->id,
                'invoice_transaction_id'     => $invoiceTransaction->id,
                'particular_id'  => $roomRent->id ?? null,
            ],
            [
                'name'  => $roomRent->display_name,
                'status'  => true,
                'is_admission'  => true,
                'mode' => 'room',
                'quantity'  => $minimumDaysRoomRent,
                'price'     => $roomRent->price,
                'sub_total' => ($roomRent->price * (int)$minimumDaysRoomRent),
            ]
        );

        InvoiceParticularModel::updateOrCreate(
            [
                'hms_invoice_id' => $entity->id,
                'invoice_transaction_id'     => $invoiceTransaction->id,
                'particular_id'  => $admissionFee->id ?? null,
            ],
            [
                'name'  => 'Admission Fee',
                'status'  => true,
                'is_admission'  => true,
                'mode' => 'admission',
                'quantity'  => 1,
                'price'     => $admissionFee->price,
                'sub_total' => $admissionFee->price,
            ]
        );
        $amount = InvoiceParticularModel::where('hms_invoice_id', $entity->id)->sum('sub_total');
        $invoiceTransaction->update(['hms_invoice_id' => $entity->id ,'is_master' => true ,'sub_total' => $amount , 'total' => $amount]);
        $entity->update(['sub_total' => $amount , 'total' => $amount, 'admission_date' => $date]);
        $roomRent->update(['is_booked' => true]);
        return $amount;



    }

    public static function updateIpdInvoice($id,$data)
    {
        $entity = self::findByIdOrUid($id);
        if ($entity) {
            $entity->update([
                'day' => $data['day'] ?? null,
                'month' => $data['month'] ?? null,
                'year' => $data['year'] ?? null,
                'admit_doctor_id' => $data['admit_doctor_id'] ?? null,
                'admit_unit_id' => $data['admit_unit_id'] ?? null,
                'admit_department_id' => $data['admit_department_id'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_mobile' => $data['guardian_mobile'] ?? null,
                'patient_relation' => $data['patient_relation'] ?? null,
                'height' => $data['height'] ?? null,
                'oxygen' => $data['oxygen'] ?? null,
                'temperature' => $data['temperature'] ?? null,
                'pulse' => $data['pulse'] ?? null,
                'sat_with_O2' => $data['sat_with_O2'] ?? null,
                'sat_without_O2' => $data['sat_without_O2'] ?? null,
                'respiration' => $data['respiration'] ?? null,
                'bp' => $data['bp'] ?? null,
                'card_no' => $data['card_no'] ?? null,
                'weight' => $data['weight'] ?? null,
                'blood_sugar' => $data['blood_sugar'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'comment' => $data['comment'] ?? null,
                'process' =>  'billing',
            ]);
            self::updateIpdPatient($entity->id,$data);
        }
        return $entity;
    }

    public static function updateIpdPatient($id,$data)
    {

        $entity = self::find($id);
        $patient = PatientModel::find($entity->customer_id);
        $dob = (isset($data['dob']) and $data['dob']) ? $data['dob']:'';
        $birth = new \DateTime($dob);
        if ($patient) {
            $patient->update([
                'name' => $data['name'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'gender' => $data['gender'] ?? null,
                'permanent_address' => $data['permanent_address'] ?? null,
                'address' => $data['address'] ?? null,
                'identity_mode' => $data['identity_mode'] ?? null,
                'nid' => $data['identity'] ?? null,
                'dob' => $dob ? $birth : null,
                'father_name' => $data['father_name'] ?? null,
                'mother_name' => $data['mother_name'] ?? null,
                'religion_id' => $data['religion_id'] ?? null,
                'country_id' => $data['country_id'] ?? null,
                'upazilla_id' => $data['upazilla_id'] ?? null,
                'profession' => $data['profession'] ?? null,
            ]);
        }
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
            ->leftJoin('hms_particular as admit_consultant', 'admit_consultant.id', '=', 'hms_invoice.admit_consultant_id')
            ->leftJoin('hms_particular as admit_doctor', 'admit_doctor.id', '=', 'hms_invoice.admit_doctor_id')
            ->leftJoin('hms_particular_mode as admit_unit', 'admit_unit.id', '=', 'hms_invoice.admit_unit_id')
            ->leftJoin('hms_particular_mode as admit_department', 'admit_department.id', '=', 'hms_invoice.admit_department_id')

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
                'admit_consultant.name as admit_consultant_name',
                'admit_doctor.name as admit_doctor_name',
                'admit_unit.name as admit_unit_name',
                'admit_department.name as admit_department_name',
            ]);

        $entities = $entities->whereNotNull('hms_invoice.parent_id');
        if (isset($request['term']) && !empty($request['term'])){
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

        if (isset($request['prescription_mode']) && !empty($request['prescription_mode'])){
            if (!empty($request['prescription_mode'])) {
                if ($request['prescription_mode'] === 'prescription') {
                    $entities = $entities->whereNotNull('prescription.id');
                } elseif ($request['prescription_mode'] === 'non-prescription') {
                    $entities = $entities->whereNull('prescription.id');
                }
            }
        }

        if (isset($request['patient_mode']) && !empty($request['patient_mode'])){
            $entities = $entities->where('patient_mode.slug',$request['patient_mode']);
        }

        if (isset($request['process']) && !empty($request['process'])){
            $entities = $entities->where('hms_invoice.process',$request['process']);
        }

        if (isset($request['room_id']) && !empty($request['room_id'])){
            $entities = $entities->where('hms_invoice.room_id',$request['room_id']);
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

    public static function getShow($id)
    {
        $entity = self::where([
            ['hms_invoice.id', '=', $id]
        ])
            ->leftjoin('cor_customers','cor_customers.id','=','hms_invoice.customer_id')
            ->leftjoin('inv_sales','inv_sales.id','=','hms_invoice.sales_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->leftjoin('hms_particular as room','room.id','=','hms_invoice.room_id')
            ->leftjoin('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('hms_particular_mode as particular_payment_mode','particular_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                'hms_invoice.id',
                DB::raw('DATE_FORMAT(hms_invoice.updated_at, "%d-%m-%y") as created'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%m-%y") as appointment'),
                'inv_sales.invoice as invoice',
                'hms_invoice.total as total',
                'hms_invoice.comment',
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
            }])
            ->first();

        return $entity;
    }

    public static function getIpdShow($id)
    {
        $entity = self::where([
            ['hms_invoice.id', '=', $id]
        ])
            ->leftjoin('cor_customers','cor_customers.id','=','hms_invoice.customer_id')
            ->leftjoin('inv_sales','inv_sales.id','=','hms_invoice.sales_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->leftjoin('hms_particular as room','room.id','=','hms_invoice.room_id')
            ->leftjoin('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('hms_particular_mode as particular_payment_mode','particular_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                'hms_invoice.id',
                DB::raw('DATE_FORMAT(hms_invoice.updated_at, "%d-%m-%y") as created'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%m-%y") as appointment'),
                'inv_sales.invoice as invoice',
                'hms_invoice.total as total',
                'hms_invoice.comment',
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
            }])
            ->first();

        return $entity;
    }

    public static function getEditData($id,$domain)
    {
        $entity = self::where([
            ['hms_invoice.config_id', '=', $domain['config_id']],
            ['hms_invoice.id', '=', $id]
        ])
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

    public static function getVisitingRoomss($domain)
    {
        $entities = self::where([
            ['hms_invoice.config_id', $domain['hms_config']],
            ['hms_particular_master_type.slug', 'visiting-room']])
            ->leftJoin('hms_particular as vr', 'vr.id', '=', 'hms_invoice.room_id')
            ->leftJoin('hms_particular_type', 'hms_particular_type.id', '=', 'vr.particular_type_id')
            ->leftJoin('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->select([
                'vr.id as particular_id',
                'vr.name',
                DB::raw('COUNT(hms_invoice.id) as invoice_count')
            ])
            ->groupBy('vr.id', 'vr.name')
            ->get();

        return $entities;
    }


    public static function getVisitingRooms($domain)
    {
        $entities = ParticularModel::where([
            ['hms_particular.config_id', $domain['hms_config']],
            ['hms_particular_master_type.slug', 'opd-room']])
            ->leftJoin('hms_invoice', 'hms_invoice.room_id', '=', 'hms_particular.id')
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
            ->leftJoin('hms_invoice', 'hms_invoice.room_id', '=', 'hms_particular.id')
            ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->select([
                'hms_particular.id as id',
            ])
            ->groupBy('hms_particular.id')
            ->orderBy(DB::raw('COUNT(hms_invoice.id)'), 'ASC')
            ->limit(1)
            ->get()->first()->id;
        return array('ipdRooms' => $entities ,'selectedRoom' => $selected);
    }

    public static function patientChart($entity,$data)
    {
        $date = new \DateTime('now');
        $vital_chart_json =  (isset($data['vital_chart_json']) and $data['vital_chart_json'] ) ? json_encode($data['vital_chart_json']) : null;
        $insulin_chart_json = (isset($data['insulin_chart_json']) and $data['insulin_chart_json']) ? json_encode($data['insulin_chart_json']) : null;
        AdmissionPatientModel::updateOrCreate(
            [
                'hms_invoice_id'             => $entity,
            ],
            [
                'vital_chart_json'       => $vital_chart_json,
                'insulin_chart_json'       => $insulin_chart_json,
                'updated_at'    => $date,
                'created_at'    => $date,
            ]
        );
    }

}
