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



    public static function getRecords($request,$domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)$request['offset'] : 50;
        $skip = $page * $perPage;

        $entitiesQuery = PatientWaiverModel::join('hms_invoice as hms_invoice', 'hms_patient_waiver.hms_invoice_id', '=', 'hms_invoice.id')
            ->join('cor_customers as customer','customer.id','=','hms_invoice.customer_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->join('hms_particular_mode as patient_payment_mode','patient_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->where('hms_invoice.config_id', $domain['hms_config'])
            ->select([
                'hms_patient_waiver.uid as uid',
                'hms_invoice.id',
                'customer.customer_id as patient_id',
                'customer.health_id',
                'customer.name',
                'customer.mobile',
                'customer.address',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d-%m-%Y %H:%i %p") as created_at'),
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

        $entitiesQuery = InvoiceParticularModel::join('hms_invoice as hms_invoice', 'hms_invoice_particular.hms_invoice_id', '=', 'hms_invoice.id')
            ->join('cor_customers as customer','customer.id','=','hms_invoice.customer_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->join('hms_particular_mode as patient_payment_mode','patient_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->where('hms_invoice.config_id', $domain['hms_config'])
            ->select([
                'hms_invoice_particular.hms_invoice_id',
                'hms_invoice.id',
                'hms_invoice.uid',
                'customer.customer_id as patient_id',
                'customer.health_id',
                'customer.name',
                'customer.mobile',
                'customer.address',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d-%m-%Y %H:%i %p") as created_at'),
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
                'hms_invoice_particular.mode as mode',

            ])
            ->groupBy('hms_invoice_particular.hms_invoice_id')
            ->orderBy('hms_invoice.updated_at', 'DESC');

        // ✅ Use clone for total count before skip/take
        $total = (clone $entitiesQuery)->count(DB::raw('DISTINCT hms_invoice_particular.hms_invoice_id'));
        if (isset($request['mode']) && !empty($request['mode']) and $request['mode'] == "opd_investigation") {
            $entitiesQuery = $entitiesQuery->where('hms_invoice.process', 'closed');
            $entitiesQuery = $entitiesQuery->where('patient_mode.slug', 'opd');
            $entitiesQuery = $entitiesQuery->where('hms_invoice_particular.mode', 'investigation');
        }elseif (isset($request['mode']) && !empty($request['mode']) and $request['mode'] == "ipd_investigation") {
            $entitiesQuery = $entitiesQuery->where('hms_invoice.process', 'admitted');
            $entitiesQuery = $entitiesQuery->where('patient_mode.slug', 'ipd');
            $entitiesQuery = $entitiesQuery->where('hms_invoice_particular.mode', 'investigation');
        }elseif (isset($request['mode']) && !empty($request['mode']) and $request['mode'] == "ipd_room") {
            $entitiesQuery = $entitiesQuery->where('hms_invoice.process', 'admitted');
            $entitiesQuery = $entitiesQuery->where('patient_mode.slug', 'ipd');
            $entitiesQuery = $entitiesQuery->where('hms_invoice_particular.mode', 'room');
        }
        $entities = $entitiesQuery
            ->skip($skip)
            ->take($perPage)
            ->get();

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
                    'price'                  => 0,
                    'is_waver'               => 1,
                ]);
        }
    }



}
