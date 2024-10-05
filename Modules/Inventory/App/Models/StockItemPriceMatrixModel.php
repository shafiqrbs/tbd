<?php

namespace Modules\Inventory\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class StockItemPriceMatrixModel extends Model
{
    use HasFactory;

    protected $table = 'inv_stock_item_price_matrix';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'stock_item_id',
        'price_unit_id',
        'price',
        'product_id',
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            $model->status = 1;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function updateStockItemPriceMatrix($stock_item_id, $price_unit_id, $price){

    }
}
