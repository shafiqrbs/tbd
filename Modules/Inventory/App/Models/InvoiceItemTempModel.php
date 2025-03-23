<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItemTempModel extends Model
{
    protected $table = 'inv_invoice_table_item';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'stock_item_id',
        'invoice_id',
        'quantity',
        'purchase_price',
        'sales_price',
        'custom_price',
        'is_print',
        'sub_total'
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
