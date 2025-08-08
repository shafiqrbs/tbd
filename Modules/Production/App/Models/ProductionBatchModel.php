<?php

namespace Modules\Production\App\Models;

use App\Helpers\DateHelper;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\WarehouseModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;


class ProductionBatchModel extends Model
{
    protected $table = 'pro_batch';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'issue_date',
        'receive_date',
        'approve_date',
        'process',
        'remark',
        'mode',
        'invoice',
        'created_by_id',
        'approved_by_id',
        'warehouse_id',
        'status',
        'code'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $date = new DateTime("now");
            $model->status = 1;
            $model->created_at = $date;
            $model->issue_date = $date;
            $model->receive_date = $date;
        });
        self::updating(function ($model) {
            $date = new DateTime("now");
            $model->updated_at = $date;
        });
    }

    public function batchItems()
    {
        return $this->hasMany(ProductionBatchItemModel::class, 'batch_id');
    }

    public function productionConfig(): BelongsTo
    {
        return $this->belongsTo(ProductionConfig::class, 'config_id', 'id');
    }

    public function getDomainWarehouseAttribute(): Collection
    {
        // Safely access the domain ID using null-safe operator
        $domainId = $this->productionConfig?->domain_id;

        return $domainId
            ? WarehouseModel::where('domain_id', $domainId)->get()
            : collect(); // Safe fallback when productionConfig is null
    }


    public static function getRecords($request, $domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 0;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        $entity = self::where('pro_batch.config_id', $domain['pro_config'])
            ->join('users', 'users.id', '=', 'pro_batch.created_by_id')
            ->select([
                'pro_batch.id',
                'pro_batch.invoice',
                'pro_batch.status',
                'pro_batch.process',
                'pro_batch.created_by_id',
                'pro_batch.approved_by_id',
                'users.name as created_by_name',
                DB::raw('DATE_FORMAT(pro_batch.created_at, "%d-%M-%Y") as created_date'),
                DB::raw('DATE_FORMAT(pro_batch.issue_date, "%d-%M-%Y") as issue_date'),
            ]);

        if (isset($request['term']) && !empty($request['term'])) {
            $entity = $entity->whereAny(['pro_batch.invoice', 'pro_batch.process', 'users.name'], 'LIKE', '%' . trim($request['term']) . '%');
        }
        if (isset($request['created_at']) && !empty($request['created_at'])) {
            $entity = $entity->whereDate('pro_batch.created_at', trim($request['created_at']));
        }

        $total = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('id', 'DESC')
            ->get();

        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }

    public static function getShow($id, $domain)
    {
        $entity = self::where([
            ['pro_batch.config_id', '=', $domain['pro_config']],
            ['pro_batch.id', '=', $id]
        ])
            ->leftjoin('cor_warehouses', 'cor_warehouses.id', '=', 'pro_batch.warehouse_id')
            ->select([
                'pro_batch.id',
                DB::raw('DATE_FORMAT(pro_batch.created_at, "%d-%m-%Y") as created_date'),
                'pro_batch.invoice as invoice',
                'pro_batch.code',
                'pro_batch.mode',
                'pro_batch.process',
                'pro_batch.status',
                'pro_batch.issue_date',
                'pro_batch.receive_date',
                'pro_batch.remark',
                'pro_batch.warehouse_id',
                'cor_warehouses.name as warehouse_name',
            ])
            ->with(['batchItems' => function ($query) {
                $query->select([
                    'pro_batch_item.id',
                    'pro_batch_item.batch_id',
                    'pro_batch_item.production_item_id',
                    'pro_batch_item.issue_quantity',
                    'pro_batch_item.receive_quantity',
                    'pro_batch_item.damage_quantity',
                    'inv_stock.name',
                    DB::raw('DATE_FORMAT(pro_batch_item.created_at, "%d-%m-%Y") as created_date')
                ])
                    ->join('pro_item', 'pro_item.id', '=', 'pro_batch_item.production_item_id')
                    ->join('inv_stock', 'inv_stock.id', '=', 'pro_item.item_id')
                    ->with(['productionExpenses' => function ($query) {
                        $query->select([
                            'pro_expense.id',
                            'pro_expense.production_batch_item_id',
                            'pro_expense.production_item_id',
                            'pro_expense.quantity as needed_quantity',
                            'pro_expense.issue_quantity as raw_issue_quantity',
                            'pro_element.material_id',
                            'pro_element.quantity',
                            'inv_stock.quantity as stock_quantity',
                            'inv_stock.opening_quantity as opening_quantity',
                            'inv_stock.name as name',
                            'inv_stock.uom',
                        ])
                            ->join('pro_element', 'pro_element.id', '=', 'pro_expense.production_element_id')
                            ->join('inv_stock', 'inv_stock.id', '=', 'pro_element.material_id');
                    }]);
            }])
            ->first();

        // Collect unique material IDs once
        $materialIds = $entity->batchItems
            ->flatMap(fn($item) => $item->productionExpenses->pluck('material_id'))
            ->unique()
            ->values();

        // Determine stock history source
        $isWarehouse = !empty($entity?->warehouse_id);

        $stockHistories = StockItemHistoryModel::select([
            'stock_item_id',
            $isWarehouse ? 'warehouse_closing_quantity' : 'closing_quantity',
        ])
            ->whereIn('stock_item_id', $materialIds)
            ->when($isWarehouse, fn($q) => $q->where('warehouse_id', $entity->warehouse_id))
            ->orderByDesc('id')
            ->get()
            ->unique('stock_item_id')
            ->keyBy('stock_item_id');

        // Assign quantity values
        foreach ($entity->batchItems as $batchItem) {
            foreach ($batchItem->productionExpenses as $expense) {
                $materialId = $expense->material_id;

                $expense->stock_quantity = (float)(
                    $stockHistories[$materialId]->{$isWarehouse ? 'warehouse_closing_quantity' : 'closing_quantity'}
                    ?? 0.0
                );

                $expense->needed_quantity = (float)$expense->needed_quantity;

                [$expense->less_quantity, $expense->more_quantity] = $expense->needed_quantity > $expense->stock_quantity
                    ? [$expense->needed_quantity - $expense->stock_quantity, null]
                    : [null, $expense->stock_quantity - $expense->needed_quantity];
            }
        }
        return $entity;
    }

    public static function getBatchDropdown($domain)
    {
        return self::where([['pro_batch.config_id', $domain['pro_config']], ['pro_batch.status', 1]])->select(['pro_batch.id', 'pro_batch.invoice', 'mode', 'issue_date', DB::raw('DATE_FORMAT(pro_batch.created_at, "%d-%M-%Y") as created_date')])->get();
    }

    public static function issueReportData(array $params, int $domain_id, int $production_config_id)
    {
        $data = self::query()
            ->leftjoin('cor_warehouses', 'cor_warehouses.id', '=', 'pro_batch.warehouse_id')
            ->where('pro_batch.config_id', '=', $production_config_id)
            ->where('pro_batch.status', '=', 1)
            ->when(!empty($params['start_date']), function ($query) use ($params) {
                $start = Carbon::parse($params['start_date'])->startOfDay();
                $query->where('pro_batch.created_at', '>=', $start);
            })
            ->when(!empty($params['end_date']), function ($query) use ($params) {
                $end = Carbon::parse($params['end_date'])->endOfDay();
                $query->where('pro_batch.created_at', '<=', $end);
            })
            ->select([
                'pro_batch.id',
                DB::raw('DATE_FORMAT(pro_batch.created_at, "%d-%m-%Y") as created_date'),
                'pro_batch.invoice',
                'pro_batch.code',
                'pro_batch.mode',
                'pro_batch.process',
                'pro_batch.status',
                'pro_batch.issue_date',
                'pro_batch.receive_date',
                'pro_batch.remark',
                'pro_batch.warehouse_id',
                'cor_warehouses.name as warehouse_name',
            ])
            ->with(['batchItems' => function ($query) {
                $query->select([
                    'pro_batch_item.id',
                    'pro_batch_item.batch_id',
                    'pro_batch_item.production_item_id',
                    'pro_batch_item.issue_quantity',
                    'pro_batch_item.receive_quantity',
                    'pro_batch_item.damage_quantity',
                    'inv_stock.name',
                    DB::raw('DATE_FORMAT(pro_batch_item.created_at, "%d-%m-%Y") as created_date'),
                ])
                    ->join('pro_item', 'pro_item.id', '=', 'pro_batch_item.production_item_id')
                    ->join('inv_stock', 'inv_stock.id', '=', 'pro_item.item_id')
                    ->with(['productionExpenses' => function ($expenseQuery) {
                        $expenseQuery->select([
                            'pro_expense.id',
                            'pro_expense.production_batch_item_id',
                            'pro_expense.production_item_id',
                            'pro_expense.quantity as needed_quantity',
                            'pro_expense.issue_quantity as raw_issue_quantity',
                            'pro_element.material_id',
                            'pro_element.quantity',
                            'inv_stock.quantity as stock_quantity',
                            'inv_stock.opening_quantity',
                            'inv_stock.name',
                            'inv_stock.uom',
                        ])
                            ->join('pro_element', 'pro_element.id', '=', 'pro_expense.production_element_id')
                            ->join('inv_stock', 'inv_stock.id', '=', 'pro_element.material_id');
                    }]);
            }])
            ->get()
            ->each(function ($batch) {
                // Collect unique material IDs once
                $materialIds = $batch->batchItems
                    ->flatMap(fn($item) => $item->productionExpenses->pluck('material_id'))
                    ->unique()
                    ->values();

                // Determine stock history source
                $isWarehouse = !empty($batch?->warehouse_id);

                $stockHistories = StockItemHistoryModel::select([
                    'stock_item_id',
                    $isWarehouse ? 'warehouse_closing_quantity' : 'closing_quantity',
                ])
                    ->whereIn('stock_item_id', $materialIds)
                    ->when($isWarehouse, fn($q) => $q->where('warehouse_id', $batch->warehouse_id))
                    ->orderByDesc('id')
                    ->get()
                    ->unique('stock_item_id')
                    ->keyBy('stock_item_id');

                // Assign quantity values
                foreach ($batch->batchItems as $batchItem) {
                    foreach ($batchItem->productionExpenses as $expense) {
                        $materialId = $expense->material_id;

                        $expense->stock_quantity = (float)(
                            $stockHistories[$materialId]->{$isWarehouse ? 'warehouse_closing_quantity' : 'closing_quantity'}
                            ?? 0.0
                        );

                        $expense->needed_quantity = (float)$expense->needed_quantity;

                        [$expense->less_quantity, $expense->more_quantity] = $expense->needed_quantity > $expense->stock_quantity
                            ? [$expense->needed_quantity - $expense->stock_quantity, null]
                            : [null, $expense->stock_quantity - $expense->needed_quantity];
                    }
                }
            });
        return $data;
    }

    public static function warehouseMatrixReportData(array $params, int $domain_id, int $production_config_id)
    {
        // Get date range for calculations
        $dates = !empty($params['month']) && !empty($params['year'])
            ? DateHelper::getFirstAndLastDate((int)$params['year'], (int)$params['month'])
            : null;
        $startDate = $dates['first_date'] ?? null;
        $endDate = $dates['last_date'] ?? null;
        $warehouseId = $params['warehouse_id'] ?? null;

        // First, get batches in the specified date range and warehouse with date grouping
        $batchesQuery = self::query()
            ->where('pro_batch.config_id', '=', $production_config_id)
            ->where('pro_batch.status', '=', 1)
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('pro_batch.warehouse_id', '=', $warehouseId);
            })
            ->when($startDate, function ($query) use ($startDate) {
                $query->where('pro_batch.approve_date', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->where('pro_batch.approve_date', '<=', $endDate);
            })
            ->select([
                'pro_batch.id',
                DB::raw('DATE(pro_batch.approve_date) as batch_date'),
                'pro_batch.approve_date'
            ]);

        $batches = $batchesQuery->get();

        // If no batches found, return empty data
        if ($batches->isEmpty()) {
            return [
                'date_wise_data' => [],
                'production_items' => [],
                'warehouse_name' => $warehouseId ? (DB::table('cor_warehouses')->where('id', $warehouseId)->value('name') ?? 'Unknown') : 'All Warehouses',
                'meta' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'warehouse_id' => $warehouseId,
                    'message' => 'No batches found in the specified date range and warehouse'
                ]
            ];
        }

        // Group batches by date
        $batchesByDate = $batches->groupBy('batch_date');
        $allBatchIds = $batches->pluck('id')->toArray();

        // Get ALL unique production items used in these batches (for overall list)
        $productionItemsData = DB::table('pro_batch_item')
            ->join('pro_item', 'pro_item.id', '=', 'pro_batch_item.production_item_id')
            ->join('inv_stock', 'inv_stock.id', '=', 'pro_item.item_id')
            ->whereIn('pro_batch_item.batch_id', $allBatchIds)
            ->select([
                'pro_item.id as production_item_id',
                'inv_stock.name as item_name'
            ])
            ->distinct()
            ->get()
            ->keyBy('production_item_id');

        // Get raw materials used in these batches with date and batch info
        $rawMaterialsData = DB::table('pro_expense')
            ->join('pro_batch_item', 'pro_batch_item.id', '=', 'pro_expense.production_batch_item_id')
            ->join('pro_batch', 'pro_batch.id', '=', 'pro_batch_item.batch_id')
            ->join('pro_element', 'pro_element.id', '=', 'pro_expense.production_element_id')
            ->join('inv_stock', 'inv_stock.id', '=', 'pro_element.material_id')
            ->whereIn('pro_batch_item.batch_id', $allBatchIds)
            ->select([
                'pro_element.material_id',
                'inv_stock.name as material_name',
                'inv_stock.uom',
                'pro_element.price',
                'pro_expense.production_item_id',
                'pro_expense.quantity as raw_issue_quantity',
                DB::raw('DATE(pro_batch.approve_date) as batch_date'), // Changed to approve_date for consistency
                'pro_batch.id as batch_id'
            ])
            ->get();

        // Get unique materials
        $materials = $rawMaterialsData->groupBy('material_id')
            ->map(function ($materialGroup) {
                $first = $materialGroup->first();
                return [
                    'material_id' => $first->material_id,
                    'material_name' => $first->material_name,
                    'uom' => $first->uom,
                    'price' => (float)$first->price
                ];
            });

        // Group usage data by date, material, and production item
        $materialUsageByDate = $rawMaterialsData->groupBy(['batch_date', 'material_id', 'production_item_id'])
            ->map(function ($dateGroup) {
                return $dateGroup->map(function ($materialGroup) {
                    return $materialGroup->map(function ($productionGroup) {
                        return $productionGroup->sum('raw_issue_quantity');
                    });
                });
            });

        // Get all unique dates from batches
        $dateRange = $batchesByDate->keys()->sort();

        // Get opening stock data for materials used in batches only
        $materialIds = $materials->keys()->toArray();

        // Calculate date-wise data
        $dateWiseData = [];
        foreach ($dateRange as $currentDate) {
            $carbonDate = Carbon::parse($currentDate);

            // Get batch IDs for the current date
            $currentDateBatchIds = $batchesByDate->get($currentDate)->pluck('id')->toArray();

            // Get production items produced on this specific date
            $productionItemsOnCurrentDate = DB::table('pro_batch_item')
                ->join('pro_item', 'pro_item.id', '=', 'pro_batch_item.production_item_id')
                ->join('inv_stock', 'inv_stock.id', '=', 'pro_item.item_id')
                ->whereIn('pro_batch_item.batch_id', $currentDateBatchIds)
                ->select([
                    'pro_item.id as production_item_id',
                    'inv_stock.name as item_name'
                ])
                ->distinct()
                ->get()
                ->keyBy('production_item_id');

            // Get opening stock (previous day's closing or initial opening)
            $previousDate = $carbonDate->copy()->subDay()->endOfDay();
            $openingStockData = collect();
            if (!empty($materialIds)) {
                $openingStockQuery = DB::table('inv_stock_item_history')
                    ->select([
                        'stock_item_id',
                        $warehouseId ? 'warehouse_closing_quantity' : 'closing_quantity'
                    ])
                    ->whereIn('stock_item_id', $materialIds)
                    ->where('created_at', '<=', $previousDate)
                    ->when($warehouseId, function ($q) use ($warehouseId) {
                        $q->where('warehouse_id', $warehouseId);
                    })
                    ->orderBy('stock_item_id')
                    ->orderByDesc('created_at')
                    ->get()
                    ->unique('stock_item_id')
                    ->keyBy('stock_item_id');
                $openingStockData = $openingStockQuery;
            }

            // Get IN data (purchase entries for current date)
            $inData = collect();
            if (!empty($materialIds)) {
                $dayStart = Carbon::parse($currentDate)->startOfDay();
                $dayEnd = Carbon::parse($currentDate)->endOfDay();
                $inQuery = DB::table('inv_stock_item_history')
                    ->select([
                        'stock_item_id',
                        DB::raw('SUM(quantity) as total_in_quantity')
                    ])
                    ->whereIn('stock_item_id', $materialIds)
                    ->where('mode', 'purchase')
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->when($warehouseId, function ($q) use ($warehouseId) {
                        $q->where('warehouse_id', $warehouseId);
                    })
                    ->groupBy('stock_item_id')
                    ->get()
                    ->keyBy('stock_item_id');
                $inData = $inQuery;
            }

            // Format the data for current date - ONLY MATERIALS USED ON THIS DATE
            $reportData = [];
            $serialNo = 1;
            // Get materials used on current date only
            $materialsUsedOnDate = $materialUsageByDate->get($currentDate, collect());

            foreach ($materialsUsedOnDate as $materialId => $productionUsage) {
                $material = $materials->get($materialId);
                if (!$material) continue; // Skip if material not found

                $closingQuantityField = $warehouseId ? 'warehouse_closing_quantity' : 'closing_quantity';
                $openingStock = (float)($openingStockData->get($materialId)?->{$closingQuantityField} ?? 0);
                $inQuantity = (float)($inData->get($materialId)?->total_in_quantity ?? 0);
                $totalIn = $openingStock + $inQuantity;
                $price = $material['price'];
                $inAmount = $totalIn * $price;

                // Calculate production item usage for this material on current date
                $productionUsageFormatted = [];
                $totalOut = 0;
                // Iterate over production items *relevant to this date*
                foreach ($productionItemsOnCurrentDate as $prodId => $prodItem) {
                    $usage = (float)($productionUsage->get($prodId) ?? 0);
                    $amount = $usage * $price;
                    $productionUsageFormatted[$prodId] = [
                        'quantity' => $usage,
                        'amount' => $amount,
                    ];
                    $totalOut += $usage;
                }

                $outAmount = $totalOut * $price;
                $closingStock = $totalIn - $totalOut;
                $closingStockAmount = $closingStock * $price;

                $reportData[] = [
                    'sl' => $serialNo++,
                    'material_id' => $materialId,
                    'product_name_bangla' => $material['material_name'],
                    'product_name_english' => $material['material_name'],
                    'unit' => $material['uom'],
                    'present_day_rate' => $price,
                    'opening_stock' => $openingStock,
                    'in_warehouse' => $inQuantity,
                    'total_in' => $totalIn,
                    'in_amount' => $inAmount,
                    'production_usage' => $productionUsageFormatted,
                    'out' => $totalOut,
                    'out_amount' => $outAmount,
                    'closing_stock' => $closingStock,
                    'closing_stock_amount' => $closingStockAmount,
                ];
            }

            $dateWiseData[$currentDate] = [
                'date' => $carbonDate->format('d-m-Y'),
                'date_formatted' => $carbonDate->format('d'), // Monday, July 21, 2025
                'data' => $reportData,
                'production_items_on_date' => $productionItemsOnCurrentDate->toArray(), // Added date-specific production items
                'summary' => [
                    'total_materials' => count($reportData),
                    'total_in_amount' => array_sum(array_column($reportData, 'in_amount')),
                    'total_out_amount' => array_sum(array_column($reportData, 'out_amount')),
                    'total_closing_amount' => array_sum(array_column($reportData, 'closing_stock_amount')),
                ]
            ];
        }

        // Get warehouse name
        $warehouseName = 'All Warehouses';
        if ($warehouseId) {
            $warehouse = DB::table('cor_warehouses')->where('id', $warehouseId)->first();
            $warehouseName = $warehouse ? $warehouse->name : 'Unknown Warehouse';
        }

        return [
            'date_wise_data' => $dateWiseData,
            'production_items' => $productionItemsData->toArray(), // Kept for overall list of all production items
            'warehouse_name' => $warehouseName,
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'warehouse_id' => $warehouseId,
                'total_dates' => count($dateWiseData),
                'total_production_items' => count($productionItemsData), // This is for the global count
                'date_range' => $dateRange->toArray(),
            ]
        ];
    }

}
