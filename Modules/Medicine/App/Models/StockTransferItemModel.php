<?php

namespace Modules\Medicine\App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class StockTransferItemModel extends Model
{
    protected $table = 'inv_stock_transfer_item';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'config_id', 'stock_transfer_id', 'stock_item_id', 'purchase_item_id', 'quantity', 'created_at', 'updated_at', 'uom', 'name', 'stock_quantity'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $date = new DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date = new DateTime("now");
            $model->updated_at = $date;
        });
    }

    public function stockTransfer()
    {
        return $this->belongsTo(StockTransferModel::class, 'stock_transfer_id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItemModel::class, 'stock_item_id', 'stock_item_id')
            ->whereNotNull('expired_date')
            ->where('expired_date', '>', now())
            ->whereRaw('quantity > COALESCE(sales_quantity, 0)');
    }


}
