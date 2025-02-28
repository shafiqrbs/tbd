<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Modules\Accounting\App\Entities\Config;
use Modules\Accounting\App\Models\TransactionModeModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Inventory\App\Entities\SalesItem;
use Modules\Inventory\App\Http\Requests\ProductRequest;
use Modules\Inventory\App\Http\Requests\SalesRequest;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use function Symfony\Component\HttpFoundation\Session\Storage\Handler\getInsertStatement;

class SalesController extends Controller
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
        $data = SalesModel::getRecords($request, $this->domain);
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
    public function store(SalesRequest $request, EntityManager $em)
    {
        $service = new JsonRequestResponse();
        $input = $request->validated();
        $input['config_id'] = $this->domain['config_id'];
        $entity = SalesModel::create($input);

        $entity->refresh();

        $em->getRepository(SalesItem::class)->salesInsert($entity->id,$input,$this->domain);
        $salesData = SalesModel::getShow($entity->id, $this->domain);

        // for stock maintain
        $entity->update(['approved_by_id' => $this->domain['user_id']]);
        if (sizeof($entity->salesItems)>0){
            foreach ($entity->salesItems as $item){
                StockItemHistoryModel::openingStockQuantity($item,'sales',$this->domain);
            }
        }

        $data = $service->returnJosnResponse($salesData);
        return $data;

    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = SalesModel::getShow($id, $this->domain);
        if (!$entity) {
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }
    /**
     * Show the specified resource for edit.
     */
    public function edit($id)
    {
        $entity = SalesModel::getEditData($id, $this->domain);
        $status = $entity ? Response::HTTP_OK : Response::HTTP_NOT_FOUND;

        return response()->json([
            'message' => 'success',
            'status' => $status,
            'data' => $entity ?? []
        ], Response::HTTP_OK);

    }


    /**
     * Update the specified resource in storage.
     */
    public function update(SalesRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $getSales = SalesModel::findOrFail($id);
            $getSales->fill($data);
            $getSales->save();

            SalesItemModel::where('sale_id', $id)->delete();
            if (sizeof($data['items'])>0){
                foreach ($data['items'] as $item){
                    $item['stock_item_id'] = $item['product_id'];
                    $item['name'] = $item['item_name'];
                    $item['uom'] = $item['uom'];
                    SalesItemModel::create($item);
                }
            }
            DB::commit();

            $salesData = SalesModel::getShow($id, $this->domain);


            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'success',
                'status' => Response::HTTP_OK,
                'data' => $salesData ?? []
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
     * Update the specified resource in storage.
     */
    public function domainCustomerSales($id)
    {
        DB::beginTransaction();
        try {
            $getSales = SalesModel::findOrFail($id);
            $getSalesItems = $getSales->salesItems;

            // get customer domain
            $customerDomain = $getSales->customerDomain;
            if ($customerDomain){
                // parent domain data
                $getVendor = VendorModel::where('customer_id', $customerDomain->id)->first();

                // child domain data
                $getAccountConfigId = DB::table('acc_config')->where('domain_id', $customerDomain->sub_domain_id)->first()->id;
                $getInventoryConfigId = DB::table('inv_config')->where('domain_id', $customerDomain->sub_domain_id)->first()->id;
                if ($getSales->transaction_mode_id){
                    $getTransactionModeSlug = TransactionModeModel::find($getSales->transaction_mode_id)->slug;
                    $getTransactionMode = TransactionModeModel::where('slug', $getTransactionModeSlug)->where('config_id',$getAccountConfigId)->first()->id;
                }

                $purchase = PurchaseModel::create([
                    'config_id' => $getInventoryConfigId,
                    'created_by_id' => $this->domain['user_id'],
                    'vendor_id' => $getVendor->id ?? null,
                    'transaction_mode_id' => $getTransactionMode ?? null,
                    'process' => 'created'
                ]);

                if ($purchase){
                    if (sizeof($getSalesItems)>0){
                        $totalPrice = 0;

                        $records = []; // Initialize an empty array to store records

                        foreach ($getSalesItems as $item) {
                            $getStockItemId = StockItemModel::where('parent_stock_item', $item['stock_item_id'])
                                ->where('config_id', $getInventoryConfigId)
                                ->first()
                                ->id;

                            $purchasePrice = $item['sales_price'] - ($item['sales_price'] * $customerDomain->discount_percent) / 100;
                            $subtotal = $item['quantity'] * $purchasePrice;
                            $totalPrice += $subtotal;

                            $records[] = [  // Add each record to the $records array
                                'purchase_id'   => $purchase->id,
                                'created_by_id' => $this->domain['user_id'],
                                'config_id'     => $getInventoryConfigId,
                                'created_at'    => now(),
                                'quantity'      => $item['quantity'],
                                'purchase_price' => $purchasePrice,
                                'sub_total'     => $subtotal,
                                'mode'          => 'purchase',
                                'updated_at'    => now(),
                                'stock_item_id' => $getStockItemId,
                            ];
                        }

                        // Insert multiple records at once
                        PurchaseItemModel::insert($records);


                        $purchase->update([
                            'sub_total' => $totalPrice,
                            'total'=>$totalPrice,
                            'payment'=>$getSales->payment,
                            'due'=>$totalPrice-$getSales->payment,
                            'discount_type' => $getSales->discount_type,
                        ]);
                    }
                }

                $getSales->update(['is_domain_sales_completed'=>1,'approved_by_id'=>$this->domain['user_id']]);
                // Manege stock
                if (sizeof($getSales->salesItems)>0){
                    foreach ($getSales->salesItems as $item){
                        StockItemHistoryModel::openingStockQuantity($item,'sales',$this->domain);
                    }
                }
            }
            DB::commit();
            return response()->json(['status' => 200, 'success' => true]);

            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['status' => 500, 'success' => false, 'error' => $e->getMessage()]);
            }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        SalesModel::find($id)->delete();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }

}
