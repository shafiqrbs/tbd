<?php

namespace Modules\Medicine\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemInventoryHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;

class PurchaseItemModel extends Model
{
    protected $table = 'inv_purchase_item';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

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


    public function purchase()
    {
        return $this->belongsTo(PurchaseModel::class);
    }

    public function stockItems()
    {
        return $this->hasMany(StockItemHistoryModel::class,'purchase_item_id');
    }
    public function inventoryItemHistory() : HasMany
    {
        return $this->hasMany(StockItemInventoryHistoryModel::class,'purchase_item_id');
    }

    public function stock() : BelongsTo
    {
        return $this->belongsTo(StockItemModel::class , 'stock_item_id');
    }

}
