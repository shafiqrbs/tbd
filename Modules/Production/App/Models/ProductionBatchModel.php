<?php

namespace Modules\Production\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\WarehouseModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;


class ProductionBatchModel extends Model
{
    use HasFactory;

    protected $table = 'pro_batch';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'issue_date',
        'receive_date',
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

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->status = 1;
            $model->created_at = $date;
            $model->issue_date = $date;
            $model->receive_date = $date;
        });
        self::updating(function ($model) {
            $date =  new \DateTime("now");
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


    public static function getRecords($request,$domain){
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;

        $entity = self::where('pro_batch.config_id',$domain['pro_config'])
            ->join('users','users.id','=','pro_batch.created_by_id')
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

        if (isset($request['term']) && !empty($request['term'])){
            $entity = $entity->whereAny(['pro_batch.invoice','pro_batch.process','users.name'],'LIKE','%'.trim($request['term']).'%');
        }
        if (isset($request['created_at']) && !empty($request['created_at'])) {
            $entity = $entity->whereDate('pro_batch.created_at', trim($request['created_at']));
        }

        $total  = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')
            ->get();

        $data = array('count'=>$total,'entities' => $entities);
        return $data;
    }

    public static function getShow($id,$domain)
    {
        $entity = self::where([
            ['pro_batch.config_id', '=', $domain['pro_config']],
            ['pro_batch.id', '=', $id]
        ])
            ->leftjoin('cor_warehouses','cor_warehouses.id','=','pro_batch.warehouse_id')
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
                    ->join('pro_item','pro_item.id','=','pro_batch_item.production_item_id')
                    ->join('inv_stock','inv_stock.id','=','pro_item.item_id')
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
                            ->join('pro_element','pro_element.id','=','pro_expense.production_element_id')
                            ->join('inv_stock','inv_stock.id','=','pro_element.material_id')
                        ;
                    }]);
            }])
            ->first();

        // Collect unique material IDs once
        $materialIds = $entity->batchItems
            ->flatMap(fn ($item) => $item->productionExpenses->pluck('material_id'))
            ->unique()
            ->values();

        // Determine stock history source
        $isWarehouse = !empty($entity?->warehouse_id);

        $stockHistories = StockItemHistoryModel::select([
            'stock_item_id',
            $isWarehouse ? 'warehouse_closing_quantity' : 'closing_quantity',
        ])
            ->whereIn('stock_item_id', $materialIds)
            ->when($isWarehouse, fn ($q) => $q->where('warehouse_id', $entity->warehouse_id))
            ->orderByDesc('id')
            ->get()
            ->unique('stock_item_id')
            ->keyBy('stock_item_id');

        // Assign quantity values
        foreach ($entity->batchItems as $batchItem) {
            foreach ($batchItem->productionExpenses as $expense) {
                $materialId = $expense->material_id;

                $expense->stock_quantity = (float) (
                    $stockHistories[$materialId]->{ $isWarehouse ? 'warehouse_closing_quantity' : 'closing_quantity' }
                    ?? 0.0
                );

                $expense->needed_quantity = (float) $expense->needed_quantity;

                [$expense->less_quantity, $expense->more_quantity] = $expense->needed_quantity > $expense->stock_quantity
                    ? [$expense->needed_quantity - $expense->stock_quantity, null]
                    : [null, $expense->stock_quantity - $expense->needed_quantity];
            }
        }
        return $entity;
    }

    public static function getBatchDropdown($domain)
    {
        return self::where([['pro_batch.config_id',$domain['pro_config']],['pro_batch.status',1]])->select(['pro_batch.id','pro_batch.invoice','mode','issue_date',DB::raw('DATE_FORMAT(pro_batch.created_at, "%d-%M-%Y") as created_date')])->get();
    }

    public static function issueReportData(array $params, int $domain_id, int $production_config_id) {
        $data = self::query()
            ->leftjoin('cor_warehouses','cor_warehouses.id','=','pro_batch.warehouse_id')
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
                    ->flatMap(fn ($item) => $item->productionExpenses->pluck('material_id'))
                    ->unique()
                    ->values();

                // Determine stock history source
                $isWarehouse = !empty($batch?->warehouse_id);

                $stockHistories = StockItemHistoryModel::select([
                    'stock_item_id',
                    $isWarehouse ? 'warehouse_closing_quantity' : 'closing_quantity',
                ])
                    ->whereIn('stock_item_id', $materialIds)
                    ->when($isWarehouse, fn ($q) => $q->where('warehouse_id', $batch->warehouse_id))
                    ->orderByDesc('id')
                    ->get()
                    ->unique('stock_item_id')
                    ->keyBy('stock_item_id');

                // Assign quantity values
                foreach ($batch->batchItems as $batchItem) {
                    foreach ($batchItem->productionExpenses as $expense) {
                        $materialId = $expense->material_id;

                        $expense->stock_quantity = (float) (
                            $stockHistories[$materialId]->{ $isWarehouse ? 'warehouse_closing_quantity' : 'closing_quantity' }
                            ?? 0.0
                        );

                        $expense->needed_quantity = (float) $expense->needed_quantity;

                        [$expense->less_quantity, $expense->more_quantity] = $expense->needed_quantity > $expense->stock_quantity
                            ? [$expense->needed_quantity - $expense->stock_quantity, null]
                            : [null, $expense->stock_quantity - $expense->needed_quantity];
                    }
                }
            });
        return $data;
    }



    /*public static function warehouseMatrixReportData(array $params, int $domain_id, int $production_config_id) {
        // Get date range for calculations
        $startDate = !empty($params['start_date']) ? Carbon::parse($params['start_date'])->startOfDay() : null;
        $endDate = !empty($params['end_date']) ? Carbon::parse($params['end_date'])->endOfDay() : null;
        $warehouseId = $params['warehouse_id'] ?? null;

        // First, get batches in the specified date range and warehouse with date grouping
        $batchesQuery = self::query()
            ->where('pro_batch.config_id', '=', $production_config_id)
            ->where('pro_batch.status', '=', 1)
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('pro_batch.warehouse_id', '=', $warehouseId);
            })
            ->when($startDate, function ($query) use ($startDate) {
                $query->where('pro_batch.created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->where('pro_batch.created_at', '<=', $endDate);
            })
            ->select([
                'pro_batch.id',
                DB::raw('DATE(pro_batch.created_at) as batch_date'),
                'pro_batch.created_at'
            ]);

        $batches = $batchesQuery->get();

        // If no batches found, return empty data
        if ($batches->isEmpty()) {
            return [
                'data' => [],
                'production_items' => [],
                'warehouse_name' => $warehouseId ? (DB::table('cor_warehouses')->where('id', $warehouseId)->value('name') ?? 'Unknown') : 'All Warehouses',
                'meta' => [
                    'start_date' => $startDate?->format('d-m-Y'),
                    'end_date' => $endDate?->format('d-m-Y'),
                    'warehouse_id' => $warehouseId,
                    'message' => 'No batches found in the specified date range and warehouse'
                ]
            ];
        }

        // Group batches by date
        $batchesByDate = $batches->groupBy('batch_date');
        $allBatchIds = $batches->pluck('id')->toArray();

        // Get production items used in these batches only
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
                'pro_expense.issue_quantity as raw_issue_quantity',
                DB::raw('DATE(pro_batch.created_at) as batch_date'),
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
                    'price' => (float) $first->price
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

        // Calculate date-wise stock for each material
        $dateWiseData = [];

        foreach ($dateRange as $currentDate) {
            $carbonDate = Carbon::parse($currentDate);

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

            // Format the data for current date
            $reportData = [];
            $serialNo = 1;

            foreach ($materials as $materialId => $material) {
                $closingQuantityField = $warehouseId ? 'warehouse_closing_quantity' : 'closing_quantity';
                $openingStock = (float) ($openingStockData->get($materialId)?->{$closingQuantityField} ?? 0);
                $inQuantity = (float) ($inData->get($materialId)?->total_in_quantity ?? 0);
                $totalIn = $openingStock + $inQuantity;
                $price = $material['price'];
                $inAmount = $totalIn * $price;

                // Calculate production item usage for this material on current date
                $productionUsage = [];
                $totalOut = 0;

                foreach ($productionItemsData as $prodId => $prodItem) {
                    $usage = (float) ($materialUsageByDate->get($currentDate)?->get($materialId)?->get($prodId) ?? 0);
                    $amount = $usage * $price;

                    $productionUsage[$prodId] = [
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
                    'production_usage' => $productionUsage,
                    'out' => $totalOut,
                    'out_amount' => $outAmount,
                    'closing_stock' => $closingStock,
                    'closing_stock_amount' => $closingStockAmount,
                ];
            }

            $dateWiseData[$currentDate] = [
                'date' => $carbonDate->format('d-m-Y'),
                'date_formatted' => $carbonDate->format('l, F j, Y'), // Monday, July 21, 2025
                'data' => $reportData,
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
            'production_items' => $productionItemsData->toArray(),
            'warehouse_name' => $warehouseName,
            'meta' => [
                'start_date' => $startDate?->format('d-m-Y'),
                'end_date' => $endDate?->format('d-m-Y'),
                'warehouse_id' => $warehouseId,
                'total_dates' => count($dateWiseData),
                'total_production_items' => count($productionItemsData),
                'date_range' => $dateRange->toArray(),
            ]
        ];
    }*/
    public static function warehouseMatrixReportData(array $params, int $domain_id, int $production_config_id) {
        // Get date range for calculations
        $startDate = !empty($params['start_date']) ? Carbon::parse($params['start_date'])->startOfDay() : null;
        $endDate = !empty($params['end_date']) ? Carbon::parse($params['end_date'])->endOfDay() : null;
        $warehouseId = $params['warehouse_id'] ?? null;

        // First, get batches in the specified date range and warehouse with date grouping
        $batchesQuery = self::query()
            ->where('pro_batch.config_id', '=', $production_config_id)
            ->where('pro_batch.status', '=', 1)
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('pro_batch.warehouse_id', '=', $warehouseId);
            })
            ->when($startDate, function ($query) use ($startDate) {
                $query->where('pro_batch.created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->where('pro_batch.created_at', '<=', $endDate);
            })
            ->select([
                'pro_batch.id',
                DB::raw('DATE(pro_batch.created_at) as batch_date'),
                'pro_batch.created_at'
            ]);

        $batches = $batchesQuery->get();

        // If no batches found, return empty data
        if ($batches->isEmpty()) {
            return [
                'data' => [],
                'production_items' => [],
                'warehouse_name' => $warehouseId ? (DB::table('cor_warehouses')->where('id', $warehouseId)->value('name') ?? 'Unknown') : 'All Warehouses',
                'meta' => [
                    'start_date' => $startDate?->format('d-m-Y'),
                    'end_date' => $endDate?->format('d-m-Y'),
                    'warehouse_id' => $warehouseId,
                    'message' => 'No batches found in the specified date range and warehouse'
                ]
            ];
        }

        // Group batches by date
        $batchesByDate = $batches->groupBy('batch_date');
        $allBatchIds = $batches->pluck('id')->toArray();

        // Get production items used in these batches only
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
                'pro_expense.issue_quantity as raw_issue_quantity',
                DB::raw('DATE(pro_batch.created_at) as batch_date'),
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
                    'price' => (float) $first->price
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

        // Calculate date-wise stock for each material
        $dateWiseData = [];
        foreach ($dateRange as $currentDate) {
            $carbonDate = Carbon::parse($currentDate);

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
                $openingStock = (float) ($openingStockData->get($materialId)?->{$closingQuantityField} ?? 0);
                $inQuantity = (float) ($inData->get($materialId)?->total_in_quantity ?? 0);
                $totalIn = $openingStock + $inQuantity;
                $price = $material['price'];
                $inAmount = $totalIn * $price;

                // Calculate production item usage for this material on current date
                $productionUsageFormatted = [];
                $totalOut = 0;
                foreach ($productionItemsData as $prodId => $prodItem) {
                    $usage = (float) ($productionUsage->get($prodId) ?? 0);
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
                'date_formatted' => $carbonDate->format('l, F j, Y'), // Monday, July 21, 2025
                'data' => $reportData,
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
            'production_items' => $productionItemsData->toArray(),
            'warehouse_name' => $warehouseName,
            'meta' => [
                'start_date' => $startDate?->format('d-m-Y'),
                'end_date' => $endDate?->format('d-m-Y'),
                'warehouse_id' => $warehouseId,
                'total_dates' => count($dateWiseData),
                'total_production_items' => count($productionItemsData),
                'date_range' => $dateRange->toArray(),
            ]
        ];
    }

// Helper method to format date-wise data for display
    public static function formatDateWiseMatrixReportForDisplay($reportData) {
        $dateWiseData = $reportData['date_wise_data'];
        $productionItems = $reportData['production_items'];
        $warehouseName = $reportData['warehouse_name'];

        $formattedData = [];

        foreach ($dateWiseData as $date => $dateData) {
            $dayData = [];

            foreach ($dateData['data'] as $row) {
                $formattedRow = [
                    'S.L.' => $row['sl'],
                    'পণ্যের নাম (বাংলায়)' => $row['product_name_bangla'],
                    'Product Name (English)' => $row['product_name_english'],
                    'Unit' => $row['unit'],
                    'Present Day Rate' => number_format($row['present_day_rate'], 2),
                    'Opening Stock' => number_format($row['opening_stock'], 2),
                    "IN {$warehouseName}" => number_format($row['in_warehouse'], 2),
                    'Total In' => number_format($row['total_in'], 2),
                    'IN Amount (TK)' => number_format($row['in_amount'], 2),
                ];

                // Add dynamic production item columns
                foreach ($productionItems as $prodId => $prodItem) {
                    $usage = $row['production_usage'][$prodId] ?? ['quantity' => 0, 'amount' => 0];
                    $formattedRow[$prodItem['item_name']] = number_format($usage['quantity'], 2);
                    $formattedRow[$prodItem['item_name'] . ' Out Amount'] = number_format($usage['amount'], 2);
                }

                $formattedRow['Out'] = number_format($row['out'], 2);
                $formattedRow['Out Amount (TK)'] = number_format($row['out_amount'], 2);
                $formattedRow['Closing Stock'] = number_format($row['closing_stock'], 2);
                $formattedRow['Closing Stock Amount (TK)'] = number_format($row['closing_stock_amount'], 2);

                $dayData[] = $formattedRow;
            }

            $formattedData[$date] = [
                'date' => $dateData['date'],
                'date_formatted' => $dateData['date_formatted'],
                'data' => $dayData,
                'summary' => $dateData['summary']
            ];
        }

        return $formattedData;
    }

// Helper method to get consolidated summary across all dates
    public static function getConsolidatedSummary($reportData) {
        $dateWiseData = $reportData['date_wise_data'];
        $consolidatedData = [];

        // Consolidate all materials across all dates
        foreach ($dateWiseData as $date => $dateData) {
            foreach ($dateData['data'] as $row) {
                $materialId = $row['material_id'];

                if (!isset($consolidatedData[$materialId])) {
                    $consolidatedData[$materialId] = $row;
                    $consolidatedData[$materialId]['dates_used'] = [$date];
                } else {
                    // Accumulate quantities
                    $consolidatedData[$materialId]['in_warehouse'] += $row['in_warehouse'];
                    $consolidatedData[$materialId]['out'] += $row['out'];
                    $consolidatedData[$materialId]['in_amount'] += $row['in_amount'];
                    $consolidatedData[$materialId]['out_amount'] += $row['out_amount'];
                    $consolidatedData[$materialId]['dates_used'][] = $date;

                    // Accumulate production usage
                    foreach ($row['production_usage'] as $prodId => $usage) {
                        $consolidatedData[$materialId]['production_usage'][$prodId]['quantity'] += $usage['quantity'];
                        $consolidatedData[$materialId]['production_usage'][$prodId]['amount'] += $usage['amount'];
                    }
                }
            }
        }

        return array_values($consolidatedData);
    }

}
