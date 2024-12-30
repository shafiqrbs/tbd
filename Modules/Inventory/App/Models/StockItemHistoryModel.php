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

    public static function openingStockQuantity($item, $process = 'opening')
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
                $process
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
            StockItemInventoryHistoryModel::openingInventoryHistory($item, $stockHistory,$process);

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

    private static function prepareStockHistoryData($existingStockHistory, $item, $closing_quantity, $closing_balance, $quantity, $process)
    {
        return [
            'stock_item_id' => $item->stock_item_id,
            'config_id' => $item->config_id,
            'quantity' => $quantity,
            'price' => $item->purchase_price ?? 0,
            'opening_quantity' => $existingStockHistory->closing_quantity ?? 0,
            'opening_balance' => $existingStockHistory->closing_balance ?? 0,
            'closing_quantity' => $closing_quantity,
            'closing_balance' => $closing_balance,
            'wearhouse_id' => $item->wearhouse_id ?? null,
            'mode' => $process,
            'process' => 'approved',
            'created_by' => $item->approved_by_id
        ];
    }




    /*public static function openingStockQuantity($item, $process = 'opening')
    {
        // Check if record exists
        $existingStockHistory = self::where('stock_item_id', $item->stock_item_id)
            ->where('config_id', $item->config_id)
            ->latest()
            ->first();

        // select operator
        $operatorData = [
            'opening' => '+',
            'purchase' => '+',
            'sales' => '-'
        ];

            // Get the operator based on the process
            $operator = $operatorData[$process] ?? '+';

            // Perform the dynamic calculation for `closing_quantity`
            if ($operator === '+') {
                $closing_quantity = $existingStockHistory ? $existingStockHistory->closing_quantity + $item->quantity : $item->quantity;
                $closing_balance = $existingStockHistory ? $existingStockHistory->closing_balance + $item->sub_total: $item->sub_total;
                $quantity = $item->quantity ?? 0;
            } elseif ($operator === '-') {
                $closing_quantity = $existingStockHistory ? $existingStockHistory->closing_quantity - $item->quantity : $item->quantity;
                $closing_balance = $existingStockHistory ? $existingStockHistory->closing_balance - $item->sub_total : $item->sub_total;
                $quantity = -$item->quantity ?? 0;
            }

        // Prepare common data variables
            $data = [
                'stock_item_id' => $item->stock_item_id,
                'config_id' => $item->config_id,
                'quantity' => $quantity,
                'price' => $item->purchase_price ?? 0,
                'opening_quantity' => $existingStockHistory ? $existingStockHistory->closing_quantity : 0 ,
                'opening_balance' => $existingStockHistory ? $existingStockHistory->closing_balance : 0,
                'closing_quantity' => $closing_quantity,
                'closing_balance' => $closing_balance,
                'wearhouse_id' => $item->wearhouse_id ?? null,
                'mode' => $process,
                'process' => 'approved',
                'created_by' => $item->approved_by_id
            ];

            $stockHistory = self::create($data);
            $findStockItem = StockItemModel::find($item->stock_item_id);
            $findStockItem->update(['quantity' => $stockHistory->closing_quantity]);

            $totalProductQty = StockItemModel::calculateTotalStockQuantity($findStockItem->product_id, $findStockItem->config_id);

            $findProduct = ProductModel::find($findStockItem->product_id);
            $findProduct->update(['quantity' => $totalProductQty]);

            // create opening inventory-history if needed
            StockItemInventoryHistoryModel::openingInventoryHistory($item,$stockHistory);

        return true;
    }*/

}
