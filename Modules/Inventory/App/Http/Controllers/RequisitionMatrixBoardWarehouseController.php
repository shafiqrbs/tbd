<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Models\CurrentStockModel;
use Modules\Inventory\App\Models\InvoiceBatchItemModel;
use Modules\Inventory\App\Models\InvoiceBatchModel;
use Modules\Inventory\App\Models\RequisitionBoardModel;
use Modules\Inventory\App\Models\RequisitionItemModel;
use Modules\Inventory\App\Models\RequisitionMatrixBoardModel;
use Modules\Inventory\App\Models\RequisitionModel;
use Modules\Inventory\App\Models\RequisitionProductItemMatrixModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Production\App\Models\ProductionBatchItemModel;
use Modules\Production\App\Models\ProductionBatchModel;
use Modules\Production\App\Models\ProductionExpense;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class   RequisitionMatrixBoardWarehouseController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if ($userId && !empty($userId)) {
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }

    public function store(Request $request)
    {
        $vendorConfigId = $this->domain['config_id'];
        $expectedDate = $request->get('expected_date');
        $childConfigId = $request->get('config_id');

        // Validate input
        if (!$expectedDate || empty($expectedDate)) {
            throw new Exception("Expected date not found");
        }
        // Validate input
        if (!$childConfigId || empty($childConfigId)) {
            throw new Exception("Domain not found");
        }

        DB::beginTransaction(); // Start transaction

        try {
            // Fetch the latest data
            $getBoard = RequisitionBoardModel::where([
                ['config_id', $vendorConfigId],
                ['generate_date', $request->expected_date],
                ['process', 'Created']
            ])->first();

            if ($getBoard) {
                return response()->json([
                    'status' => ResponseAlias::HTTP_CONFLICT,
                    'message' => 'A board has already been created. Please settle the existing one before creating a new one.',
                ], ResponseAlias::HTTP_CONFLICT);
            }

            // Fetch Requisition Items
            $getItems = RequisitionItemModel::where([
                ['inv_requisition_item.customer_config_id', $childConfigId],
                ['inv_requisition_item.vendor_config_id', $vendorConfigId],
                ['inv_requisition.expected_date', '<=', $expectedDate]
            ])
                ->whereIn('inv_requisition.process', ['Approved'])
                ->whereNotNull('inv_requisition.approved_by_id')
                ->select([
                    'inv_requisition_item.id',
                    'cor_warehouses.name as warehouse_name',
                    'inv_requisition_item.warehouse_id',
                    'inv_requisition_item.vendor_config_id',
                    'inv_requisition_item.customer_config_id',
                    'inv_requisition_item.vendor_stock_item_id',
                    'inv_requisition_item.customer_stock_item_id',
                    'inv_requisition_item.quantity',
                    'inv_requisition_item.barcode',
                    'inv_requisition_item.purchase_price',
                    'inv_requisition_item.sales_price',
                    'inv_requisition_item.sub_total',
                    'inv_requisition_item.display_name',
                    'inv_requisition_item.unit_name',
                    'inv_requisition_item.unit_id',
                    'inv_requisition.customer_id',
                    'cor_customers.name as customer_name',
                    'cor_customers.mobile as customer_mobile',
                    'inv_requisition.expected_date',
                    'vendor_stock_item.quantity as remaining_quantity',
                    'vendor_stock_item.quantity as vendor_stock_quantity',
                    'inv_requisition.id as requisition_id',
                ])
                ->join('cor_warehouses', 'cor_warehouses.id', '=', 'inv_requisition_item.warehouse_id')
                ->leftjoin('inv_requisition', 'inv_requisition.id', '=', 'inv_requisition_item.requisition_id')
                ->leftjoin('inv_stock as vendor_stock_item', 'vendor_stock_item.id', '=', 'inv_requisition_item.vendor_stock_item_id')
                ->leftjoin('cor_customers', 'cor_customers.id', '=', 'inv_requisition.customer_id')
                ->get()
                ->toArray();

            if (count($getItems) > 0) {
                $board = RequisitionBoardModel::create([
                    'config_id' => $vendorConfigId,
                    'created_by_id' => $this->domain['user_id'],
                    'total' => 0,
                    'status' => 1,
                    'process' => 'Created',
                    'generate_date' => $expectedDate,
                    'is_warehouse_board' => true
                ]);
            } else {
                return response()->json([
                    'status' => ResponseAlias::HTTP_CONFLICT,
                    'message' => 'No available data for create board.',
                ], ResponseAlias::HTTP_CONFLICT);
            }

            // Update process for fetched requisition items
            $requisitionIds = array_unique(array_column($getItems, 'requisition_id'));
            RequisitionModel::whereIn('id', $requisitionIds)->update(['process' => 'Generated', 'matrix_generate_date' => $expectedDate]);

            $groupedItems = $this->groupByCustomerStockItemIdAndConfigId($getItems);

            if (!empty($groupedItems)) {
                $itemsToInsert = [];

                foreach ($groupedItems as $val) {
                    $findProItem = DB::table('pro_item')
                        ->where('item_id', $val['vendor_stock_item_id'])
                        ->where('config_id', $this->domain['pro_config'])
                        ->where('process', 'approved')
                        ->first();

                    $warehouseId = $this->domain['warehouse_id'];
                    if ($findProItem) {
                        $warehouseId = $findProItem->warehouse_id;
                    }

                    $itemsToInsert[] = [
                        'customer_stock_item_id' => $val['customer_stock_item_id'],
                        'vendor_stock_item_id' => $val['vendor_stock_item_id'],
                        'requisition_item_id' => $val['requisition_item_id'],
                        'customer_config_id' => $val['customer_config_id'],
                        'vendor_config_id' => $val['vendor_config_id'],
                        'unit_id' => $val['unit_id'],
                        'barcode' => $val['barcode'],
                        'purchase_price' => $val['purchase_price'],
                        'sales_price' => $val['sales_price'],
                        'quantity' => $val['quantity'],
                        'requested_quantity' => $val['quantity'],
                        'approved_quantity' => $val['quantity'],
                        'sub_total' => $val['sub_total'],
                        'display_name' => $val['display_name'],
                        'unit_name' => $val['unit_name'],
                        'customer_id' => $val['customer_id'],
                        'customer_name' => $val['customer_name'],
                        'expected_date' => $val['expected_date'],
                        'generate_date' => $expectedDate,
//                        'vendor_stock_quantity' => $val['vendor_stock_quantity'],
                        'vendor_stock_quantity' => CurrentStockModel::getCurrentStockByWarehouseAndStockItemId(
                            $this->domain['config_id'],
                            $warehouseId,
                            $val['vendor_stock_item_id']
                        ),
                        'status' => true,
                        'process' => 'Created',
                        'requisition_board_id' => $board->id,
                        'created_at' => now(),
                        'warehouse_id' => $warehouseId,
                        'child_warehouse_id' => $val['child_warehouse_id'],
                        'child_warehouse_name' => $val['child_warehouse_name'],

                    ];
                    // Check if a Generated record already exists for this vendor and expected date
                }

                if (!empty($itemsToInsert)) {
                    // Delete previously generated records before inserting new ones
                    RequisitionMatrixBoardModel::where([
                        ['generate_date', $expectedDate],
                        ['process', 'Created'],
                        ['vendor_config_id', $val['vendor_config_id']]
                    ])->delete();

                    // Insert new records
                    RequisitionMatrixBoardModel::insert($itemsToInsert);
                }
            }

            DB::commit(); // Commit transaction

            return response()->json([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'Matrix data process successfully',
                'data' => $board
            ], ResponseAlias::HTTP_OK);

        } catch (Exception $e) {
            DB::rollBack(); // Rollback if any error
            Log::error("Matrix Batch Generation Error: " . $e->getMessage());

            return response()->json([
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'An error occurred while generating the batch',
                'error' => $e->getMessage(),
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function matrixBoardDetails(Request $request, $id)
    {
        DB::beginTransaction(); // Start Database Transaction

        try {
            $findBoard = RequisitionBoardModel::find($id);
            // Fetch the latest data after insertion
            $getItemWiseProduct = $findBoard->requisition_matrix->toArray();

            $shops = $this->getCustomerNames($getItemWiseProduct);
            $transformedData = $this->formatMatrixBoardData($getItemWiseProduct, $shops);

            return response()->json([
                'status' => ResponseAlias::HTTP_OK,
                'data' => $transformedData,
                'customers' => $shops,
                'board' => [
                    'id' => $findBoard->id,
                    'child_domain_name' => $transformedData[0]['child_domain_name'] ?? null,
                    'created_by_id' => $findBoard->created_by_id,
                    'approved_by_id' => $findBoard->approved_by_id,
                    'batch_no' => $findBoard->batch_no,
                    'total' => $findBoard->total,
                    'status' => $findBoard->status,
                    'process' => $findBoard->process,
                    'created_at' => $findBoard->created_at->toDateTimeString(),
                    'generate_date' => $findBoard->generate_date,
                ]
            ], ResponseAlias::HTTP_OK);

        } catch (Exception $e) {
            // Rollback all changes if any exception occurs
            DB::rollBack();

            return response()->json([
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'An error occurred!',
                'error' => $e->getMessage(),
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getCustomerNames(array $data): array
    {
        return collect($data)
            ->pluck('child_warehouse_name')
            ->unique()
            ->values()
            ->toArray();
    }

    private function formatMatrixBoardData(array $data, array $shops): array
    {
        return collect($data)
            ->groupBy('vendor_stock_item_id')
            ->map(function ($group) use ($shops) {
                $base = [
                    'process' => $group->first()['process'],
                    'child_warehouse_name' => $group->first()['child_warehouse_name'],
                    'child_warehouse_id' => $group->first()['child_warehouse_id'],
                    'vendor_stock_item_id' => $group->first()['vendor_stock_item_id'],
                    'customer_stock_item_id' => $group->first()['customer_stock_item_id'],
                    'product' => $group->first()['display_name'],
                    'warehouse_id' => $group->first()['warehouse_id'],
                    'warehouse_name' => DB::table('cor_warehouses')->where('id', $group->first()['warehouse_id'])->first()?->name,
                    'id' => $group->first()['id'],
                    'vendor_stock_quantity' => $group->first()['vendor_stock_quantity'],
                    'total_approved_quantity' => $group->sum('approved_quantity'),
                    'is_production_item' => DB::table('pro_item')->where('item_id', $group->first()['vendor_stock_item_id'])->where('config_id', $this->domain['pro_config'])->where('process', 'approved')->exists(),
                    'child_domain_name' => DB::table('dom_domain')->join('inv_config', 'inv_config.domain_id', '=', 'dom_domain.id')->select('dom_domain.name')->where('inv_config.id', $group->first()['customer_config_id'])->first()?->name,
                ];

                foreach ($shops as $shop) {
                    $base[strtolower(str_replace(' ', '_', $shop))] = 0;
                }

                $totalRequestQuantity = 0;
                foreach ($group as $item) {
                    $customerName = strtolower(str_replace(' ', '_', $item['child_warehouse_name']));
                    $base[$customerName . '_id'] = $item['id'];
                    $base[$customerName . '_approved_quantity'] = $item['approved_quantity'];
                    $base[$customerName . '_requested_quantity'] = $item['requested_quantity'];
                    $base[$customerName] = $item['quantity'];
                    $totalRequestQuantity += $item['requested_quantity'];
                }

                $base['total_request_quantity'] = $totalRequestQuantity;
                $base['remaining_quantity'] = $group->first()['vendor_stock_quantity'] - $base['total_approved_quantity'];

                return $base;
            })
            ->values()
            ->toArray();
    }

    private function groupByCustomerStockItemIdAndConfigId(array $data): Collection
    {
        return collect($data)->groupBy(fn($item) => $item['customer_stock_item_id'] . '-' . $item['customer_config_id'] . '-' . $item['warehouse_id'])
            ->map(function ($group) {
                return [
                    'id' => $group->first()['id'],
                    'child_warehouse_id' => $group->first()['warehouse_id'],
                    'child_warehouse_name' => $group->first()['warehouse_name'],
                    'customer_stock_item_id' => $group->first()['customer_stock_item_id'],
                    'vendor_stock_item_id' => $group->first()['vendor_stock_item_id'],
                    'requisition_item_id' => $group->first()['id'],
                    'customer_config_id' => $group->first()['customer_config_id'],
                    'vendor_config_id' => $group->first()['vendor_config_id'],
                    'quantity' => $group->sum('quantity'),
                    'sub_total' => $group->sum('sub_total'),
                    'purchase_price' => $group->first()['purchase_price'],
                    'sales_price' => $group->first()['sales_price'],
                    'display_name' => $group->first()['display_name'],
                    'barcode' => $group->first()['barcode'],
                    'unit_name' => $group->first()['unit_name'],
                    'unit_id' => $group->first()['unit_id'],
                    'customer_id' => $group->first()['customer_id'],
                    'customer_name' => $group->first()['customer_name'],
                    'customer_mobile' => $group->first()['customer_mobile'],
                    'expected_date' => $group->first()['expected_date'],
                    'remaining_quantity' => $group->first()['remaining_quantity'],
                    'vendor_stock_quantity' => $group->first()['vendor_stock_quantity'],
                ];
            });
    }


    /**
     * @throws Exception
     */
    public function matrixBoardQuantityUpdate(Request $request)
    {
        if (!$request->has('id') || empty($request->id)) {
            throw new Exception("Update id not found");
        }

        $findBoardMatrix = RequisitionMatrixBoardModel::find($request->id);
        if (!$findBoardMatrix) {
            throw new Exception("Board matrix not found");
        }

        $type = $request->type;

        if ($type == 'quantity') {
            $quantity = $request->quantity ?? 0;

            $findBoardMatrix->update([
                'quantity' => $quantity,
                'approved_quantity' => $quantity,
                'sub_total' => $quantity * $findBoardMatrix->purchase_price,
            ]);
        } elseif ($type == 'warehouse') {
            $warehouseId = $request->warehouse_id;
            $stockItemId = $request->stock_item_id;

            $currentStock = CurrentStockModel::getCurrentStockByWarehouseAndStockItemId($this->domain['config_id'], $warehouseId, $stockItemId);
            $findBoardMatrix->update([
                'warehouse_id' => $warehouseId,
                'vendor_stock_quantity' => $currentStock,
            ]);
        }

        return response()->json([
            'status' => ResponseAlias::HTTP_OK,
            'message' => 'Update successfully',
        ], ResponseAlias::HTTP_OK);
    }

    public function matrixBoardBatchGenerate(Request $request, $id)
    {
        DB::beginTransaction(); // Start transaction

        try {
            $findBoard = RequisitionBoardModel::find($id);

            if (!$findBoard) {
                return response()->json([
                    'status' => ResponseAlias::HTTP_NOT_FOUND,
                    'message' => 'Requisition board not found.',
                ], ResponseAlias::HTTP_NOT_FOUND);
            }

            if ($findBoard->status === 'Confirmed') {
                return response()->json([
                    'status' => ResponseAlias::HTTP_CONFLICT,
                    'message' => 'This board has already been processed.',
                ], ResponseAlias::HTTP_CONFLICT);
            }

            $matrixCollection = $findBoard->requisition_matrix;

            if ($matrixCollection->isEmpty()) {
                return response()->json([
                    'status' => ResponseAlias::HTTP_BAD_REQUEST,
                    'message' => 'No matrix data found for the given board.',
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            $matrixCollection = $matrixCollection->where('process', '!=', 'Confirmed');

            if ($matrixCollection->isEmpty()) {
                return response()->json([
                    'status' => ResponseAlias::HTTP_CONFLICT,
                    'message' => 'Matrix data has already been processed.',
                ], ResponseAlias::HTTP_CONFLICT);
            }

            $reqProItemMatrix = $matrixCollection->groupBy('vendor_stock_item_id')
                ->map(function ($items) use ($findBoard) {
                    $proItem = DB::table('pro_item')
                        ->where('item_id', $items->first()['vendor_stock_item_id'])
                        ->where('config_id', $this->domain['pro_config'])
                        ->where('process', 'approved')
                        ->first();

                    if (!$proItem) {
                        return null;
                    }

                    return [
                        'requisition_board_id' => $findBoard->id,
                        'config_id' => $this->domain['pro_config'],
                        'name' => $items->first()['display_name'],
                        'item_id' => $items->first()['vendor_stock_item_id'],
                        'pro_item_id' => $proItem->id,
                        'warehouse_id' => $proItem->warehouse_id,
                        'quantity' => $items->sum('approved_quantity'),
                        'stock_quantity' => $items->first()['vendor_stock_quantity'],
                        'demand_quantity' => $items->sum('approved_quantity'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })
                ->filter()
                ->values()
                ->toArray();

            RequisitionProductItemMatrixModel::insert($reqProItemMatrix);

            $batchInputs = $matrixCollection->groupBy('vendor_config_id')
                ->map(function ($group) {
                    return [
                        'process' => 'New',
                        'config_id' => $group->first()->vendor_config_id,
                        'created_by_id' => $this->domain['user_id'],
                        'sales_by_id' => $this->domain['user_id'],
                        'approved_by_id' => $this->domain['user_id'],
                        'quantity' => $group->sum('quantity'),
                        'sub_total' => $group->sum('sub_total'),
                        'total' => $group->sum('sub_total'),
                        'invoice_date' => now(),
                    ];
                })->values()->first();

            $findBoard->update([
                'total' => $batchInputs['total'],
                'approved_by_id' => $this->domain['user_id']
            ]);

            $batch = InvoiceBatchModel::create($batchInputs);

            $groupedItems = $matrixCollection->groupBy('vendor_stock_item_id')
                ->map(function ($items) use ($batch) {
                    return [
                        'invoice_batch_id' => $batch->id,
                        'quantity' => $items->sum('quantity'),
                        'sales_price' => $items->first()['sales_price'],
                        'purchase_price' => $items->first()['purchase_price'],
                        'price' => $items->first()['purchase_price'],
                        'sub_total' => $items->sum('sub_total'),
                        'stock_item_id' => $items->first()['vendor_stock_item_id'],
                        'uom' => $items->first()['unit_name'],
                        'name' => $items->first()['display_name'],
                        'warehouse_id' => $items->first()['warehouse_id'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->values()->toArray();

            InvoiceBatchItemModel::insert($groupedItems);

            $groupedSales = $this->groupSalesByCustomer($matrixCollection->toArray(), $batch);

            foreach ($groupedSales as $sale) {
                $sale['sales_form'] = 'requisition';
                $sale['expected_date'] = $findBoard->generate_date;
                $sales = SalesModel::create($sale);
                $salesItems = array_map(fn($item) => array_merge($item, ['sale_id' => $sales->id]), $sale['items']);
                SalesItemModel::insert($salesItems);
            }
            RequisitionMatrixBoardModel::whereIn('id', $matrixCollection->pluck('id')->toArray())
                ->update(['process' => 'Confirmed']);

            RequisitionModel::where([
                ['matrix_generate_date', $findBoard->expected_date],
                ['vendor_config_id', $findBoard->config_id],
                ['process', 'Generated']
            ])->update(['process' => 'Confirmed']);

            $findBoard->update(['process' => 'Confirmed', 'production_process' => 'Created']);

            DB::commit();

            return response()->json([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'Matrix data processed successfully.',
                'pro_item_process' => count($reqProItemMatrix) ?? 0
            ], ResponseAlias::HTTP_OK);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Matrix Batch Generation Error: " . $e->getMessage());

            return response()->json([
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'An error occurred while generating the batch.',
                'error' => $e->getMessage(),
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function groupSalesByCustomer(array $salesData, $batch)
    {
        $groupedSales = [];

        foreach ($salesData as $item) {
            $customerId = $item['child_warehouse_id'];

            if (!isset($groupedSales[$customerId])) {
                $groupedSales[$customerId] = [
                    'customer_id' => $item['customer_id'],
                    'config_id' => $item['vendor_config_id'],
                    'invoice_batch_id' => $batch->id,
                    'created_by_id' => $this->domain['user_id'],
                    'sales_by_id' => $this->domain['user_id'],
                    'sub_total' => 0,
                    'total' => 0,
                    'items' => []
                ];
            }

            $groupedSales[$customerId]['items'][] = [
                'sale_id' => '',
                'uom' => $item['unit_name'],
                'name' => $item['display_name'],
                'quantity' => $item['quantity'],
                'sales_price' => $item['sales_price'],
                'purchase_price' => $item['purchase_price'],
                'price' => $item['purchase_price'],
                'sub_total' => $item['sub_total'],
                'stock_item_id' => $item['vendor_stock_item_id'],
                'warehouse_id' => $item['warehouse_id'],
                'requisition_item_id' => $item['requisition_item_id'],
                'config_id' => $this->domain['config_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $groupedSales[$customerId]['sub_total'] += $item['sub_total'];
            $groupedSales[$customerId]['total'] += $item['sub_total'];
        }

        return array_values($groupedSales);
    }

    public function boardWiseProduction($id)
    {
        $findBoard = RequisitionBoardModel::find($id);

        if (!$findBoard) {
            return response()->json([
                'status' => ResponseAlias::HTTP_NOT_FOUND,
                'message' => 'Board not found.',
                'data' => null
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        $productionItems = $findBoard->requisition_matrix_production->map(function ($item) {
            return [
                'id' => $item->id,
                'pro_item_id' => $item->pro_item_id,
                'pro_batch_item_id' => $item->pro_batch_item_id,
                'pro_batch_id' => $item->pro_batch_id,
                'product_name' => $item->name ?? null,
                'approved_by_id' => $item->approved_by_id,
                'process' => $item->process,
                'approved_date' => $item->approved_date,
                'quantity' => $item->quantity,
                'stock_quantity' => $item->stock_quantity,
                'demand_quantity' => $item->demand_quantity,
                'warehouse_id' => $item->warehouse_id,
                'warehouse_name' => $item->warehouse->name,
                'created_at' => $item->created_at ? $item->created_at->format('d-m-Y') : null,
            ];
        })->toArray();

        return response()->json([
            'status' => ResponseAlias::HTTP_OK,
            'message' => 'Production items data found.',
            'data' => [
                'id' => $findBoard->id,
                'config_id' => $findBoard->config_id,
                'batch_no' => $findBoard->batch_no,
                'total' => $findBoard->total,
                'process' => $findBoard->process,
                'board_status' => $findBoard->status,
                'production_approved_by_id' => $findBoard->production_approved_by_id,
                'production_process' => $findBoard->production_process,
                'production_approved_date' => $findBoard->production_approved_date ? $findBoard->production_approved_date->format('d-m-Y') : null,
                'created_at' => $findBoard->created_at ? $findBoard->created_at->format('d-m-Y') : null,
                'production_items' => $productionItems
            ]
        ], ResponseAlias::HTTP_OK);
    }

    public function matrixBoardProductionQuantityUpdate(Request $request)
    {
        if (!$request->has('id') || empty($request->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Update ID not provided.'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $quantity = $request->quantity ?? 0;

        try {
            DB::beginTransaction();

            $findBoardProductionItem = RequisitionProductItemMatrixModel::find($request->id);
            if (!$findBoardProductionItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Board matrix item not found.'
                ], ResponseAlias::HTTP_NOT_FOUND);
            }

            $findBoardProductionItem->update([
                'demand_quantity' => $quantity,
            ]);

            if ($findBoardProductionItem->pro_batch_item_id && $findBoardProductionItem->pro_batch_id) {
                $productionBatchItem = ProductionBatchItemModel::find($findBoardProductionItem->pro_batch_item_id);
                if (!$productionBatchItem) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Production batch item not found.'
                    ], ResponseAlias::HTTP_NOT_FOUND);
                }

                $productionBatchItem->update([
                    'issue_quantity' => $quantity
                ]);

                ProductionExpense::handleProductionExpenseBatchItemIdWise($productionBatchItem, $productionBatchItem->config_id);
            }

            DB::commit();

            return response()->json([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'Update successful.'
            ], ResponseAlias::HTTP_OK);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during the update process.'
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function matrixBoardProductionProcess(Request $request, GeneratePatternCodeService $patternCodeService)
    {
        $itemIds = $request->input('item_ids', []);
        if (empty($itemIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No items provided.',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
        // If it's a string that looks like a JSON array: "[31,32,33]" or "[]"
        if (is_string($itemIds)) {
            $decoded = json_decode($itemIds, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $itemIds = $decoded;
            } else {
                // fallback: maybe comma-separated "31,32,33"
                $itemIds = array_filter(array_map('intval', explode(',', $itemIds)));
            }
        }
        // Ensure it's always an array of integers
        $itemIds = array_map('intval', (array)$itemIds);
        try {
            DB::beginTransaction();
            $productionMatrixItems = RequisitionProductItemMatrixModel::whereIn('id', $itemIds)->get();
            if ($productionMatrixItems->isEmpty()) {
                throw new Exception('No valid production matrix items found.');
            }
            $firstItem = $productionMatrixItems->first();
            $pattern = $patternCodeService->productBatch([
                'config' => $firstItem->config_id,
                'table' => 'pro_batch',
                'prefix' => 'PB-',
            ]);
            $productionBatch = ProductionBatchModel::create([
                'config_id' => $firstItem->config_id,
                'warehouse_id' => $firstItem->warehouse_id,
                'code' => $pattern['code'],
                'invoice' => $pattern['generateId'],
                'process' => 'Draft',
                'issue_date' => now(),
                'receive_date' => now(),
                'is_requisition' => 1,
                'requisition_board_id' => $firstItem->requisition_board_id,
                'created_by_id' => $this->domain['user_id'],
                'mode' => 'Requisition',
                'status' => 1,
            ]);

            foreach ($productionMatrixItems as $findProductionMatrixItem) {
                $productionBatchItem = ProductionBatchItemModel::create([
                    'config_id' => $findProductionMatrixItem->config_id,
                    'production_item_id' => $findProductionMatrixItem->pro_item_id,
                    'issue_quantity' => $findProductionMatrixItem->demand_quantity,
                    'receive_quantity' => $findProductionMatrixItem->demand_quantity,
                    'batch_id' => $productionBatch->id,
                    'warehouse_id' => $findProductionMatrixItem->warehouse_id,
                ]);

                ProductionExpense::handleProductionExpenseBatchItemIdWise($productionBatchItem, $findProductionMatrixItem->config_id);

                $findProductionMatrixItem->update([
                    'pro_batch_item_id' => $productionBatchItem->id,
                    'pro_batch_id' => $productionBatch->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'Data successfully processed.',
            ], ResponseAlias::HTTP_OK);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to process data: ' . $e->getMessage(),
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function matrixBoardProductionApproved(Request $request, $id)
    {
        $findProductionBatch = ProductionBatchModel::find($id);
        if (!$findProductionBatch) {
            return response()->json([
                'success' => false,
                'message' => 'Production batch not found.',
            ], ResponseAlias::HTTP_NOT_FOUND);
        }


        $findProductionMatrixBoardItems = RequisitionProductItemMatrixModel::where('pro_batch_id', $id)->get();
        if ($findProductionMatrixBoardItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No production matrix items found for this batch.',
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();

        try {
            $findProductionBatch->update([
                'process' => 'Created',
                'is_requisition' => 1,
            ]);

            foreach ($findProductionMatrixBoardItems as $item) {
                $item->update([
                    'approved_by_id' => $this->domain['user_id'],
                    'process' => 'Approved',
                    'approved_date' => now()
                ]);
            }

            // realtime raw material stock item average price calculate with pro_element , pro_item , pro_expense , pro_batch_items,
            $materialService = app(\App\Services\Production\ProductionMaterialService::class);
            $materialService->updateFullMaterialProcess($id);

            ProductionBatchModel::generateProductionToVendorRequisition($this->domain->toArray(), $id, $findProductionBatch);

            DB::commit();

            return response()->json([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'Approved successfully processed.',
            ], ResponseAlias::HTTP_OK);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during the approval process.',
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
