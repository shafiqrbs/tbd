<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransactionModel extends Model
{
    protected $table = 'inv_payment_transaction';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['created_at','updated_at'];

    protected $fillable = [
        'config_id',
        'sale_id',
        'transaction_mode_id',
        'amount',
        'is_main',
        'purchase_id'
    ];


    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            $model->is_main = true;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

}
