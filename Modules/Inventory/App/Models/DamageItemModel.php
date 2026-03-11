<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageItemModel extends Model
{

    protected $table = 'inv_damage_item';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime'];

    protected $fillable = ['config_id','purchase_item_id','sales_item_id','warehouse_id','quantity','price','purchase_price','sub_total','notes','damage_mode','process','damage_id','stock_item_id'];

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
