<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

class StockItemInventoryHistoryModel extends Model
{

    protected $table = 'inv_stock_item_inventory_history';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'stock_item_history_id',
        'purchase_return_item_id',
        'purchase_return_id',
        'quantity',
        'brand',
        'category',
        'purchase_item_id',
        'purchase_id',
        'sales_item_id',
        'sale_id',
        'price',
        'purchase_price',
        'sales_price',
        'sub_total',
        'config_id',
        'total',
        'stock_transfer_item_id',
        'stock_transfer_id'
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {

        });

        self::updating(function ($model) {

        });
    }

    public static function openingInventoryHistory($item, $stockItemHistory, $process, $domain)
    {
        try {
            $exist = self::where('stock_item_history_id', $stockItemHistory->id)->first();
            $findStockItem = StockItemModel::with(['brand', 'product.category'])->find($item->stock_item_id);

            if (!$findStockItem || $exist) {
                return; // Safe exit
            }

            $brandName = $findStockItem->brand->name ?? null;
            $categoryName = $findStockItem->product->category->name ?? null;

            $data = [
                'stock_item_history_id' => $stockItemHistory->id,
                'quantity' => $item->quantity,
                'brand' => $brandName,
                'category' => $categoryName,
                'price' => $item->purchase_price ?? 0,
                'purchase_price' => $item->purchase_price ?? 0,
                'sales_price' => $item->sales_price ?? 0,
                'sub_total' => $item->sub_total ?? 0,
                'total' => $item->sub_total ?? 0,
                'config_id' => $item->config_id,
            ];

            switch ($process) {
                case 'opening':
                    $data['purchase_item_id'] = $item->id ?? null;
                    break;
                case 'purchase':
                    $data['purchase_id'] = $item->purchase_id ?? null;
                    $data['purchase_item_id'] = $item->id ?? null;
                    break;
                case 'sales':
                    $data['sale_id'] = $item->sale_id ?? null;
                    $data['sales_item_id'] = $item->id ?? null;
                    break;
                case 'sales-return':
                    $data['sales_return_id'] = $item->sales_return_id ?? null;
                    $data['sales_return_item_id'] = $item->id ?? null;
                    break;
                case 'purchase-return':
                    $data['purchase_return_id'] = $item->purchase_return_id ?? null;
                    $data['purchase_return_item_id'] = $item->id ?? null;
                    break;
                case 'stock-transfer-in':
                case 'stock-transfer-out':
                    $data['stock_transfer_item_id'] = $item->stock_transfer_item_id ?? null;
                    $data['stock_transfer_id'] = $item->stock_transfer_id ?? null;
                    break;
            }

            self::create($data);
        } catch (\Throwable $e) {
            logger()->error('openingInventoryHistory failed', [
                'error' => $e->getMessage(),
                'process' => $process,
                'item' => $item,
            ]);
        }
    }



}
