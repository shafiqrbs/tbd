<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\Accounting\App\Models\AccountJournalModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Inventory\App\Entities\PurchaseItem;
use Modules\Inventory\App\Http\Requests\ProductRequest;
use Modules\Inventory\App\Http\Requests\PurchaseRequest;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\ConfigPurchaseModel;
use Modules\Inventory\App\Models\ConfigSalesModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use function Symfony\Component\HttpFoundation\Session\Storage\Handler\getInsertStatement;

class PurchaseController extends Controller
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
    public function store(PurchaseRequest $request,EntityManager $em)
    {
        $service = new JsonRequestResponse();
        $input = $request->validated();

        $input['config_id'] = $this->domain['config_id'];
        $input['created_by_id'] = $this->domain['user_id'];

        if(empty($input['vendor_id']) and isset($input['vendor_name']) and isset($input['vendor_mobile'])) {
            $find = VendorModel::uniqueVendorCheck($this->domain['domain_id'], $input['vendor_mobile'], $input['vendor_name']);
            if (empty($find)) {
                $find = VendorModel::insertPurchaseVendor($this->domain, $input);
                $config = AccountingModel::where('id', $this->domain['acc_config'])->first();
                $ledgerExist = AccountHeadModel::where('vendor_id', $find->id)->where('config_id', $this->domain['acc_config'])->where('parent_id', $config->account_vendor_id)->first();
                if (empty($ledgerExist)) {
                    AccountHeadModel::insertVendorLedger($config, $find);
                }
                $vendor = $find->refresh();
                $input['vendor_id'] = $vendor->id;
            }else{
                $input['vendor_id'] = $find->id;
            }
        }
        $entity = PurchaseModel::create($input);
        $process = new PurchaseModel();
        $process->insertPurchaseItems($entity,$input['items'],$input['warehouse_id']);

        // purchase auto approve
        $findInvConfig = ConfigPurchaseModel::where('config_id',$this->domain['inv_config'])->first();
        if ($findInvConfig->is_purchase_auto_approved == 1){
            if (sizeof($entity->purchaseItems)>0){
                foreach ($entity->purchaseItems as $item){
                    // get average price
                    $itemAveragePrice = StockItemModel::calculateStockItemAveragePrice($item->stock_item_id,$item->config_id,$item);
                    //set average price
                    StockItemModel::where('id', $item->stock_item_id)->where('config_id',$item->config_id)->update(['average_price' => $itemAveragePrice,'purchase_price' => $item['purchase_price'],'price' => $item['sales_price'],'sales_price' => $item['sales_price']]);
                    $item->update(['approved_by_id' => $this->domain['user_id']]);
                    StockItemHistoryModel::openingStockQuantity($item,'purchase',$this->domain);
                }
            }
            $entity->update([
                'approved_by_id' => $this->domain['user_id'],
                'process' => 'Approved'
            ]);
            AccountJournalModel::insertPurchaseAccountJournal($this->domain,$entity->id);
        }

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
                }
                AccountJournalModel::insertPurchaseAccountJournal($this->domain,$purchase->id);
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
