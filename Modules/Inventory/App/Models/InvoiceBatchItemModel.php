<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Utility\App\Models\ProductUnitModel;
use Ramsey\Collection\Collection;

class InvoiceBatchItemModel extends Model
{
    use HasFactory;

    protected $table = 'inv_invoice_batch_item';
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

    public function product()
    {
        return $this->belongsTo(StockItemModel::class, 'stock_item_id','id');
    }

}
