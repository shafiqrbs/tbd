<?php

namespace Modules\Production\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Models\StockItemModel;


class ProductionStockHistory extends Model
{
    protected $table = 'pro_stock_production_history';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = [
        'config_id',
        'stock_item_history_id',
        'production_issue_id',
        'production_inventory_return_id',
        'production_batch_item_id',
        'production_batch_id',
        'production_batch_item_return_id',
        'production_receive_item_id',
        'production_receive_id',
        'production_expense_id',
        'production_expense_return_id',
        'production_issue_quantity',
        'production_inventory_return_quantity',
        'production_batch_item_quantity',
        'production_batch_item_return_quantity',
        'production_expense_quantity',
        'production_expense_return_quantity',
        'production_issue_item_id'
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
                'production_issue_item_id' => $item->id,
                'production_issue_quantity' => $item->quantity,
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
            if ($process==='production-issue' && isset($item->production_issue_id)) {
                $data['production_issue_id'] = $item->production_issue_id;
            }

            // Create the database record
            self::create($data);
        }

    }


}
