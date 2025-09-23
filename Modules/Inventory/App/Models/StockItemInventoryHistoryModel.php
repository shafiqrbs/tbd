<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockItemInventoryHistoryModel extends Model
{
    use HasFactory;

    protected $table = 'inv_stock_item_inventory_history';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'stock_item_history_id',
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
        'total'
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {

        });

        self::updating(function ($model) {

        });
    }

    public static function openingInventoryHistory($item, $stockItemHistory,$process,$domain)
    {
        // Check for existing record
        $exist = self::where('stock_item_history_id', $stockItemHistory->id)->first();

        // Fetch related stock item details with eager loading
        $findStockItem = StockItemModel::with(['brand', 'product.category'])->find($item->stock_item_id);

        if (!$findStockItem) {
            return; // Exit if stock item doesn't exist
        }

        // Get related details using relationships if available
        $brandName = $findStockItem->brand->name ?? null;
        $categoryName = $findStockItem->product->category->name ?? null;

        if (!$exist) {
            $data = [
                'stock_item_history_id' => $stockItemHistory->id,
                'quantity' => $item->quantity,
                'brand' => $brandName,
                'category' => $categoryName,
                'price' => $item->purchase_price, // Assuming purchase price as default
                'purchase_price' => $item->purchase_price,
                'sales_price' => $item->sales_price,
                'sub_total' => $item->sub_total,
                'total' => $item->sub_total,
                'config_id' => $item->config_id,
            ];

            // Check if this is a purchase operation
            if ($process==='purchase' && isset($item->purchase_id)) {
                $data['purchase_id'] = $item->purchase_id;
                $data['purchase_item_id'] = $item->id;
            }

            // Check if this is a sales operation
            if ($process==='sales' && isset($item->sale_id)) {
                $data['sale_id'] = $item->sale_id;
                $data['sales_item_id'] = $item->id;
            }

            // Check if this is a sales-return operation
            if ($process==='sales-return' && isset($item->sales_return_id)) {
                $data['sales_return_id'] = $item->sales_return_id;
                $data['sales_return_item_id'] = $item->id;
            }

            // Check if this is a purchase-return operation
            if ($process==='purchase-return' && isset($item->purchase_return_id)) {
                $data['purchase_return_id'] = $item->purchase_return_id;
                $data['purchase_return_item_id'] = $item->id;
            }

            // Create the database record
            self::create($data);
        }

    }


}
