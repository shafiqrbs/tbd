<?php

namespace Modules\Medicine\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class MedicineGenericModel extends Model
{
    use HasFactory;

    protected $table = 'medicine_brand';
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

    public static function getMedicineGenericDropdown($term){

        $entities = self::where(function ($query) use ($term) {
            $query->where('medicine_generic.name', 'LIKE', '%' . trim($term) . '%');
        })
            ->leftJoin('medicine_generic', 'medicine_generic.id', '=', 'medicine_brand.medicineGeneric_id')
            ->leftJoin('medicine_company', 'medicine_company.id', '=', 'medicine_brand.medicineCompany_id')
            ->select([
                DB::raw("
                    CONCAT(
                        IF(medicine_brand.medicineForm != '', CONCAT(trim(medicine_brand.medicineForm), '. '), ''),
                        trim(medicine_brand.name),
                        IF(medicine_brand.strength != '', CONCAT(' - ', trim(medicine_brand.strength)), '')
                    ) as name
                "),
                'medicine_brand.id as generic_id',
                'medicine_generic.name as generic',
                'medicine_brand.packSize',
                'medicine_company.name as medicine_company',
            ])
            ->orderBy('medicine_brand.name', 'ASC')
            ->take(100)
            ->get();

        return $entities;

    }

}
