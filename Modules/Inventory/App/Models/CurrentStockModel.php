<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
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

    public static function getCurrentStockByWarehouseAndStockItemId(int $configID, int $warehouseId, int $stockItemId): int
    {
        return (int) self::where('config_id', $configID)
            ->where('warehouse_id', $warehouseId)
            ->where('stock_item_id', $stockItemId)
            ->value('quantity') ?? 0;
    }

    public static function getItemsForTransfer( $domain, $warehouseIds )
    {
        $warehouses = self::with([
            'warehouse:id,name',
            'stockItem' => function ($q) {
                $q->select(['id', 'name','uom','sales_price','purchase_price'])
                    ->with([
                        'purchaseItemForSales' => function ($q) {
                            $q->select([
                                'id',
                                'stock_item_id',
                                'quantity',
                                'sales_quantity',
                                'expired_date'
                            ])
                                ->whereNotNull('expired_date')
                                ->where('expired_date', '>', now())
                                ->whereRaw('quantity > COALESCE(sales_quantity, 0)');
                        }
                    ]);
            }
        ])
            ->select(['id', 'config_id', 'warehouse_id', 'stock_item_id', 'quantity'])
            ->whereIn('warehouse_id', $warehouseIds)
            ->where('config_id', $domain['config_id'])
            ->where('quantity', '>', 0)
            ->get()
            ->groupBy(fn($stock) => $stock->warehouse_id) //  group by warehouse id
            ->map(function ($stocks, $warehouseId) {
                $warehouse = $stocks->first()->warehouse; // first warehouse record

                return [
                    'warehouse_id'   => $warehouseId,
                    'warehouse_name' => $warehouse?->name,
                    'items' => $stocks->groupBy('stock_item_id')->map(function ($itemStocks) {
                        $firstStock = $itemStocks->first();
                        $stockItem = $firstStock->stockItem;

                        return [
                            'id'   => $stockItem?->id,
                            'stock_item_id'   => $stockItem?->id,
                            'stock_item_name' => $stockItem?->name,
                            'uom' => $stockItem?->uom,
                            'sales_price' => $stockItem?->sales_price,
                            'purchase_price' => $stockItem?->purchase_price,
                            'total_quantity'  => $itemStocks->sum('quantity'),
                            'is_purchase_item' => count($stockItem->purchaseItemForSales)>0 ? true : false,
                            'purchase_items'  => $stockItem && $stockItem->purchaseItemForSales
                                ? $stockItem->purchaseItemForSales->map(function ($purchase) {
                                    $salesQty = $purchase->sales_quantity ?? 0;
                                    return [
                                        'id'                => $purchase->id,
                                        'purchase_quantity' => $purchase->quantity,
                                        'sales_quantity'    => $salesQty,
                                        'remain_quantity'   => $purchase->quantity - $salesQty,
                                        'expired_date'      => $purchase->expired_date
                                            ? Carbon::parse($purchase->expired_date)->format('d-M-Y')
                                            : null,
                                    ];
                                })->values()->toArray()
                                : [],
                        ];
                    })->values()->toArray(),
                ];
            })
            ->values()
            ->toArray();
        return $warehouses;

    }

}
