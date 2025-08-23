<?php

namespace Modules\Hospital\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class MedicineModel extends Model
{
    use HasFactory;

    protected $table = 'hms_medicine';
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

    public static function getMedicineDropdown($domain,$term){

        $config =  $domain['hms_config'];
        $entities = self::where('hms_medicine.config_id', $config)
            ->where(function ($query) use ($term) {
                $query->where('hms_medicine.name', 'LIKE', '%' . trim($term) . '%')
                    ->orWhere('hms_medicine.generic', 'LIKE', '%' . trim($term) . '%');
            })
            ->select('*')
            ->orderBy('name', 'ASC')
            ->take(100)
            ->get();

        return $entities;

    }

}
