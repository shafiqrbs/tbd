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

class PoliceCaseModel extends Model
{
    use HasFactory;

    protected $table = 'hms_police_case';
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

    public function particular()
    {
        return $this->hasOne(ParticularModel::class, 'id', 'particular_id');
    }

    public static function insert($domain,$entity,$data)
    {

         self::updateOrCreate(
            [
                'hms_invoice_id'               => $entity,
            ],
            [
                'created_by_id'    => $domain['user_id'],
                'case_no'    => $data['case_no'] ?? null,
                'thana'    => $data['thana'] ?? null,
                'duty_officer'    => $data['duty_officer'] ?? null,
                'mobile'    => $data['mobile'] ?? null,
                'case_details'    => $data['case_details'] ?? null,
                'comment'    => $data['comment'] ?? null,

            ]
        );

    }


}
