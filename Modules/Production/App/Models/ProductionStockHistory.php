<?php

namespace Modules\Production\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ProductionStockHistory extends Model
{
    protected $table = 'pro_stock_production_history';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = [
        'config_id',
        'stock_item_history_id',
        'production_issue_id',
        'production_inventory_return_id',
        'production_batch_item_id',
        'production_batch_id',
        'production_batch_item_return_id',
        'production_receive_item_id',
        'production_receive_id',
        'production_expense_id',
        'production_expense_return_id',
        'production_issue_quantity',
        'production_inventory_return_quantity',
        'production_batch_item_quantity',
        'production_batch_item_return_quantity',
        'production_expense_quantity',
        'production_expense_return_quantity'
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
