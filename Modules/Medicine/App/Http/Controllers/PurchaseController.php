<?php

namespace Modules\Medicine\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\UserModel;
use Modules\Medicine\App\Http\Requests\PurchaseRequest;
use Modules\Medicine\App\Models\PurchaseModel;
use Symfony\Component\HttpFoundation\Response;

class PurchaseController extends Controller
{
    protected UserModel $domain;

    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if (!empty($userId)) {
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }

    public function index(Request $request)
    {
        $data = PurchaseModel::getRecords($request, $this->domain);
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
    public function store(PurchaseRequest $request)
    {
        $input = $request->validated();
        DB::beginTransaction();
        try {
            // Common fields
            $input['config_id'] = $this->domain['config_id'];
            $input['process'] = 'Created';
            $input['warehouse_id'] = $input['warehouse_id'] ?? $this->domain['warehouse_id'];
            $input['mode'] = 'Purchase';
            $input['status'] = 1;

            // 1️⃣ Create purchase master record
            $purchase = PurchaseModel::create($input);

            // 2️⃣ Insert related purchase items
            PurchaseModel::insertPurchaseItems($purchase, $input['items'], $input['warehouse_id']);

            // 3️⃣ Commit transaction only if everything succeeds
            DB::commit();

            return response()->json([
                'status' => Response::HTTP_CREATED,
                'success' => true,
                'message' => 'Purchase created successfully!',
            ]);
        } catch (\Throwable $e) {
            // Rollback on any failure
            DB::rollBack();

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'success' => false,
                'message' => 'Failed to create purchase. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $data['process'] = 'Created';
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
                    $item['warehouse_id'] = $item['warehouse_id'] ?? $data['warehouse_id'];
                    $item['bonus_quantity'] = $item['bonus_quantity'];
                    PurchaseItemModel::create($item);
                }
            }
            DB::commit();

            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'success',
                'status' => ResponseAlias::HTTP_OK,
            ]));
            $response->setStatusCode(ResponseAlias::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollback();
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'error',
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
                'error' => $e->getMessage(),
            ]));
            $response->setStatusCode(ResponseAlias::HTTP_OK);
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
            $purchase->update([
                'approved_by_id' => $this->domain['user_id'],
                'process' => 'Approved'
            ]);

            if (sizeof($purchase->purchaseItems)>0){
                foreach ($purchase->purchaseItems as $item){
                    // get average price
                    $itemAveragePrice = StockItemModel::calculateStockItemAveragePrice($item->stock_item_id,$item->config_id,$item);
                    //set average price
                    StockItemModel::where('id', $item->stock_item_id)->where('config_id',$item->config_id)->update([
                        'average_price' => $itemAveragePrice,
                        'purchase_price' => $item['purchase_price'],
                        'price' => $item['sales_price'] ?? 0,
                        'sales_price' => $item['sales_price'] ?? 0
                    ]);

                    $item->update(['approved_by_id' => $this->domain['user_id']]);
                    StockItemHistoryModel::openingStockQuantity($item,'purchase',$this->domain);

                    // for maintain inventory daily stock
                    date_default_timezone_set('Asia/Dhaka');
                    DailyStockService::maintainDailyStock(
                        date: date('Y-m-d'),
                        field: 'purchase_quantity',
                        configId: $this->domain['config_id'],
                        warehouseId: $purchase->warehouse_id,
                        stockItemId: $item->stock_item_id,
                        quantity: $item->quantity
                    );
                }
                AccountJournalModel::insertPurchaseAccountJournal($this->domain,$purchase->id);
            }
            // Commit the transaction after all updates are successful
            DB::commit();
            $response->setContent(json_encode([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'Approved successfully',
            ]));
            $response->setStatusCode(ResponseAlias::HTTP_OK);
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

    public function purchaseCopy($id)
    {
        try {
            DB::beginTransaction();

            $original = PurchaseModel::with('purchaseItems')->findOrFail($id);

            $newPurchase = $original->replicate([
                'approved_by_id','process','invoice_date'
            ]);
            $newPurchase->approved_by_id = null;
            $newPurchase->invoice_date = now();
            $newPurchase->process = 'Created';
            $newPurchase->save();

            foreach ($original->purchaseItems as $item) {
                $newItem = $item->replicate(['purchase_id','approved_by_id']);
                $newItem->purchase_id = $newPurchase->id;
                $newItem->approved_by_id = null;
                $newItem->save();
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Purchase duplicated successfully!',
                'new_purchase_id' => $newPurchase->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Duplication failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}
