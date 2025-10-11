<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Ramsey\Collection\Collection;

class TreatmentMedicineModel extends Model
{
    use HasFactory;

    protected $table = 'hms_treatment_medicine';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public function medicineDosage()
    {
        return $this->hasOne(MedicineDosageModel::class, 'id', 'medicine_dosage_id');
    }

    public function medicineBymeal()
    {
        return $this->hasOne(MedicineDosageModel::class, 'id', 'medicine_bymeal_id');
    }

}
