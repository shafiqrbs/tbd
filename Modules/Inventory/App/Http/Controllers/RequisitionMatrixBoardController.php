<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Models\InvoiceBatchItemModel;
use Modules\Inventory\App\Models\InvoiceBatchModel;
use Modules\Inventory\App\Models\RequisitionBoardModel;
use Modules\Inventory\App\Models\RequisitionItemModel;
use Modules\Inventory\App\Models\RequisitionMatrixBoardModel;
use Modules\Inventory\App\Models\RequisitionModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class RequisitionMatrixBoardController extends Controller
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

        // Validate input
        if (!$expectedDate || empty($expectedDate)) {
            throw new \Exception("Expected date not found");
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

            $board = RequisitionBoardModel::create([
                'config_id' => $vendorConfigId,
                'created_by_id' => $this->domain['user_id'],
                'batch_no' => $request->batch_no,
                'total' => 0,
                'status' => 1,
                'process' => 'Created',
                'generate_date' => $expectedDate,
            ]);

            // Fetch Requisition Items
            $getItems = RequisitionItemModel::where([
                ['inv_requisition_item.vendor_config_id', $vendorConfigId],
                ['inv_requisition.expected_date','<=', $expectedDate]
            ])
                ->whereIn('inv_requisition.process', ['Approved', 'Generated'])
                ->select([
                    'inv_requisition_item.id',
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
                ->join('inv_requisition', 'inv_requisition.id', '=', 'inv_requisition_item.requisition_id')
                ->join('inv_stock as vendor_stock_item', 'vendor_stock_item.id', '=', 'inv_requisition_item.vendor_stock_item_id')
                ->join('cor_customers', 'cor_customers.id', '=', 'inv_requisition.customer_id')
                ->get()
                ->toArray();

            // Update process for fetched requisition items
            $requisitionIds = array_unique(array_column($getItems, 'requisition_id'));
            RequisitionModel::whereIn('id', $requisitionIds)->update(['process' => 'Generated', 'matrix_generate_date' => $expectedDate]);

            $groupedItems = $this->groupByCustomerStockItemIdAndConfigId($getItems);

            if (!empty($groupedItems)) {
                $itemsToInsert = [];

                foreach ($groupedItems as $val) {
                    $itemsToInsert[] = [
                        'customer_stock_item_id' => $val['customer_stock_item_id'],
                        'vendor_stock_item_id' => $val['vendor_stock_item_id'],
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
                        'vendor_stock_quantity' => $val['vendor_stock_quantity'],
                        'status' => true,
                        'process' => 'Created',
                        'requisition_board_id' => $board->id,
                        'created_at' => now(),
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

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback if any error
            Log::error("Matrix Batch Generation Error: " . $e->getMessage());

            return response()->json([
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'An error occurred while generating the batch',
                'error' => $e->getMessage(),
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function matrixBoard(Request $request , $id)
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

        } catch (\Exception $e) {
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
            ->pluck('customer_name')
            ->unique()
            ->values()
            ->toArray();
    }

    private function formatMatrixBoardData(array $data, array $shops): array
    {
        return collect($data)
            ->groupBy('display_name')
            ->map(function ($group) use ($shops) {
                $base = [
                    'process' => $group->first()['process'],
                    'vendor_stock_item_id' => $group->first()['vendor_stock_item_id'],
                    'customer_stock_item_id' => $group->first()['customer_stock_item_id'],
                    'product' => $group->first()['display_name'],
                    'vendor_stock_quantity' => $group->first()['vendor_stock_quantity'],
                    'total_approved_quantity' => $group->sum('approved_quantity'),
                ];

                foreach ($shops as $shop) {
                    $base[strtolower(str_replace(' ', '_', $shop))] = 0;
                }

                $totalRequestQuantity = 0;
                foreach ($group as $item) {
                    $customerName = strtolower(str_replace(' ', '_', $item['customer_name']));
                    $base[$customerName.'_id'] = $item['id'];
                    $base[$customerName.'_approved_quantity'] = $item['approved_quantity'];
                    $base[$customerName.'_requested_quantity'] = $item['requested_quantity'];
                    $base[$customerName] = $item['quantity'];
                    $totalRequestQuantity+=$item['requested_quantity'];
                }

                $base['total_request_quantity'] = $totalRequestQuantity;
                $base['remaining_quantity'] = $group->first()['vendor_stock_quantity']-$base['total_approved_quantity'];

                return $base;
            })
            ->values()
            ->toArray();
    }

    private function groupByCustomerStockItemIdAndConfigId(array $data): Collection
    {
        return collect($data)->groupBy(fn($item) => $item['customer_stock_item_id'] . '-' . $item['customer_config_id'])
            ->map(function ($group) {
                return [
                    'id' => $group->first()['id'],
                    'customer_stock_item_id' => $group->first()['customer_stock_item_id'],
                    'vendor_stock_item_id' => $group->first()['vendor_stock_item_id'],
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
     * @throws \Exception
     */
    public function matrixBoardQuantityUpdate(Request $request)
    {
        $quantity = $request->quantity ?? 0;

        if (!$request->has('id') || empty($request->id)) {
            throw new \Exception("Update id not found");
        }

        $findBoardMatrix = RequisitionMatrixBoardModel::find($request->id);
        if (!$findBoardMatrix) {
            throw new \Exception("Board matrix not found");
        }

        $findBoardMatrix->update([
            'quantity' => $quantity,
            'approved_quantity' => $quantity,
            'sub_total' => $quantity*$findBoardMatrix->purchase_price,
        ]);

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

            // 🔒 Check if board exists
            if (!$findBoard) {
                return response()->json([
                    'status' => ResponseAlias::HTTP_NOT_FOUND,
                    'message' => 'Requisition board not found.',
                ], ResponseAlias::HTTP_NOT_FOUND);
            }

            // 🔒 Check if this board is already processed
            if ($findBoard->status === 'Confirmed') {
                return response()->json([
                    'status' => ResponseAlias::HTTP_CONFLICT,
                    'message' => 'This board has already been processed.',
                ], ResponseAlias::HTTP_CONFLICT);
            }

            // Fetch the related matrix records
            $matrixCollection = $findBoard->requisition_matrix;

            if ($matrixCollection->isEmpty()) {
                return response()->json([
                    'status' => ResponseAlias::HTTP_BAD_REQUEST,
                    'message' => 'No matrix data found for the given board.',
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            // 🔄 Filter unprocessed rows only (if needed)
            $matrixCollection = $matrixCollection->where('process', '!=', 'Confirmed');

            if ($matrixCollection->isEmpty()) {
                return response()->json([
                    'status' => ResponseAlias::HTTP_CONFLICT,
                    'message' => 'Matrix data has already been processed.',
                ], ResponseAlias::HTTP_CONFLICT);
            }

            // 🛠️ Group matrix to create batch input
            $batchInputs = $matrixCollection->groupBy('vendor_config_id')
                ->map(function ($group) {
                    return [
                        'process'         => 'New',
                        'config_id'       => $group->first()->vendor_config_id,
                        'created_by_id'   => $this->domain['user_id'],
                        'sales_by_id'     => $this->domain['user_id'],
                        'approved_by_id'  => $this->domain['user_id'],
                        'quantity'        => $group->sum('quantity'),
                        'sub_total'       => $group->sum('sub_total'),
                        'total'           => $group->sum('sub_total'),
                        'invoice_date'    => now(),
                    ];
                })->values()->first(); // 🚫 NOTE: Only the first invoice batch group will be used

            $batch = InvoiceBatchModel::create($batchInputs);

            // 🌐 Group by vendor_stock_item_id → for batch items
            $groupedItems = $matrixCollection->groupBy('vendor_stock_item_id')
                ->map(function ($items) use ($batch) {
                    return [
                        'invoice_batch_id' => $batch->id,
                        'quantity'         => $items->sum('quantity'),
                        'sales_price'      => $items->first()['sales_price'],
                        'purchase_price'   => $items->first()['purchase_price'],
                        'price'            => $items->first()['purchase_price'],
                        'sub_total'        => $items->sum('sub_total'),
                        'stock_item_id'    => $items->first()['vendor_stock_item_id'],
                        'uom'              => $items->first()['unit_name'],
                        'name'             => $items->first()['display_name'],
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ];
                })->values()->toArray();

            InvoiceBatchItemModel::insert($groupedItems);

            // 🧾 Group sales by customer
            $groupedSales = $this->groupSalesByCustomer($matrixCollection->toArray(), $batch);

            foreach ($groupedSales as $sale) {
                $sale['sales_form'] = 'requisition';
                $sales = SalesModel::create($sale);

                $salesItems = array_map(fn($item) => array_merge($item, ['sale_id' => $sales->id]), $sale['items']);
                SalesItemModel::insert($salesItems);
            }

            // ✅ Matrix rows → Confirmed
            RequisitionMatrixBoardModel::whereIn('id', $matrixCollection->pluck('id')->toArray())
                ->update(['process' => 'Confirmed']);

            // ✅ Parent requisition update
            RequisitionModel::where([
                ['matrix_generate_date', $findBoard->expected_date],
                ['vendor_config_id', $findBoard->config_id],
                ['process', 'Generated']
            ])->update(['process' => 'Confirmed']);

            // ✅ Optionally, update board status itself to prevent duplicates
            $findBoard->update(['process' => 'Confirmed']);

            DB::commit(); // All done

            return response()->json([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'Matrix data processed successfully.',
            ], ResponseAlias::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Matrix Batch Generation Error: " . $e->getMessage());

            return response()->json([
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'An error occurred while generating the batch.',
                'error' => $e->getMessage(),
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function groupSalesByCustomer(array $salesData,$batch)
    {
        $groupedSales = [];

        foreach ($salesData as $item) {
            $customerId = $item['customer_id'];

            if (!isset($groupedSales[$customerId])) {
                $groupedSales[$customerId] = [
                    'customer_id' => $customerId,
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
                'config_id' => $this->domain['config_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $groupedSales[$customerId]['sub_total'] += $item['sub_total'];
            $groupedSales[$customerId]['total'] += $item['sub_total'];
        }

        return array_values($groupedSales);
    }


}
