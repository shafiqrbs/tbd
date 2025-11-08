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

class AdmissionPatientPrescriptionHistoryModel extends Model
{
    use HasFactory;

    protected $table = 'hms_admission_patient_prescription_history';
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


    public static function insert($domain,$entity,$data)
    {
        $date = new \DateTime('now');
        return self::query()->insert([
            'hms_invoice_id'  => $entity,
            'created_by_id'   => $domain['user_id'],
            'json_content'    => $data ?? null,
            'updated_at'    => $date,
            'created_at'    => $date,
        ]);

    }


}
