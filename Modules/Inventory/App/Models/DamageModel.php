<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageModel extends Model
{

    protected $table = 'inv_damage';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime'];

    protected $fillable = ['config_id','created_by_id','quantity','sub_total','notes','damage_mode','process','damage_type'];

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

}
