<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Production\App\Models\ProductionStockHistory;

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
            $configId = $item->config_id;
            $warehouseId = $item->warehouse_id;
            if ($process === 'production-issue'){
                $findStockItem = StockItemModel::find($item->stock_item_id);
                $configId = $findStockItem->config_id;
                $warehouseId = $item->product_warehouse_id;
            }
            // Fetch the latest stock history
            /*$existingStockHistory = self::where('stock_item_id', $item->stock_item_id)
                ->where('config_id', $configId)
                ->latest()
                ->first();*/

            $existingStockHistory = self::where('stock_item_id', $item->stock_item_id)
                ->where('config_id', $configId)
                ->when(!empty($warehouseId), function ($query) use ($warehouseId) {
                    $query->where('warehouse_id', $warehouseId);
                })
                ->latest()
                ->first();

            // Define operators for processes
            $operatorData = [
                'opening' => '+',
                'purchase' => '+',
                'production' => '-',
                'production-issue' => '-',
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
                $domain,
                $configId,
                $warehouseId
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
            if ($process == 'production-issue') {
                ProductionStockHistory::openingInventoryHistory($item, $stockHistory, $process, $domain);
            }else{
                StockItemInventoryHistoryModel::openingInventoryHistory($item, $stockHistory, $process, $domain);
            }

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

    private static function prepareStockHistoryData($existingStockHistory, $item, $closing_quantity, $closing_balance, $quantity, $process,$domain,$configId,$warehouseId = null)
    {
        return [
            'stock_item_id' => $item->stock_item_id,
            'item_name' => $item->name,
            'config_id' => $configId,
            'quantity' => $quantity,
            'purchase_price' => $item->purchase_price ?? 0,
            'sales_price' => $item->sales_price ?? 0,
            'opening_quantity' => $existingStockHistory->closing_quantity ?? 0,
            'opening_balance' => $existingStockHistory->closing_balance ?? 0,
            'closing_quantity' => $closing_quantity,
            'closing_balance' => $closing_balance,
            'warehouse_id' => $warehouseId,
            'mode' => $process,
            'process' => 'approved',
            'created_by' => $domain['user_id'],
        ];
    }

    public static function getUserWarehouseProductionItem($userId)
    {
        $sub = DB::table('inv_stock_item_history')
            ->select('stock_item_id', 'warehouse_id', DB::raw('MAX(created_at) as max_created'))
            ->groupBy('stock_item_id', 'warehouse_id');

        $latestEntries = DB::table('inv_stock_item_history as h1')
            ->select([
                'h1.id',
                'h1.stock_item_id',
                'h1.sales_price',
                'h1.quantity',
                'h1.opening_quantity',
                'h1.opening_balance',
                'h1.closing_quantity',
                'h1.purchase_price',
                'h1.closing_balance',
                'h1.process',
                'h1.mode',
                'h1.warehouse_id',
                'w.name as warehouse_name',
                'si.name as item_name',
                'si.quantity as stock_quantity',
                'si.uom',
                'uw.id as user_warehouse_id'
            ])
            ->join('cor_user_warehouse as uw', function($join) use ($userId) {
                $join->on('uw.warehouse_id', '=', 'h1.warehouse_id')
                    ->where('uw.user_id', $userId);
            })
            ->join('cor_warehouses as w', 'w.id', '=', 'uw.warehouse_id')
            ->join('inv_stock as si', 'si.id', '=', 'h1.stock_item_id')

            ->joinSub($sub, 'h2', function($join) {
                $join->on('h1.stock_item_id', '=', 'h2.stock_item_id')
                    ->on('h1.warehouse_id', '=', 'h2.warehouse_id')
                    ->on('h1.created_at', '=', 'h2.max_created');
            })
            ->where('h1.closing_quantity','>',0)
            ->get()
            ->toArray();

        return $latestEntries;

    }
}
