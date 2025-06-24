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
use Modules\Accounting\App\Entities\AccountHead;
use Modules\Accounting\App\Entities\AccountJournal;
use Modules\Accounting\App\Entities\Config;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\Accounting\App\Models\AccountJournalModel;
use Modules\Accounting\App\Models\TransactionModeModel;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Inventory\App\Entities\SalesItem;
use Modules\Inventory\App\Http\Requests\ProductRequest;
use Modules\Inventory\App\Http\Requests\SalesRequest;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\ConfigSalesModel;
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

    public function store(SalesRequest $request, EntityManager $em,GeneratePatternCodeService $patternCodeService)
    {
        $input = $request->validated();
        $input['config_id'] = $this->domain['config_id'];
        $input['sales_form'] = 'inventory';
        // Start Database Transaction to Ensure Data Consistency
        DB::beginTransaction();

        try {

            if (empty($input['sales_by_id'])){
                $input['sales_by_id'] = $this->domain['user_id'];
            }
            $input['inv_config'] = $this->domain['inv_config'];
            // Create Sales Record
            $sales = SalesModel::create($input);
            $sales->refresh();

            // Insert Sales Items
            SalesItemModel::insertSalesItems($sales, $input['items']);

            // Fetch Sales Data for Response
            $salesData = SalesModel::getShow($sales->id, $this->domain);

            // Customer Maintenance Logic (Auto Approval)
            if(empty($input['customer_id']) and isset($input['customer_name']) and isset($input['customer_mobile'])) {
                $findCustomer = CustomerModel::uniqueCustomerCheck($this->domain['domain_id'],$input['customer_mobile'],$input['customer_name']);
                if(empty($findCustomer)){
                    $findCustomer = CustomerModel::insertSalesCustomer($this->domain,$input);
                    $config = AccountingModel::where('id',$this->domain['acc_config'])->first();
                    $ledgerExist = AccountHeadModel::where('customer_id',$findCustomer->id)->where('config_id',$this->domain['acc_config'])->where('parent_id',$config->account_customer_id)->first();
                    if (empty($ledgerExist)){
                        AccountHeadModel::insertCustomerLedger($config,$findCustomer);
                    }
                    $findCustomer = $findCustomer->refresh();
                }
            }elseif(empty($input['customer_id'])){
                $domain = DomainModel::find($this->domain->domain_id);
                $license = ($domain['license_no']) ? $domain['license_no'] : $domain['mobile'];
                $findCustomer = CustomerModel::where([
                    ['is_default_customer', '=', 1],
                    ['mobile', '=', $license],
                    ['domain_id', '=', $this->domain->domain_id],
                ])->select('id')->first();
            }else{
                $findCustomer = CustomerModel::find($input['customer_id']);
            }

            $sales->update(['customer_id' => $findCustomer->id]);
            $findInvConfig = ConfigSalesModel::where('config_id',$this->domain['inv_config'])->first();
            if ($findInvConfig->is_sales_auto_approved) {
                $sales->update(['approved_by_id' => $this->domain['user_id']]);
                if ($sales->salesItems->count() > 0) {
                    foreach ($sales->salesItems as $item) {
                        StockItemHistoryModel::openingStockQuantity($item, 'sales', $this->domain);
                    }
                }
                AccountJournalModel::insertSalesAccountJournal($this->domain,$sales->id);
            }
            DB::commit();

            // Send Success Response
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Sales created successfully.',
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

                // accounting journal entry
                AccountJournalModel::insertSalesAccountJournal($this->domain,$getSales->id);
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
            $entity = SalesModel::find($id);
            $entity->update([
                'approved_by_id' => $this->domain['user_id'],
                'process' => 'Approved'
            ]);
            $entity->update(['approved_by_id' => $this->domain['user_id']]);
            if ($entity->salesItems->count() > 0) {
                foreach ($entity->salesItems as $item) {
                    StockItemHistoryModel::openingStockQuantity($item, 'sales', $this->domain);
                }
            }

            // accounting journal entry
            AccountJournalModel::insertSalesAccountJournal($this->domain,$entity->id);

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
