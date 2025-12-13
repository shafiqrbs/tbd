<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\CustomerModel;
use Modules\Inventory\App\Models\SalesModel;


class ReportModel extends Model
{

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

    public static function getSummary($domain,$request){


        $summary = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->select([
                DB::raw('COUNT(hms_invoice.id) as patient'),
                DB::raw('SUM(hms_invoice.total) as total'),
            ]);

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $summary = $summary->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (!empty($request['created'])) {
            $start = Carbon::parse($request['created'])->startOfDay();
            $end   = Carbon::parse($request['created'])->endOfDay();
            $summary->whereBetween('hms_invoice.created_at', [$start, $end]);
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

        /*if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $doctorMode = $doctorMode->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }*/

        $doctorMode->groupBy('doctor.id');
        $doctorMode = $doctorMode->get();

        $services =self::serviceBaseInvestigation($domain,$request);
        $records =['summary'=>$summary,'userBase'=>$userBase,'roomBase'=>$roomBase,'paymentMode'=>$paymentMode,'patientMode'=>$patientMode,'doctorMode'=>$doctorMode,'services'=>$services];
        return $records;
    }

    public static function getInvoiceSummary($domain,$request){

        $summary =self::summaryCollection($domain,$request);
        $invoiceMode =self::invoiceModeCollection($domain,$request);
        $patientMode =self::patientModeBaseCollection($domain,$request);
        $userBase =self::userBaseCollection($domain,$request);
        $serviceGroups =self::serviceBaseGroupInvestigation($domain,$request);
        $services =self::serviceBaseInvestigation($domain,$request);
        $financialServices =self::financialServiceGroupInvestigation($domain,$request);
        $records =['summary'=>$summary,'invoiceMode'=>$invoiceMode,'userBase'=>$userBase,'patientMode'=>$patientMode,'serviceGroups'=>$serviceGroups,'services'=>$services,'financialServices' => $financialServices];
        return $records;
    }

    public static function getPatientCollections($domain,$request)
    {
        $entities = InvoiceTransactionModel::where(['hms_invoice.config_id' => $domain['hms_config'],'hms_invoice_transaction.process'=>'Done'])
            ->leftjoin('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_transaction.hms_invoice_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->join('cor_customers as customer','customer.id','=','hms_invoice.customer_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->join('hms_particular_mode as patient_payment_mode','patient_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                'hms_invoice.id',
                'hms_invoice.uid',
                'hms_invoice.parent_id as parent_id',
                'hms_invoice.invoice as invoice',
                'hms_invoice.barcode  as barcode',
                'customer.customer_id as patient_id',
                'customer.health_id',
                'customer.name',
                'customer.mobile',
                'customer.address',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d %b %Y, %h:%i %p") as created_at'),
                DB::raw('DATE_FORMAT(hms_invoice.admission_date, "%d-%M-%Y") as admission_date'),
                DB::raw('DATE_FORMAT(customer.dob, "%d-%M-%Y") as dob'),
                'hms_invoice.process as process',
                'patient_mode.name as patient_mode_name',
                'patient_payment_mode.name as patient_payment_mode_name',
                'patient_payment_mode.slug as patient_payment_mode_slug',
                'createdBy.name as created_by',
                DB::raw('SUM(hms_invoice_transaction.amount) as amount'),
            ])->groupBy('hms_invoice_transaction.hms_invoice_id');


        if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->orderBy('hms_invoice_transaction.updated_at','DESC')
            ->get();
        return $entities;
    }

    public static function getPatientTickets($domain,$request)
    {

        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->whereIn('mode',['opd','emergency','admission'])
            ->leftjoin('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->join('cor_customers as customer','customer.id','=','hms_invoice.customer_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->join('hms_particular_mode as patient_payment_mode','patient_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                'hms_invoice.id',
                'hms_invoice.uid',
                'hms_invoice.parent_id as parent_id',
                'hms_invoice.invoice as invoice',
                'hms_invoice.barcode  as barcode',
                'customer.customer_id as patient_id',
                'customer.health_id',
                'customer.name',
                'customer.mobile',
                'customer.address',
                'vr.name as visiting_room',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d %b %Y, %h:%i %p") as created_at'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%M-%Y") as appointment'),
                DB::raw('DATE_FORMAT(hms_invoice.admission_date, "%d-%M-%Y") as admission_date'),
                DB::raw('DATE_FORMAT(customer.dob, "%d-%M-%Y") as dob'),
                'hms_invoice.process as process',
                'patient_mode.name as patient_mode_name',
                'patient_payment_mode.name as patient_payment_mode_name',
                'patient_payment_mode.slug as patient_payment_mode_slug',
                'createdBy.name as created_by',
                'hms_invoice_particular.price as amount'
            ]);

        if (isset($request['start_date']) && !empty($request['start_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }
        if (isset($request['patient_mode']) && !empty($request['patient_mode']) && $request['patient_mode'] !== 'all'){
            $entities = $entities->where('hms_invoice.invoice_mode', $request['patient_mode']);
        }
        $entities = $entities->orderBy('hms_invoice.created_at','ASC')
            ->get();
        return $entities;
    }

    public static function summaryCollection($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->where('hms_invoice_particular.status',1)
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice_transaction as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as total_count'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
            ]);

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $entities = $entities->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['start_date']) && !empty($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->get();
        return $entities;
    }

    public static function invoiceModeCollection($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->where('hms_invoice_particular.status',1)
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice_transaction as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_id')
            ->select([
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
                'hms_invoice_particular.mode as name',
            ])->groupBy('hms_invoice_particular.mode');

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $entities = $entities->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->get();
        return $entities;
    }


    public static function patientModeBaseCollection($domain,$request)
    {

        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->whereIn('hms_invoice_particular.mode',['opd','emergency','ipd'])
            ->where('hms_invoice_particular.status',1)
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice_transaction as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as patient'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
                'hms_invoice_particular.mode as name',
            ])->groupBy('hms_invoice_particular.mode');

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $entities = $entities->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->get();
        return $entities;
    }


    public static function userBaseCollection($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->where('hms_invoice_particular.status',1)
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice_transaction as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_id')
            ->join('users as createdBy','createdBy.id','=','hms_invoice_transaction.created_by_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as total_count'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
                'createdBy.name as name',
            ]);

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $entities = $entities->where('hms_invoice_transaction.created_by_id',$request['created_by_id']);
        }
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities->groupBy('hms_invoice_transaction.created_by_id');
        $rows = $entities->get();
        return $rows;
    }

    public static function financialServiceGroupInvestigation($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->whereIn('hms_invoice_particular.mode',['investigation'])
            ->where('hms_invoice_particular.status',1)
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice_transaction as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_id')
            ->leftjoin('hms_particular as hms_particular','hms_particular.id','=','hms_invoice_particular.particular_id')
            ->join('hms_particular_mode as particular_mode','particular_mode.id','=','hms_particular.financial_service_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as total_count'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
                'particular_mode.name as name',
            ])->groupBy('particular_mode.id');
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->orderBy('particular_mode.name','ASC')->get();
        return $entities;
    }

    public static function serviceBaseGroupInvestigation($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->whereIn('hms_invoice_particular.mode',['investigation'])
            ->where('hms_invoice_particular.status',1)
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice_transaction as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_id')
            ->leftjoin('hms_particular as hms_particular','hms_particular.id','=','hms_invoice_particular.particular_id')
            ->join('inv_category as inv_category','inv_category.id','=','hms_particular.category_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as total_count'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
                'inv_category.name as name',
            ])->groupBy('inv_category.id');
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->orderBy('inv_category.name','ASC')->get();
        return $entities;
    }


    public static function serviceBaseInvestigation1($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->whereIn('hms_invoice_particular.mode',['investigation'])
            ->where('hms_invoice_particular.status',1)
            ->leftjoin('hms_invoice_transaction as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_id')
            ->leftjoin('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->leftjoin('hms_particular as hms_particular','hms_particular.id','=','hms_invoice_particular.particular_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as total_count'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
                'hms_particular.display_name as name',
            ])->groupBy('particular_id');
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->orderBy('hms_particular.name','ASC')
            ->get();
        return $entities;
    }

    public static function serviceBaseRefundInvestigation($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->whereIn('hms_invoice_particular.mode',['investigation'])
            ->where('hms_invoice_particular.is_refund',1)
            ->whereNotNull('hms_invoice_particular.refund',1)
            ->leftjoin('hms_invoice_transaction_refund as hms_invoice_transaction_refund','hms_invoice_transaction_refund.id','=','hms_invoice_particular.invoice_transaction_refund_id')
            ->leftjoin('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->leftjoin('hms_particular as hms_particular','hms_particular.id','=','hms_invoice_particular.particular_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as total_refund_count'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total_refund_amount'),
                'hms_particular.display_name as name',
            ])->groupBy('particular_id');
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->orderBy('hms_particular.name','ASC')
            ->get();
        return $entities;
    }

    public static function serviceBaseInvestigationbk($domain, $request)
    {
        $start = !empty($request['start_date'])
            ? (new \DateTime($request['start_date']))->format('Y-m-d 00:00:00')
            : (new \DateTime())->format('Y-m-d 00:00:00');

        $end = !empty($request['end_date'])
            ? (new \DateTime($request['end_date']))->format('Y-m-d 23:59:59')
            : (new \DateTime())->format('Y-m-d 23:59:59');

        return InvoiceParticularModel::where('hi.config_id', $domain['hms_config'])
            ->where('hms_invoice_particular.mode', 'investigation')
            ->where('hms_invoice_particular.status',1)

            ->leftJoin(
                'hms_invoice_transaction as hit',
                'hit.id',
                '=',
                'hms_invoice_particular.invoice_transaction_id'
            )
            ->leftJoin(
                'hms_invoice_transaction_refund as hitr',
                'hitr.id',
                '=',
                'hms_invoice_particular.invoice_transaction_refund_id'
            )
            ->leftJoin(
                'hms_invoice as hi',
                'hi.id',
                '=',
                'hms_invoice_particular.hms_invoice_id'
            )
            ->leftJoin(
                'hms_particular as hp',
                'hp.id',
                '=',
                'hms_invoice_particular.particular_id'
            )

            ->select([
                'hp.display_name as name',

                // total service count
                DB::raw('SUM(CASE
                WHEN hms_invoice_particular.status = 1
                 AND hit.created_at BETWEEN "' . $start . '" AND "' . $end . '"
                THEN 1 ELSE 0 END) AS total_count'),

                // total service amount count
                DB::raw('SUM(CASE
                WHEN hms_invoice_particular.status = 1
                 AND hit.created_at BETWEEN "' . $start . '" AND "' . $end . '"
                THEN hms_invoice_particular.sub_total ELSE 0 END) AS total'),

                // total refund count
                DB::raw('SUM(CASE
                WHEN hms_invoice_particular.is_refund = 1
                 AND hms_invoice_particular.invoice_transaction_refund_id IS NOT NULL
                 AND hitr.created_at BETWEEN "' . $start . '" AND "' . $end . '"
                THEN 1 ELSE 0 END) AS total_refund_count'),

                // total refund amount count
                DB::raw('SUM(CASE
                WHEN hms_invoice_particular.is_refund = 1
                 AND hms_invoice_particular.invoice_transaction_refund_id IS NOT NULL
                 AND hitr.created_at BETWEEN "' . $start . '" AND "' . $end . '"
                THEN hms_invoice_particular.sub_total ELSE 0 END) AS total_refund_amount'),
            ])
            ->groupBy('hms_invoice_particular.particular_id', 'hp.display_name')
            ->orderBy('hp.display_name', 'ASC')
            ->get();
    }

    public static function serviceBaseInvestigation($domain, $request)
    {
        $start = !empty($request['start_date'])
            ? (new \DateTime($request['start_date']))->format('Y-m-d 00:00:00')
            : (new \DateTime())->format('Y-m-d 00:00:00');

        $end = !empty($request['end_date'])
            ? (new \DateTime($request['end_date']))->format('Y-m-d 23:59:59')
            : (new \DateTime())->format('Y-m-d 23:59:59');

        return InvoiceParticularModel::query()

            ->leftJoin('hms_invoice as hi', 'hi.id', '=', 'hms_invoice_particular.hms_invoice_id')
            ->leftJoin('hms_invoice_transaction as hit', 'hit.id', '=', 'hms_invoice_particular.invoice_transaction_id')
            ->leftJoin('hms_invoice_transaction_refund as hitr', 'hitr.id', '=', 'hms_invoice_particular.invoice_transaction_refund_id')
            ->leftJoin('hms_particular as hp', 'hp.id', '=', 'hms_invoice_particular.particular_id')

            ->where('hi.config_id', $domain['hms_config'])
            ->where('hms_invoice_particular.mode', 'investigation')

            ->select([
                'hp.display_name as name',

                DB::raw('SUM(CASE
                WHEN hms_invoice_particular.status = 1
                 AND hit.created_at BETWEEN "' . $start . '" AND "' . $end . '"
                THEN 1 ELSE 0 END) AS total_count'),

                DB::raw('SUM(CASE
                WHEN hms_invoice_particular.status = 1
                 AND hit.created_at BETWEEN "' . $start . '" AND "' . $end . '"
                THEN hms_invoice_particular.sub_total ELSE 0 END) AS total'),

                DB::raw('SUM(CASE
                WHEN hms_invoice_particular.is_refund = 1
                 AND hms_invoice_particular.invoice_transaction_refund_id IS NOT NULL
                 AND hitr.created_at BETWEEN "' . $start . '" AND "' . $end . '"
                THEN 1 ELSE 0 END) AS total_refund_count'),

                DB::raw('SUM(CASE
                WHEN hms_invoice_particular.is_refund = 1
                 AND hms_invoice_particular.invoice_transaction_refund_id IS NOT NULL
                 AND hitr.created_at BETWEEN "' . $start . '" AND "' . $end . '"
                THEN hms_invoice_particular.sub_total ELSE 0 END) AS total_refund_amount'),
            ])

            ->groupBy('hms_invoice_particular.particular_id', 'hp.display_name')

            ->havingRaw('
            (
                SUM(CASE
                    WHEN hms_invoice_particular.status = 1
                     AND hms_invoice_particular.is_refund = 0
                     AND hit.created_at BETWEEN ? AND ?
                    THEN 1 ELSE 0 END) > 0
            )
            OR
            (
                SUM(CASE
                    WHEN hms_invoice_particular.is_refund = 1
                     AND hms_invoice_particular.invoice_transaction_refund_id IS NOT NULL
                     AND hitr.created_at BETWEEN ? AND ?
                    THEN 1 ELSE 0 END) > 0
            )
        ', [$start, $end, $start, $end])

            ->orderBy('hp.display_name', 'ASC')
            ->get();
    }



}
