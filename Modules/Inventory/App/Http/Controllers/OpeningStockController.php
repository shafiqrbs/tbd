<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Accounting\App\Entities\AccountJournal;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Entities\StockItemHistory;
use Modules\Inventory\App\Entities\StockItemInventoryHistory;
use Modules\Inventory\App\Http\Requests\OpeningStockRequest;
use Modules\Inventory\App\Http\Requests\ProductRequest;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseModel;


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
    public function store(OpeningStockRequest $request)
    {
        $service = new JsonRequestResponse();
        $input = $request->validated();
        $input['config_id'] = $this->domain['config_id'];
        $input['created_by_id'] = $this->domain['user_id'];
        $input['stock_item_id'] = $input['product_id'];
        $input['quantity'] = $input['opening_quantity'];
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

    public function inlineUpdate(Request $request, EntityManager $em)
    {

        $getPurchaseItem = PurchaseItemModel::find($request->id);
        if (!$getPurchaseItem){
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data not found',
            ]));
            $response->setStatusCode(Response::HTTP_OK);
            return $response;
        }
      //  dd($getPurchaseItem);

        if ($request->field_name === 'approve' ){
            $getPurchaseItem->update(['approved_by_id' => $this->domain['user_id']]);
            if($getPurchaseItem){
                $em->getRepository(StockItemHistory::class)->openingStockQuantity($getPurchaseItem->id);
            }
            if($getPurchaseItem){
               // $em->getRepository(AccountJournal::class)->openingProductInsert($getPurchaseItem->id);
            }
        }
        if ($request->field_name === 'opening_quantity' ){
            $getPurchaseItem->update(['opening_quantity' => $request->opening_quantity,'quantity' => $request->opening_quantity,'sub_total'=>$request->subTotal]);
        }
        if ($request->field_name === 'purchase_price' ){
            $getPurchaseItem->update(['purchase_price' => $request->purchase_price,'sub_total' => $request->subTotal]);
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
