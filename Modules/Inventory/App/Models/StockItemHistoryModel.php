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
        'warehouse_id',
        'mode',
        'process',
        'sales_price',
        'purchase_price',
        'opening_balance',
        'created_by',
        'item_name'
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
        });

        self::updating(function ($model) {
        });
    }

    public static function openingStockQuantity($item, $process = 'opening',$domain)
    {
        try {
            // Fetch the latest stock history
            $existingStockHistory = self::where('stock_item_id', $item->stock_item_id)
                ->where('config_id', $item->config_id)
                ->latest()
                ->first();

            // Define operators for processes
            $operatorData = [
                'opening' => '+',
                'purchase' => '+',
                'production' => '-',
                'sales' => '-',
            ];

            // Determine the operator and apply calculations
            $operator = $operatorData[$process] ?? '+';

            // Extract quantities and balances for new stock history
            list($closing_quantity, $closing_balance, $quantity) = self::calculateClosingStock(
                $existingStockHistory,
                $item,
                $operator
            );

            // Prepare data for creating stock history
            $data = self::prepareStockHistoryData(
                $existingStockHistory,
                $item,
                $closing_quantity,
                $closing_balance,
                $quantity,
                $process,
                $domain
            );

            // Create stock history record
            $stockHistory = self::create($data);

            // Update stock item quantity
            $stockItem = StockItemModel::find($item->stock_item_id);
            if ($stockItem) {
                $stockItem->update(['quantity' => $stockHistory->closing_quantity]);
            }

            // Update product quantity
            $totalProductQty = StockItemModel::calculateTotalStockQuantity(
                $stockItem->product_id,
                $stockItem->config_id
            );

            $product = ProductModel::find($stockItem->product_id);
            if ($product) {
                $product->update(['quantity' => $totalProductQty]);
            }

            // Log inventory history
            StockItemInventoryHistoryModel::openingInventoryHistory($item, $stockHistory,$process,$domain);

            return true;

        } catch (\Exception $e) {
            // Log and handle errors appropriately
            logger()->error('Opening Stock Quantity Error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private static function calculateClosingStock($existingStockHistory, $item, $operator)
    {
        $quantity = $item->quantity ?? 0;
        $subTotal = $item->sub_total ?? 0;

        if ($operator === '+') {
            $closing_quantity = ($existingStockHistory->closing_quantity ?? 0) + $quantity;
            $closing_balance = ($existingStockHistory->closing_balance ?? 0) + $subTotal;
        } elseif ($operator === '-') {
            $closing_quantity = ($existingStockHistory->closing_quantity ?? 0) - $quantity;
            $closing_balance = ($existingStockHistory->closing_balance ?? 0) - $subTotal;
            $quantity = -$quantity; // Set negative quantity for deductions
        } else {
            $closing_quantity = $quantity;
            $closing_balance = $subTotal;
        }

        return [$closing_quantity, $closing_balance, $quantity];
    }

    private static function prepareStockHistoryData($existingStockHistory, $item, $closing_quantity, $closing_balance, $quantity, $process,$domain)
    {
        return [
            'stock_item_id' => $item->stock_item_id,
            'item_name' => $item->name,
            'config_id' => $item->config_id,
            'quantity' => $quantity,
            'purchase_price' => $item->purchase_price ?? 0,
            'sales_price' => $item->sales_price ?? 0,
            'opening_quantity' => $existingStockHistory->closing_quantity ?? 0,
            'opening_balance' => $existingStockHistory->closing_balance ?? 0,
            'closing_quantity' => $closing_quantity,
            'closing_balance' => $closing_balance,
            'warehouse_id' => $item->warehouse_id ?? null,
            'mode' => $process,
            'process' => 'approved',
            'created_by' => $domain['user_id']
        ];
    }
}
