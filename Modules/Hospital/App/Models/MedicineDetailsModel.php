<?php

namespace Modules\Hospital\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Models\ParticularModel;
use Modules\Medicine\App\Models\MedicineStockModel;


class MedicineDetailsModel extends Model
{
    use HasFactory;

    protected $table = 'hms_medicine_details';
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


    public function medicineStock()
    {
        return $this->belongsTo(MedicineStockModel::class, 'medicine_stock_id');
    }

    public function prescription_medicine()
    {
        return $this->hasMany(
            PatientPrescriptionMedicineModel::class,
            'medicine_id',     // foreign key on patient prescription table
            'id'               // local key on medicine_details table
        );
    }
}
