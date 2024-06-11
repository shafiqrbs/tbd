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


    public function invoiceBatch()
    {
        return $this->belongsTo(InvoiceBatchModel::class);
    }
}
