<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DailyStockService;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Entities\AccountJournal;
use Modules\Accounting\App\Models\AccountJournalModel;
use Modules\Accounting\App\Models\AccountVoucherModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Entities\PurchaseItem;
use Modules\Inventory\App\Entities\StockItemHistory;
use Modules\Inventory\App\Entities\StockItemInventoryHistory;
use Modules\Inventory\App\Http\Requests\OpeningStockRequest;
use Modules\Inventory\App\Http\Requests\ProductRequest;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


class OpeningStockController extends Controller
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

        $data = PurchaseItemModel::getRecords($request, $this->domain);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => ResponseAlias::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities']
        ]));
        $response->setStatusCode(ResponseAlias::HTTP_OK);
        return $response;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OpeningStockRequest $request)
    {
        $service = new JsonRequestResponse();
        $input = $request->validated();

        $findStockItem = StockItemModel::find($input['product_id']);
        if (!$findStockItem) {
            return response()->json([
                'status' => 404,
                'message' => 'Stock Item not found',
            ], 404);
        }

        $input['config_id'] = $this->domain['config_id'];
        $input['created_by_id'] = $this->domain['user_id'];
        $input['stock_item_id'] = $input['product_id'];
        $input['quantity'] = $input['opening_quantity'];
        $input['warehouse_id'] = $input['warehouse_id'] ?? $this->domain['warehouse_id'];
        $input['name'] = $findStockItem->name ?? $findStockItem->display_name;
        $entity = PurchaseItemModel::create($input);
        $data = $service->returnJosnResponse($entity);
        return $data;
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
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, $id)
    {
        $data = $request->validated();
        $entity = PurchaseModel::find($id);
        $entity->update($data);

        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        PurchaseItemModel::find($id)->delete();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }

    public function inlineUpdate(Request $request)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $getPurchaseItem = PurchaseItemModel::find($request->id);

        // Start the database transaction
        DB::beginTransaction();
        try {

            $getPurchaseItem = PurchaseItemModel::find($request->id);

            // Validate purchase item existence
            if (!$getPurchaseItem) {
                $response->setContent(json_encode([
                    'status' => ResponseAlias::HTTP_NOT_FOUND,
                    'message' => 'Data not found',
                ]));
                $response->setStatusCode(ResponseAlias::HTTP_OK);
                return $response;
            }

            // Perform updates based on field_name
            if ($request->field_name === 'approve') {
                // Approve the purchase item
                $getPurchaseItem->update(['approved_by_id' => $this->domain['user_id']]);

                // Call the opening stock quantity method
                StockItemHistoryModel::openingStockQuantity($getPurchaseItem, 'opening',$this->domain);

                // for maintain inventory daily stock
                date_default_timezone_set('Asia/Dhaka');
                DailyStockService::maintainDailyStock(
                    date: date('Y-m-d'),
                    field: 'purchase_quantity',
                    configId: $this->domain['config_id'],
                    warehouseId: $getPurchaseItem->warehouse_id,
                    stockItemId: $getPurchaseItem->stock_item_id,
                    quantity: $getPurchaseItem->quantity
                );

                AccountJournalModel::insertOpeningStockAccountJournal($this->domain,$getPurchaseItem->id);
            }

            if ($request->field_name === 'opening_quantity') {
                $getPurchaseItem->update([
                    'opening_quantity' => $request->opening_quantity,
                    'quantity' => $request->opening_quantity,
                    'sub_total' => $request->subTotal,
                ]);
            }

            if ($request->field_name === 'purchase_price') {
                $getPurchaseItem->update([
                    'purchase_price' => $request->purchase_price,
                    'sub_total' => $request->subTotal,
                ]);
            }

            if ($request->field_name === 'warehouse_id') {
                $getPurchaseItem->update([
                    'warehouse_id' => $request->warehouse_id,
                ]);
            }

            // Commit the transaction after all updates are successful
            DB::commit();

            $response->setContent(json_encode([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'Updated successfully',
            ]));
            $response->setStatusCode(ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            $response->setContent(json_encode([
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ]));
            $response->setStatusCode(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }



    public function openingApprove(Request $request, EntityManager $em)
    {

        $getPurchaseItem = PurchaseItemModel::find($request->id);
        if ($request->field_name === 'approve' ){
            //  $getPurchaseItem->update(['approved_by_id' => $this->domain['user_id']]);
            if(empty($getPurchaseItem->stockItems)){
                $em->getRepository(StockItemInventoryHistory::class)->openingStockQuantity($getPurchaseItem->id);
            }
            if(empty($getPurchaseItem->stockItems)){
                $em->getRepository(AccountJournal::class)->openingStockQuantity($getPurchaseItem->id);
            }
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'status' => Response::HTTP_OK,
            'message' => 'update',
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }



}
