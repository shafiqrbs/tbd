<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Production\App\Models\ProductionStockHistory;

class StockItemHistoryModel extends Model
{

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
        'item_name',
        'warehouse_opening_quantity',
        'warehouse_opening_balance',
        'warehouse_closing_quantity',
        'warehouse_closing_balance'
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
            $existingStockHistory = self::where('stock_item_id', $item->stock_item_id)
                ->where('config_id', $configId)
                ->where('warehouse_id', $warehouseId)
                ->latest()
                ->first();

            # Remove this part after warehouse full implementation start ------------------------------------------
            $existingStockHistoryWithWarehouse = null;
            if ($warehouseId) {
                $existingStockHistoryWithWarehouse = self::where('stock_item_id', $item->stock_item_id)
                    ->where('config_id', $configId)
                    ->where('warehouse_id', $warehouseId)
                    ->latest()
                    ->first();
            }
            # Remove this part after warehouse full implementation end ------------------------------------------


            // Define operators for processes
            $operatorData = [
                'opening' => '+',
                'purchase' => '+',
                'sales-return' => '+',
                'purchase-return' => '-',
                'production' => '-',
                'production-issue' => '-',
                'sales' => '-',
            ];

            // Determine the operator and apply calculations
            $operator = $operatorData[$process] ?? '+';

            // Extract quantities and balances for new stock history
            list($closing_quantity, $closing_balance, $quantity,$warehouse_closing_quantity, $warehouse_closing_balance) = self::calculateClosingStock(
                $existingStockHistory,
                $item,
                $operator,
                $existingStockHistoryWithWarehouse
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
                $warehouseId,
                $existingStockHistoryWithWarehouse,
                $warehouse_closing_quantity,
                $warehouse_closing_balance
            );

            // Create stock history record
            $stockHistory = self::create($data);

            // maintain current stock quantity
            CurrentStockModel::maintainCurrentStock($configId,$warehouseId,$item->stock_item_id,$closing_quantity);

