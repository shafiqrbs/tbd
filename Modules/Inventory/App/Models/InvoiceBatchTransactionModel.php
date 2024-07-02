<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Utility\App\Models\ProductUnitModel;
use Ramsey\Collection\Collection;

class InvoiceBatchTransactionModel extends Model
{
    use HasFactory;

    protected $table = 'inv_invoice_batch_transaction';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'invoice_batch_id',
        'created_by_id',
        'sales_by_id',
        'provision_discount',
        'provision_mode',
        'discount_calculation',
        'discount_type',
        'comment',
        'invoice_date',
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $model->invoice = round(microtime(true) * 1000);
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $model->invoice = round(microtime(true) * 1000);
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }



    public function invoiceBatch()
    {
        return $this->belongsTo(InvoiceBatchModel::class);
    }
}
