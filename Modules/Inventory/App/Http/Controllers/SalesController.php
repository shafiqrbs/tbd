<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Modules\Accounting\App\Entities\Config;
use Modules\Accounting\App\Models\TransactionModeModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Inventory\App\Entities\SalesItem;
use Modules\Inventory\App\Http\Requests\ProductRequest;
use Modules\Inventory\App\Http\Requests\SalesRequest;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\SettingModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
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

    public function store(SalesRequest $request)
    {
        $input = $request->validated();
        $input['config_id'] = $this->domain['config_id'];
        $input['sales_form'] = 'inventory';

        // Start Database Transaction to Ensure Data Consistency
        DB::beginTransaction();

        try {
            // Create Sales Record
            $sales = SalesModel::create($input);
            $sales->refresh();

            // Insert Sales Items
            SalesItemModel::insertSalesItems($sales, $input['items']);

            // Fetch Sales Data for Response
            $salesData = SalesModel::getShow($sales->id, $this->domain);

            // Stock Maintenance Logic (Auto Approval)
            $findCustomer = CustomerModel::find($input['customer_id']);
            $findUserType = \Modules\Core\App\Models\SettingModel::find($findCustomer->customer_group_id);
            $findConfig = ConfigModel::find($this->domain['config_id']);

            if (
                $findConfig->is_sales_auto_approved &&
                (!$findUserType || $findUserType->name !== 'Domain')
            ) {
                $sales->update(['approved_by_id' => $this->domain['user_id']]);

                if ($sales->salesItems->count() > 0) {
                    foreach ($sales->salesItems as $item) {
                        StockItemHistoryModel::openingStockQuantity($item, 'sales', $this->domain);
                    }
                }
            }

            // Commit Transaction
            DB::commit();

            // Send Success Response
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Sales updated successfully.',
                'data' => $salesData,
            ]);
        } catch (Exception $e) {
            // Rollback Transaction on Failure
            DB::rollBack();

            // Log the Error (For Debugging Purposes)
            \Log::error('Sales transaction failed: ' . $e->getMessage());

            // Send Error Response
            return response()->json([
                'message' => 'An error occurred while processing the sale.',
                'error' => $e->getMessage()
            ], 500);
        }
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
        $status = $entity ? ResponseAlias::HTTP_OK : ResponseAlias::HTTP_NOT_FOUND;

        return response()->json([
            'message' => 'success',
            'status' => $status,
            'data' => $entity ?? []
        ], ResponseAlias::HTTP_OK);

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
                SalesItemModel::insertSalesItems($getSales, $data['items']);
            }
            DB::commit();

            $salesData = SalesModel::getShow($id, $this->domain);


            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'success',
                'status' => ResponseAlias::HTTP_OK,
                'data' => $salesData ?? []
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
                                ->first();
                            if (!$getStockItemId){
                                return response()->json(['status' => 404, 'success' => false, 'message' => 'Stock item not found in child domain']);
                            }
                            $getStockItemId =$getStockItemId->id;

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
    public function notDomainCustomerSales($id)
    {
        DB::beginTransaction();
        try {
            $getSales = SalesModel::findOrFail($id);
            $getSalesItems = $getSales->salesItems;

            $getSales->update(['approved_by_id'=>$this->domain['user_id']]);
            // Manege stock
            if (sizeof($getSales->salesItems)>0){
                foreach ($getSales->salesItems as $item){
                    StockItemHistoryModel::openingStockQuantity($item,'sales',$this->domain);
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
