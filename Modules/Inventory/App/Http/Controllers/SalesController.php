<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DailyStockService;
use App\Services\Notification\SmsService;
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
use Modules\Production\App\Models\ProductionBatchModel;
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
        // send sms
      //  $sms = (new SmsService())->send("Hlw Raju", "8801828148148");

        $data = SalesModel::getRecords($request, $this->domain);
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

    public function store(SalesRequest $request, EntityManager $em,GeneratePatternCodeService $patternCodeService)
    {
        $input = $request->validated();
        $input['config_id'] = $this->domain['config_id'];
        $input['sales_form'] = 'inventory';
        $input['process'] = 'Created';

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
            SalesItemModel::insertSalesItems($sales, $input['items'],$this->domain['warehouse_id']);

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
                $sales->update(['approved_by_id' => $this->domain['user_id'], 'process' => 'Approved']);
                if ($sales->salesItems->count() > 0) {
                    foreach ($sales->salesItems as $item) {
                        // for stock history
                        StockItemHistoryModel::openingStockQuantity($item, 'sales', $this->domain);

                        // for maintain inventory daily stock
                        date_default_timezone_set('Asia/Dhaka');
                        DailyStockService::maintainDailyStock(
                            date: date('Y-m-d'),
                            field: 'sales_quantity',
                            configId: $this->domain['config_id'],
                            warehouseId: $item->warehouse_id,
                            stockItemId: $item->stock_item_id,
                            quantity: $item->quantity
                        );

                        // update for set sales quantity in purchase item for batch wise sales
                        if ($item->purchase_item_id) {
                            PurchaseItemModel::updateSalesQuantity($item->purchase_item_id,$item->quantity);
                        }
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
                SalesItemModel::insertSalesItems($getSales, $data['items'],$this->domain['warehouse_id']);
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

                $getTransactionMode = null;

                if ($getSales->transaction_mode_id) {
                    $getTransactionMode = TransactionModeModel::where('id', $getSales->transaction_mode_id)
                        ->where('config_id', $getAccountConfigId)
                        ->first();

                    // If not found with the given config_id, fallback to first available
                    if (!$getTransactionMode) {
                        $getTransactionMode = TransactionModeModel::where('config_id', $getAccountConfigId)->first();
                    }
                }

                $purchase = PurchaseModel::create([
                    'config_id'          => $getInventoryConfigId,
                    'created_by_id'      => $this->domain['user_id'],
                    'vendor_id'          => $getVendor->id ?? null,
                    'transaction_mode_id'=> $getTransactionMode?->id,
                    'process'            => 'in-progress',
                    'mode'               => 'Requisition',
                    'is_requisition'     => 1,
                    'parent_sale_id'     => $id
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

                            $expiryDuration = StockItemModel::getProductStockDetails($item->stock_item_id,$this->domain);
                            if ($expiryDuration && $expiryDuration->expiry_duration) {
                                $startDate = new \DateTime(); // today
                                $endDate = (new \DateTime())->modify("+{$expiryDuration->expiry_duration} days"); // use the property
                                $item->update([
                                    'production_date' => $startDate,
                                    'expired_date'    => $endDate,
                                ]);
                            }
                            $getStockItemId = $getStockItemId->id;
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
                                'production_date'     => $item->production_date ?? null,
                                'expired_date'     => $item->expired_date ?? null,
                                'sub_total'     => $subtotal,
                                'mode'          => 'purchase',
                                'updated_at'    => now(),
                                'stock_item_id' => $getStockItemId,
                                'parent_sales_item_id' => $item['id'],
                            ];
                        }

                        // Convert collection of models/arrays into plain array of IDs
                        $salesItemIds = collect($getSalesItems)->pluck('id')->toArray();

                        PurchaseItemModel::insert($records);

                        // Fetch the inserted purchase items
                        $inserted = PurchaseItemModel::where('purchase_id', $purchase->id)
                            ->whereIn('parent_sales_item_id', $salesItemIds)
                            ->get(['id', 'parent_sales_item_id']);

                        // Map and update sales items
                        foreach ($inserted as $pi) {
                            SalesItemModel::where('id', $pi->parent_sales_item_id)
                                ->update(['child_purchase_item_id' => $pi->id]);
                        }
                        $purchase->update([
                            'sub_total' => $totalPrice,
                            'total'=>$totalPrice,
                            'payment'=>$getSales->payment,
                            'due'=>$totalPrice-$getSales->payment,
                            'discount_type' => $getSales->discount_type,
                        ]);
                    }
                }

                $getSales->update(['is_domain_sales_completed'=> 1,'approved_by_id'=>$this->domain['user_id'],'process' => 'In-progress','child_purchase_id'=>$purchase->id]);
                // Manege stock
                if (sizeof($getSales->salesItems)>0){
                    foreach ($getSales->salesItems as $item){
                        StockItemHistoryModel::openingStockQuantity($item,'sales',$this->domain);

                        // for maintain inventory daily stock
                        date_default_timezone_set('Asia/Dhaka');
                        DailyStockService::maintainDailyStock(
                            date: date('Y-m-d'),
                            field: 'sales_quantity',
                            configId: $this->domain['config_id'],
                            warehouseId: $item->warehouse_id,
                            stockItemId: $item->stock_item_id,
                            quantity: $item->quantity
                        );

                        // update for set sales quantity in purchase item for batch wise sales
                        if ($item->purchase_item_id) {
                            PurchaseItemModel::updateSalesQuantity($item->purchase_item_id,$item->quantity);
                        }
                    }
                }

                // accounting journal entry
               // AccountJournalModel::insertSalesAccountJournal($this->domain,$getSales->id);
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
            if (sizeof($getSalesItems)>0){
                foreach ($getSalesItems as $item){
                    StockItemHistoryModel::openingStockQuantity($item,'sales',$this->domain);

                    // for maintain inventory daily stock
                    date_default_timezone_set('Asia/Dhaka');
                    DailyStockService::maintainDailyStock(
                        date: date('Y-m-d'),
                        field: 'sales_quantity',
                        configId: $this->domain['config_id'],
                        warehouseId: $item->warehouse_id,
                        stockItemId: $item->stock_item_id,
                        quantity: $item->quantity
                    );

                    // update for set sales quantity in purchase item for batch wise sales
                    if ($item->purchase_item_id) {
                        PurchaseItemModel::updateSalesQuantity($item->purchase_item_id,$item->quantity);
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

            if ($entity->salesItems->count() > 0) {
                foreach ($entity->salesItems as $item) {
                    StockItemHistoryModel::openingStockQuantity($item, 'sales', $this->domain);

                    // for maintain inventory daily stock
                    date_default_timezone_set('Asia/Dhaka');
                    DailyStockService::maintainDailyStock(
                        date: date('Y-m-d'),
                        field: 'sales_quantity',
                        configId: $this->domain['config_id'],
                        warehouseId: $item->warehouse_id,
                        stockItemId: $item->stock_item_id,
                        quantity: $item->quantity
                    );
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


    public function dailySalesReport(Request $request)
    {
        $params = $request->only('year', 'month','warehouse_id');
        $inventoryConfigId = $this->domain['inv_config'];
        $domainConfigId = $this->domain['domain_id'];
//        dump($params,$inventoryConfigId,$domainConfigId);

        $entity = SalesModel::dailySalesReport($params, $domainConfigId, $inventoryConfigId);
//        dump($entity);
        return response()->json([
            'message' => 'success',
            'status' => ResponseAlias::HTTP_OK,
            'data' => $entity
        ], ResponseAlias::HTTP_OK);
    }

    public function salesCopy($id)
    {
        try {
            DB::beginTransaction();

            $original = SalesModel::with('salesItems')->findOrFail($id);

            $newSale = $original->replicate([
                'approved_by_id','process','invoice_date'
            ]);
            $newSale->approved_by_id = null;
            $newSale->process = 'Created';
            $newSale->invoice_date = now();
            $newSale->save();

            foreach ($original->salesItems as $item) {
                $newItem = $item->replicate(['sale_id']);
                $newItem->sale_id = $newSale->id;
                $newItem->save();
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Sale duplicated successfully!',
                'new_sale_id' => $newSale->id,
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
