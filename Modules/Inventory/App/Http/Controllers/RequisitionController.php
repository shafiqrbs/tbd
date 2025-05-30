<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Inventory\App\Entities\RequisitionMatrixBoard;
use Modules\Inventory\App\Http\Requests\PurchaseRequest;
use Modules\Inventory\App\Http\Requests\RequisitionRequest;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\InvoiceBatchItemModel;
use Modules\Inventory\App\Models\InvoiceBatchModel;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\RequisitionItemModel;
use Modules\Inventory\App\Models\RequisitionMatrixBoardModel;
use Modules\Inventory\App\Models\RequisitionModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class RequisitionController extends Controller
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

    public function index(Request $request)
    {
        $data = RequisitionModel::getRecords($request, $this->domain);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RequisitionRequest $request)
    {
        DB::beginTransaction();

        try {
            $input = $request->validated();
            $input['config_id'] = $this->domain['config_id'];
            $input['status'] = true;

            $findVendor = VendorModel::find($input['vendor_id']);
            if (!$findVendor) {
                throw new \Exception("Vendor not found");
            }

            if ($findVendor->customer_id) {
                $input['customer_id'] = $findVendor->customer_id;
                $input['customer_config_id'] = $this->domain['config_id'];
                $input['vendor_config_id'] = ConfigModel::where('domain_id', $findVendor->sub_domain_id)
                    ->first()
                    ->id;
            }

            $requisition = RequisitionModel::create($input);

            if (!empty($input['items'])) {
                $itemsToInsert = [];
                $total = 0;

                foreach ($input['items'] as $val) {
                    $customerStockItem = StockItemModel::find($val['product_id']);
                    if (!$customerStockItem) {
                        throw new \Exception("Stock item not found");
                    }

                    $findProduct = ProductModel::find($customerStockItem->product_id);
                    if (!$findProduct) {
                        throw new \Exception("Product not found");
                    }

                    $total += $val['sub_total'];
                    $itemsToInsert[] = [
                        'requisition_id' => $requisition->id,
                        'customer_stock_item_id' => $val['product_id'],
                        'vendor_stock_item_id' => $customerStockItem->parent_stock_item,
                        'vendor_config_id' => $requisition->vendor_config_id,
                        'customer_config_id' => $requisition->customer_config_id,
                        'barcode' => $customerStockItem->barcode,
                        'quantity' => $val['quantity'],
                        'display_name' => $val['display_name'],
                        'purchase_price' => $val['purchase_price'],
                        'sales_price' => $val['sales_price'],
                        'sub_total' => $val['sub_total'],
                        'warehouse_id' => $val['warehouse_id'] ?? null,
                        'unit_id' => $findProduct->unit_id,
                        'unit_name' => $customerStockItem->uom,
                        'created_at' => now(),
                    ];
                }

                if (!empty($itemsToInsert)) {
                    RequisitionItemModel::insert($itemsToInsert);
                }
            }

            $requisition->update(['sub_total' => $total, 'total' => $total]);

            DB::commit();

            return response()->json([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'Insert successfully',
                'data' => $requisition,
            ], ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Transaction failed: ' . $e->getMessage(),
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = RequisitionModel::getShow($id, $this->domain);

        if (!$entity) {
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function edit($id)
    {
        $service = new JsonRequestResponse();
        $entity = PurchaseModel::getEditData($id, $this->domain);
        if (!$entity) {
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    public function update(RequisitionRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $input = $request->validated();
            $input['config_id'] = $this->domain['config_id'];
            $input['status'] = true;

            // Find requisition
            $findRequisition = RequisitionModel::findOrFail($id);

            // Validate vendor
            $findVendor = VendorModel::find($input['vendor_id']);
            if (!$findVendor) {
                throw new \Exception("Vendor not found");
            }

            // Update requisition
            $findRequisition->update($input);

            if (!empty($input['items'])) {
                $itemsToInsert = [];
                $total = 0;

                // Bulk delete old items
                $findRequisition->requisitionItems()->delete();

                // Collect stock items in bulk instead of multiple queries inside loop
                $stockItemIds = collect($input['items'])->pluck('product_id')->toArray();
                $customerStockItems = StockItemModel::whereIn('id', $stockItemIds)->get()->keyBy('id');

                $productIds = $customerStockItems->pluck('product_id')->toArray();
                $products = ProductModel::whereIn('id', $productIds)->get()->keyBy('id');

                foreach ($input['items'] as $val) {
                    $customerStockItem = $customerStockItems[$val['product_id']] ?? null;
                    if (!$customerStockItem) {
                        throw new \Exception("Stock item not found");
                    }

                    $findProduct = $products[$customerStockItem->product_id] ?? null;
                    if (!$findProduct) {
                        throw new \Exception("Product not found");
                    }

                    $total += $val['sub_total'];

                    $itemsToInsert[] = [
                        'requisition_id' => $id,
                        'customer_stock_item_id' => $val['product_id'],
                        'vendor_stock_item_id' => $customerStockItem->parent_stock_item,
                        'vendor_config_id' => $findRequisition->vendor_config_id,
                        'customer_config_id' => $findRequisition->customer_config_id,
                        'barcode' => $customerStockItem->barcode,
                        'quantity' => $val['quantity'],
                        'display_name' => $val['display_name'],
                        'purchase_price' => $val['purchase_price'],
                        'sales_price' => $val['sales_price'],
                        'sub_total' => $val['sub_total'],
                        'unit_id' => $findProduct->unit_id,
                        'unit_name' => $customerStockItem->uom,
                        'created_at' => now(),
                    ];
                }

                // Insert new items in bulk
                if (!empty($itemsToInsert)) {
                    RequisitionItemModel::insert($itemsToInsert);
                }
            }

            // Update requisition totals
            $findRequisition->update(['sub_total' => $total, 'total' => $total]);

            DB::commit();

            return response()->json([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'Update successfully',
                'data' => $findRequisition,
            ], ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Transaction failed: ' . $e->getMessage(),
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        RequisitionModel::find($id)->delete();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }

    /**
     * Approve the specified resource from storage.
     */
    public function approve($id)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        // Start the database transaction
        DB::beginTransaction();

        try {
            $purchase = PurchaseModel::find($id);
            $purchase->update(['approved_by_id' => $this->domain['user_id']]);
            if (sizeof($purchase->purchaseItems)>0){
                foreach ($purchase->purchaseItems as $item){
                    // get average price
                    $itemAveragePrice = StockItemModel::calculateStockItemAveragePrice($item->stock_item_id,$item->config_id,$item);
                    //set average price
                    StockItemModel::where('id', $item->stock_item_id)->where('config_id',$item->config_id)->update(['average_price' => $itemAveragePrice]);

                    $item->update(['approved_by_id' => $this->domain['user_id']]);
                    StockItemHistoryModel::openingStockQuantity($item,'purchase',$this->domain);
                }
            }
            // Commit the transaction after all updates are successful
            DB::commit();

            $response->setContent(json_encode([
                'status' => Response::HTTP_OK,
                'message' => 'Approved successfully',
            ]));
            $response->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            $response->setContent(json_encode([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ]));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }


    /*public function matrixBoard(Request $request)
    {
        $expectedDate = $request->query('expected_date');
        $vendorConfigId = $this->domain['config_id'];

        $getItems = RequisitionItemModel::where([
            ['inv_requisition_item.vendor_config_id', $vendorConfigId],
            ['inv_requisition.expected_date','<=', $expectedDate]
        ])
            ->whereIn('inv_requisition.process',['New','Generated'])
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

        foreach ($getItems as $item){
            $findRequisition = RequisitionModel::find($item['requisition_id']);
            $findRequisition->update(['process' => 'Generated','matrix_generate_date'=>$expectedDate]);
        }

        $groupedItems = $this->groupByCustomerStockItemIdAndConfigId($getItems);

        $matrixExists = true;

        if (!empty($groupedItems)) {
            $itemsToInsert = [];

            foreach ($groupedItems as $val) {
                // Check if a record already exists
                $exists = RequisitionMatrixBoardModel::where([
                    ['generate_date', $expectedDate],
                    ['process','Confirmed'],
                    ['vendor_config_id', $val['vendor_config_id']]
                ])->exists();

                if (!$exists) {
                    $matrixExists = false;
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
                        'process' => 'Generated',
                        'created_at' => now(),
                    ];
                }
            }

            if (!empty($itemsToInsert)) {
                $generateMatrixExists = RequisitionMatrixBoardModel::where([
                    ['generate_date', $expectedDate],
                    ['process','Generated'],
                    ['vendor_config_id', $val['vendor_config_id']]
                ])->get();

                foreach ($generateMatrixExists as $generateMatrixExist) {
                    $findMatrix = RequisitionMatrixBoardModel::find($generateMatrixExist['id']);
                    $findMatrix->delete();
                }
                RequisitionMatrixBoardModel::insert($itemsToInsert);
            }
        }

        // Fetch the latest data after insertion
        $getItemWiseProduct = RequisitionMatrixBoardModel::where([
//            ['process', 'Generated'],
            ['vendor_config_id', $vendorConfigId],
            ['generate_date', $expectedDate]
        ])->get()->toArray();

        $shops = $this->getCustomerNames($getItemWiseProduct);

        $transformedData = $this->formatMatrixBoardData($getItemWiseProduct, $shops);

        return response()->json([
            'status' => ResponseAlias::HTTP_OK,
            'message' => $matrixExists?'Already generated':'Insert successfully',
            'data' => $transformedData,
            'customers' => $shops,
        ], ResponseAlias::HTTP_OK);
    }*/


    public function matrixBoard(Request $request)
    {
        DB::beginTransaction(); // Start Database Transaction

        try {
            $expectedDate = $request->query('expected_date');
            $vendorConfigId = $this->domain['config_id'];

            // Fetch Requisition Items
            $getItems = RequisitionItemModel::where([
                ['inv_requisition_item.vendor_config_id', $vendorConfigId],
                ['inv_requisition.expected_date','<=', $expectedDate]
            ])
                ->whereIn('inv_requisition.process', ['New', 'Generated'])
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
            RequisitionModel::whereIn('id', $requisitionIds)
                ->update(['process' => 'Generated', 'matrix_generate_date' => $expectedDate]);

            $groupedItems = $this->groupByCustomerStockItemIdAndConfigId($getItems);
            $matrixExists = true;

            if (!empty($groupedItems)) {
                $itemsToInsert = [];

                foreach ($groupedItems as $val) {
                        // Define the criteria to check whether the record exists or should be updated
                        $criteria = [
                            'generate_date' => $expectedDate,
                            'vendor_config_id' => $val['vendor_config_id'],
                            'customer_stock_item_id' => $val['customer_stock_item_id'], // Add unique keys to avoid duplicate constraints
                            'vendor_stock_item_id' => $val['vendor_stock_item_id']
                        ];

                        // Data to be updated or created
                        $data = [
                            'customer_config_id' => $val['customer_config_id'],
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
                            'vendor_stock_quantity' => $val['vendor_stock_quantity'],
                            'status' => true,
                            'process' => 'Generated',
                            'created_at' => now(), // Optional: use updated_at if tracking updates
                        ];

                        // Create or update the record
                        RequisitionMatrixBoardModel::updateOrCreate($criteria, $data);

                    /*// Check if a Generated record already exists for this vendor and expected date
                    $exists = RequisitionMatrixBoardModel::where([
                        ['generate_date', $expectedDate],
                        ['process', 'Confirmed'],
                        ['vendor_config_id', $val['vendor_config_id']]
                    ])->exists();

                    if (!$exists) {
                        $matrixExists = false;
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
                            'process' => 'Generated',
                            'created_at' => now(),
                        ];
                    }*/
                }

                /*if (!empty($itemsToInsert)) {
                    // Delete previously generated records before inserting new ones
                    RequisitionMatrixBoardModel::where([
                        ['generate_date', $expectedDate],
                        ['process', 'Generated'],
                        ['vendor_config_id', $val['vendor_config_id']]
                    ])->delete();

                    // Insert new records
                    RequisitionMatrixBoardModel::insert($itemsToInsert);
                }*/
            }

            // Commit the transaction
            DB::commit();

            // Fetch the latest data after insertion
            $getItemWiseProduct = RequisitionMatrixBoardModel::where([
                ['vendor_config_id', $vendorConfigId],
                ['generate_date', $expectedDate]
            ])->get()->toArray();

            $shops = $this->getCustomerNames($getItemWiseProduct);
            $transformedData = $this->formatMatrixBoardData($getItemWiseProduct, $shops);

            return response()->json([
                'status' => ResponseAlias::HTTP_OK,
                'message' => $matrixExists ? 'Already generated' : 'Insert successfully',
                'data' => $transformedData,
                'customers' => $shops,
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
        if (!$request->has('quantity') || empty($request->quantity)) {
            throw new \Exception("Quantity not found");
        }

        if (!$request->has('id') || empty($request->id)) {
            throw new \Exception("Update id not found");
        }

        $findBoardMatrix = RequisitionMatrixBoardModel::find($request->id);
        if (!$findBoardMatrix) {
            throw new \Exception("Board matrix not found");
        }

        $findBoardMatrix->update([
            'quantity' => $request->quantity,
            'approved_quantity' => $request->quantity,
            'sub_total' => $request->quantity*$findBoardMatrix->purchase_price,
        ]);

        return response()->json([
            'status' => ResponseAlias::HTTP_OK,
            'message' => 'Update successfully',
        ], ResponseAlias::HTTP_OK);
    }


    /**
     * @throws \Exception
     */
    public function matrixBoardBatchGenerate(Request $request)
    {
        // Validate input
        if (!$request->has('expected_date') || empty($request->expected_date)) {
            throw new \Exception("Expected date not found");
        }

        $vendorConfigId = $request->config_id ?? $this->domain['config_id'];

        DB::beginTransaction(); // Start transaction

        try {
            // Fetch the latest data
            $getMatrixs = RequisitionMatrixBoardModel::where([
                ['vendor_config_id', $vendorConfigId],
                ['generate_date', $request->expected_date],
                ['process', 'Generated']
            ])->get();

            if ($getMatrixs->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'status' => ResponseAlias::HTTP_BAD_REQUEST,
                    'message' => 'No data found for the given criteria',
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            // Prepare batch data
            $inputs =  $getMatrixs->groupBy('vendor_config_id')
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
                })
                ->values()
                ->first(); // Fetch first batch

            // Insert invoice batch
            $batch = InvoiceBatchModel::create($inputs);

            // Grouping items by customer_stock_item_id
            $groupedItems = $getMatrixs->groupBy('vendor_stock_item_id')->map(function ($items) use ($batch) {
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
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->values()->toArray();

            // Bulk insert grouped data
            InvoiceBatchItemModel::insert($groupedItems);


            // Group sales data by customer
            $groupedSales = $this->groupSalesByCustomer($getMatrixs->toArray(), $batch);

            foreach ($groupedSales as $sale) {
                $sale['sales_form'] = 'requisition';
                $sales = SalesModel::create($sale);

                $salesItems = array_map(fn ($item) => array_merge($item, ['sale_id' => $sales->id]), $sale['items']);

                SalesItemModel::insert($salesItems); // Bulk insert sales items
            }

            // Update process status of requisition matrix records
            RequisitionMatrixBoardModel::whereIn('id', $getMatrixs->pluck('id'))->update(['process' => 'Confirmed']);

            //update requisition process
            RequisitionModel::where([
                ['matrix_generate_date', $request->expected_date],
                ['vendor_config_id', $vendorConfigId],
                ['process', 'Generated']
            ])->update(['process' => 'Confirmed']);

            DB::commit(); // Commit transaction

            return response()->json([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'Matrix data process successfully',
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
