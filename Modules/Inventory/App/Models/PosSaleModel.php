<?php

namespace Modules\Inventory\App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PosSaleModel extends Model
{

    protected $table = 'inv_pos_sales';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [];



    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            $model->status = false;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }


}
