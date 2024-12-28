<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockItemHistoryModel extends Model
{
    use HasFactory;

    protected $table = 'inv_stock_item_history';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'stock_item_id',
        'config_id',
        'quantity',
        'opening_quantity',
        'closing_quantity',
        'closing_balance',
        'wearhouse_id',
        'mode',
        'process',
        'price',
        'opening_balance',
        'created_by'
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
        });

        self::updating(function ($model) {
        });
    }


    public static function openingStockQuantity($purchaseItem, $process = 'opening')
    {
        // Prepare common data variables
        $data = [
            'stock_item_id' => $purchaseItem->stock_item_id,
            'config_id' => $purchaseItem->config_id,
            'quantity' => $purchaseItem->quantity ?? 0,
            'opening_quantity' => $purchaseItem->quantity ?? 0,
            'closing_quantity' => $purchaseItem->quantity ?? 0,
            'closing_balance' => $purchaseItem->sub_total ?? 0,
            'wearhouse_id' => $purchaseItem->wearhouse_id ?? null,
            'mode' => $process,
            'process' => 'approved',
        ];

        // Check if record exists
        $existingStockHistory = self::where('stock_item_id', $purchaseItem->stock_item_id)
            ->where('mode', $process)
            ->where('config_id', $purchaseItem->config_id)
            ->first();

        if ($existingStockHistory) {
            // Update existing record
            $existingStockHistory->update($data);
        } else {
            // Add additional fields for creation
            $data['created_by'] = $purchaseItem->approved_by_id;
            $data['price'] = 0;
            $data['opening_balance'] = 0;

            // Create a new record
            $existingStockHistory = self::create($data);
        }

        if ($process === 'opening') {
            // create opening inventory-history if needed
            StockItemInventoryHistoryModel::openingInventoryHistory($purchaseItem,$existingStockHistory);
        }

        return true;
    }

}
