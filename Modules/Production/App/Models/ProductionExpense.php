<?php

namespace Modules\Production\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ProductionExpense extends Model
{
    protected $table = 'pro_expense';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at','issue_date'];
    protected $fillable = [
        'config_id',
        'production_inventory_id',
        'production_item_id',
        'production_batch_item_id',
        'issue_item_id',
        'production_element_id',
        'return_receive_batch_item_id',
        'quantity',
        'return_quantity',
        'issue_date',
        'purchase_price',
        'sales_price'
    ];


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
