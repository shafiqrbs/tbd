<?php

namespace Modules\Inventory\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class ProductMeasurementModel extends Model
{
    use HasFactory;

    protected $table = 'inv_product_unit_measurment';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'product_id',
        'unit_id',
        'is_base_unit',
        'config_id',
        'is_sales',
        'is_purchase',
        'quantity'
    ];


    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            $model->status = 1;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }


    public static function getRecords($id,$configId)
    {
        return self::where([['inv_product_unit_measurment.status',1],['inv_product_unit_measurment.product_id',$id],['inv_product_unit_measurment.config_id',$configId]])
            ->leftjoin('inv_particular','inv_particular.id','=','inv_product_unit_measurment.unit_id')
            ->select([
                'inv_product_unit_measurment.id',
                'inv_product_unit_measurment.product_id',
                'inv_product_unit_measurment.unit_id',
                'inv_product_unit_measurment.quantity',
                'inv_product_unit_measurment.is_sales',
                'inv_product_unit_measurment.is_purchase',
                'inv_product_unit_measurment.is_base_unit',
                'inv_particular.name as unit_name',
            ])->orderBy('inv_product_unit_measurment.id','DESC')->get();
    }
}
