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
            $code = str_pad(random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
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


}
