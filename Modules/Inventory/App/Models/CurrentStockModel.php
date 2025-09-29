<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\WarehouseModel;

class CurrentStockModel extends Model
{

    protected $table = 'inv_current_stock';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'config_id',
        'warehouse_id',
        'stock_item_id',
        'quantity'
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

    public function stockItem(){
        return $this->belongsTo(StockItemModel::class,'stock_item_id','id');
    }

    public function warehouse(){
        return $this->belongsTo(WarehouseModel::class,'warehouse_id','id');
    }

    public static function maintainCurrentStock($configId, $warehouseId, $stockItemId, $quantity): bool
    {
        return (bool) self::updateOrCreate(
            [
                'config_id'     => $configId,
                'warehouse_id'  => $warehouseId,
                'stock_item_id' => $stockItemId,
            ],
            [
                'quantity'      => $quantity,
            ]
        );
    }

}
