<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Inventory\App\Http\Requests\PurchaseRequest;
use Modules\Inventory\App\Http\Requests\RequisitionRequest;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\RequisitionItemModel;
use Modules\Inventory\App\Models\RequisitionModel;
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

            $input['customer_id'] = $findVendor->customer_id;
            $input['customer_config_id'] = $this->domain['config_id'];
            $input['vendor_config_id'] = ConfigModel::where('domain_id', $findVendor->sub_domain_id)
                ->first()
                ->id;

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
        $entity = PurchaseModel::getShow($id, $this->domain);
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

    /**
     * Update the specified resource in storage.
     */
    public function update(PurchaseRequest $request, $id)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $getPurchase = PurchaseModel::findOrFail($id);
            $data['remark']=$request->narration;
            $data['due'] = ($data['total'] ?? 0) - ($data['payment'] ?? 0);
            $getPurchase->fill($data);
            $getPurchase->save();

            PurchaseItemModel::class::where('purchase_id', $id)->delete();
            if (sizeof($data['items'])>0){
                foreach ($data['items'] as $item){
                    $item['stock_item_id'] = $item['product_id'];
                    $item['config_id'] = $getPurchase->config_id;
                    $item['purchase_id'] = $id;
                    $item['quantity'] = $item['quantity'] ?? 0;
                    $item['purchase_price'] = $item['purchase_price'] ?? 0;
                    $item['sub_total'] = $item['sub_total'] ?? 0;
                    $item['mode'] = 'purchase';
                    PurchaseItemModel::create($item);
                }
            }
            DB::commit();

            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'success',
                'status' => Response::HTTP_OK,
//                'data' => $purchaseData ?? []
            ]));
            $response->setStatusCode(Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollback();
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'error',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error' => $e->getMessage(),
            ]));
            $response->setStatusCode(Response::HTTP_OK);
        }

        return $response;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        PurchaseModel::find($id)->delete();
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

}
