<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Ramsey\Collection\Collection;

class InvoiceParticularModel extends Model
{
    use HasFactory;

    protected $table = 'hms_invoice_particular';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            if (empty($model->barcode)) {
                $model->barcode = self::generateUniqueCode(12);
                $model->uid = self::generateUniqueCode(12);
            }
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function generateUniqueCode($length = 12)
    {
        do {
            // Generate a random 12-digit number
            $code = str_pad(random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (self::where('barcode', $code)->exists());
        return $code;
    }



    public function particular()
    {
        return $this->hasOne(ParticularModel::class, 'id', 'particular_id');
    }

    public function custom_report()
    {
        return $this->hasOne(InvoiceParticularTestReportModel::class, 'invoice_particular_id');
    }

    public function reports()
    {
        return $this->hasMany(InvoicePathologicalReportModel::class, 'invoice_particular_id');
    }

    public static function getPatientCountBedRoom($domain){


        InvoiceModel::where('hms_invoice.config_id', $domain['hms_config'])
            ->where('process', $domain['admitted'])
            ->chunk(100, function ($entities) {
                foreach ($entities as $entity) {
                    $admissionDate = new \DateTime($entity->admission_date);
                    $currentDate = new \DateTime('now');
                    $interval = $admissionDate->diff($currentDate);
                    $consumeDay = (int) $interval->days + 1;
                    $totalQuantity = DB::table('hms_invoice_particular')
                        ->join('hms_particular', 'hms_particular.id', '=', 'hms_invoice_particular.particular_id')
                        ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
                        ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
                        ->where('hms_invoice_particular.hms_invoice_id', $entity->id)
                        ->where('hms_invoice_particular.status', 1)
                        ->whereIn('hms_particular_master_type.slug', ['bed', 'cabin'])
                        ->sum('hms_invoice_particular.quantity');

                    $remainingDay = $totalQuantity - $consumeDay;
                    $entity->update([
                        'admission_day' => $totalQuantity,
                        'consume_day' => $consumeDay,
                        'remaining_day' => $remainingDay,
                    ]);
                }
            });
        }

    public static function getCountBedRoom($id){

        $entity = InvoiceModel::find($id);

        $admissionDate = new \DateTime($entity->admission_date);
        $currentDate = new \DateTime('now');

        $interval = $admissionDate->diff($currentDate);
        $consumeDay = (int)$interval->days+1;

        $totalQuantity = DB::table('hms_invoice_particular')
            ->join('hms_particular', 'hms_particular.id', '=', 'hms_invoice_particular.particular_id')
            ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->where('hms_invoice_particular.hms_invoice_id', $id)
            ->where('hms_invoice_particular.status', 1)
            ->whereIn('hms_particular_master_type.slug', ['bed', 'cabin'])
            ->sum('hms_invoice_particular.quantity');

        $remainingDay = ($totalQuantity - $consumeDay);
        $entity->update(['admission_day' => $totalQuantity, 'consume_day' => $consumeDay,'remaining_day' => $remainingDay]);
        return $totalQuantity;
    }

    public static function updateWaverParticular($entity,$data)
    {

        // Reset all to 0 first
        $array = json_decode($data, true);
        self::where('hms_invoice_id', $entity)->update(['is_waver' => 0]);
        // Set only selected ones to 1
        self::where('hms_invoice_id', $entity)
            ->whereIn('id', $array)
            ->update(['is_waver' => 1]);

    }

   public static function checkExistingWaiver($data)
    {

        // Reset all to 0 first
        $entity = $data['hms_invoice_id'];
        $array = json_decode($data['particular_ids'], true);

        $query = self::where('hms_invoice_id', $entity)
            ->whereIn('id', $array)
            ->where('is_waver', 1);

        $count = $query->count();
        $availableIds = $query->pluck('id')->toArray();
        $missingIds = array_diff($array, $availableIds);
        return [
            'count' => $count,
            'available' => $availableIds,
            'new' => $missingIds,
        ];

    }




}
