<?php

namespace Modules\Medicine\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockTransferItemModel extends Model
{

    protected $table = 'inv_stock_transfer_item';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'config_id','stock_transfer_id','stock_item_id','purchase_item_id','quantity','created_at','updated_at','uom','name','stock_quantity'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $date = new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date = new \DateTime("now");
            $model->updated_at = $date;
        });
    }

}
