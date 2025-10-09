<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\WarehouseModel;
use Modules\Inventory\App\Entities\StockItem;
use Modules\Inventory\App\Entities\StockItemInventoryHistory;
use Modules\Utility\App\Models\ProductUnitModel;
use Ramsey\Collection\Collection;

class RequisitionMatrixBoardModel extends Model
{
    protected $table = 'inv_requisition_matrix_board';
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

    public function warehouse(){
        return $this->belongsTo(WarehouseModel::class,'warehouse_id');
    }
}