            // Update stock item quantity
            $stockItem = StockItemModel::find($item->stock_item_id);
            if ($stockItem) {
                if ($operator === '+') {
                    $stockItem->update([
                        'quantity' => ($stockItem->quantity ?? 0) + $item->quantity
                    ]);
                } elseif ($operator === '-') {
                    $stockItem->update([
                        'quantity' => ($stockItem->quantity ?? 0) - $item->quantity
                    ]);
                }
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

    private static function calculateClosingStock($existingStockHistory, $item, $operator, $existingStockHistoryWithWarehouse)
    {
        $quantity = $item->quantity ?? 0;
        $subTotal = $item->sub_total ?? 0;

        if ($operator === '+') {
            $closing_quantity = ($existingStockHistory->closing_quantity ?? 0) + $quantity;
            $closing_balance = ($existingStockHistory->closing_balance ?? 0) + $subTotal;
            $warehouse_closing_quantity = ($existingStockHistoryWithWarehouse->warehouse_closing_quantity ?? 0) + $quantity;
            $warehouse_closing_balance = ($existingStockHistoryWithWarehouse->warehouse_closing_balance ?? 0) + $subTotal;
        } elseif ($operator === '-') {
            $closing_quantity = ($existingStockHistory->closing_quantity ?? 0) - $quantity;
            $closing_balance = ($existingStockHistory->closing_balance ?? 0) - $subTotal;

            $warehouse_closing_quantity = ($existingStockHistoryWithWarehouse->warehouse_closing_quantity ?? 0) - $quantity;
            $warehouse_closing_balance = ($existingStockHistoryWithWarehouse->warehouse_closing_balance ?? 0) - $subTotal;
            $quantity = -$quantity; // Set negative quantity for deductions
        } else {
            $closing_quantity = $quantity;
            $closing_balance = $subTotal;
            $warehouse_closing_quantity = $quantity;
            $warehouse_closing_balance = $subTotal;
        }
        return [$closing_quantity, $closing_balance, $quantity,$warehouse_closing_quantity, $warehouse_closing_balance];
    }

    private static function prepareStockHistoryData($existingStockHistory, $item, $closing_quantity, $closing_balance, $quantity, $process,$domain,$configId,$warehouseId = null,$existingStockHistoryWithWarehouse,$warehouse_closing_quantity,$warehouse_closing_balance)
    {
        return [
            'stock_item_id' => $item->stock_item_id,
            'item_name' => $item->name ?? $item->item_name,
            'config_id' => $configId,
            'quantity' => $quantity,
            'purchase_price' => $item->purchase_price ?? 0,
            'sales_price' => $item->sales_price ?? 0,
            'opening_quantity' => $existingStockHistory->closing_quantity ?? 0,
            'opening_balance' => $existingStockHistory->closing_balance ?? 0,
            'closing_quantity' => $closing_quantity,
            'closing_balance' => $closing_balance,
            'warehouse_opening_quantity' => $existingStockHistoryWithWarehouse->warehouse_closing_quantity ?? 0,
            'warehouse_opening_balance' => $existingStockHistoryWithWarehouse->warehouse_closing_balance ?? 0,
            'warehouse_closing_quantity' => $warehouse_closing_quantity,
            'warehouse_closing_balance' => $warehouse_closing_balance,
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


    public static function allWarehouseWiseStockItem(int $stockItemId, int $configId , int $warehouseId = null)
    {
        $sub = self::select('warehouse_id', DB::raw('MAX(id) as latest_id'))
            ->where('stock_item_id', $stockItemId)
            ->where('config_id', $configId)
            ->groupBy('warehouse_id');

        return self::joinSub($sub, 'latest', function ($join) {
            $join->on('inv_stock_item_history.id', '=', 'latest.latest_id');
        })
            ->join('cor_warehouses as w', 'w.id', '=', 'inv_stock_item_history.warehouse_id')
            ->when($warehouseId, fn($q) => $q->where('inv_stock_item_history.warehouse_id', $warehouseId))
            ->select(
                'inv_stock_item_history.item_name',
                'inv_stock_item_history.closing_quantity as stock',
                'w.name as warehouse_name'
            )
            ->get()
            ->toArray();
    }

    public static function getStockItemHistory($params, $domain)
    {
        // default pagination setup (unchanged)
        $page = isset($params['page']) && $params['page'] > 0 ? ($params['page'] - 1) : 0;
        $perPage = isset($params['offset']) && $params['offset'] != '' ? (int)($params['offset']) : 0;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        // prepare date range
        $endDate = !empty($params['end_date']) ? $params['end_date'] : $params['start_date'];
        $startDate = Carbon::parse($params['start_date'])->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // main query builder
        $stockItems = self::join('inv_stock','inv_stock.id','=','inv_stock_item_history.stock_item_id')
            ->join('cor_warehouses','inv_stock_item_history.warehouse_id','=','cor_warehouses.id')
            ->where([
                ['inv_stock_item_history.config_id', $domain['config_id']],
                ['inv_stock_item_history.stock_item_id', $params['stock_item_id']],
                ['inv_stock_item_history.warehouse_id', $params['warehouse_id']],
            ])
            ->whereBetween('inv_stock_item_history.created_at', [$startDate, $endDate])
            ->select([
                'inv_stock_item_history.id',
                'cor_warehouses.name as warehouse_name',
                DB::raw('COALESCE(inv_stock_item_history.item_name, inv_stock.name) as item_name'),
                'inv_stock.uom',
                'inv_stock_item_history.sales_price',
                'inv_stock_item_history.purchase_price',
                'inv_stock_item_history.quantity',
                'inv_stock_item_history.opening_quantity',
                'inv_stock_item_history.opening_balance',
                'inv_stock_item_history.closing_quantity',
                'inv_stock_item_history.closing_balance',
                'inv_stock_item_history.mode',
                DB::raw('DATE_FORMAT(CONVERT_TZ(inv_stock_item_history.created_at, "+00:00", "+06:00"), "%d %b %Y, %h:%i %p") as created_date'),
            ]);

        // pagination block (same logic, just safer with cloned count)
        if ($params['page'] && $params['offset']) {
            $total = (clone $stockItems)->count();

            $items = $stockItems->skip($skip)
                ->take($perPage)
                ->orderBy('inv_stock_item_history.id', 'DESC')
                ->get();

            return [
                'count' => $total,
                'items' => $items,
            ];
        }

        // ✅ non-paginated case
        return $stockItems
            ->orderBy('inv_stock_item_history.id', 'DESC')
            ->get();
    }

}
