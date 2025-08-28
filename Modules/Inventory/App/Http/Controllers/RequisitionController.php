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
use Throwable;

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
     * @throws \Throwable
     */
    public function approve(int $id): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();

        try {
            $requisition = RequisitionModel::findOrFail($id);

            $requisition->update([
                'approved_by_id' => $this->domain['user_id'] ?? null,
                'process'        => 'Approved',
                'approved_date'  => now(),
            ]);

            DB::commit();

            return response()->json([
                'status'  => ResponseAlias::HTTP_OK,
                'message' => 'Approved successfully.',
            ], ResponseAlias::HTTP_OK);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to approve the requisition.',
                'error'   => $e->getMessage(),
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
