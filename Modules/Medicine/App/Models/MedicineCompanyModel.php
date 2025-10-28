<?php

namespace Modules\Medicine\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class MedicineCompanyModel extends Model
{
    use HasFactory;

    protected $table = 'medicine_company';
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

    public static function getMedicineCompanyDropdown($term){


        $results = collect();
        self::where(function ($query) use ($term) {
            $query->where('name', 'LIKE', '%' . trim($term) . '%');
        })
            ->select('name', 'id')
            ->orderBy('name', 'ASC')
            ->chunk(100, function ($entities) use ($results) {
                $results->push(...$entities);
            });
        return $results;

    }

}
