<?php

namespace Modules\Domain\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Accounting\App\Entities\AccountHead;
use Modules\Accounting\App\Entities\AccountVoucher;
use Modules\Accounting\App\Entities\TransactionMode;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\Accounting\App\Models\AccountVoucherModel;
use Modules\Accounting\App\Models\TransactionModeModel;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Entities\UserRole;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\SettingModel;
use Modules\Core\App\Models\SettingTypeModel;
use Modules\Core\App\Models\UserRoleModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Domain\App\Entities\GlobalOption;
use Modules\Domain\App\Entities\SubDomain;
use Modules\Domain\App\Http\Requests\DomainRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\CurrencyModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Inventory\App\Entities\PurchaseItem;
use Modules\Inventory\App\Entities\Setting;
use Modules\Inventory\App\Entities\StockItemInventoryHistory;
use Modules\Inventory\App\Models\ConfigDiscountModel;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\ConfigProductModel;
use Modules\Inventory\App\Models\ConfigPurchaseModel;
use Modules\Inventory\App\Models\ConfigSalesModel;
use Modules\Inventory\App\Models\ConfigVatModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemInventoryHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\NbrVatTax\App\Models\NbrVatConfigModel;
use Modules\Production\App\Models\ProductionConfig;
use Modules\Utility\App\Models\SettingModel as UtilitySettingModel;
use Modules\Inventory\App\Models\SettingModel as InventorySettingModel;

class DomainController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $entityId = $request->header('X-Api-User');
        if ($entityId && !empty($entityId)){
            $entityData = UserModel::getUserData($entityId);
            $this->domain = $entityData;
        }
    }

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request){

        $data = DomainModel::getRecords($request);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
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

    public function store(DomainRequest $request , EntityManager $em,GeneratePatternCodeService $patternCodeService)
    {
        $data = $request->validated();

        // Start the transaction
        DB::beginTransaction();

        try {
            // Step 1: Create the domain the entity
            $data['modules'] = json_encode($data['modules'], JSON_PRETTY_PRINT);
            $data['license_no'] = $data['mobile'];
            $entity = DomainModel::create($data);

            // Step 2: Prepare email and password, then create user
            $password = "@123456";
            $email = $data['email'] ?? "{$data['username']}@gmail.com"; // If email is not present, default to username@gmail.com

            $user = UserModel::create([
                'username' => $data['username'],
                'email' => $email,
                'password' => Hash::make($password),
                'domain_id' => $entity->id,
                'user_group' => 'domain'
            ]);
            $accessControlRoles = array('role_domain');
            $accessControlRolesJson = json_encode($accessControlRoles, JSON_PRETTY_PRINT);

            UserRoleModel::create([
                'user_id' => $user->id,
                'access_control_role' => $accessControlRolesJson,
                'android_control_role'=> $accessControlRolesJson,
            ]);

            // create domain customer

            // Fetch the customer
            $customer = CustomerModel::where('domain_id',$entity->id)->first();
            if (!$customer) {
                $getCoreSettingTypeId = SettingTypeModel::where('slug', 'customer-group')->first();
                $getCustomerGroupId = SettingModel::updateOrCreate(
                    [
                        'domain_id' => $entity->id,
                        'setting_type_id' => $getCoreSettingTypeId->id,
                        'name' => 'Domain',
                        'is_private' => true,
                    ],
                    [
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                SettingModel::updateOrCreate(
                    [
                        'domain_id' => $entity->id,
                        'setting_type_id' => $getCoreSettingTypeId->id,
                        'name' => 'Default',
                        'is_private' => true,
                    ],
                    [
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // Handle Customer
                $code = $this->generateCustomerCode($patternCodeService);
                CustomerModel::updateOrCreate(
                    [
                        'domain_id' => $entity->id,
                        'name' => 'Default',
                        'mobile' => $data['mobile'],
                        'customer_group_id' => $getCustomerGroupId->id ?? null,
                    ],
                    [
                        'slug' => Str::slug($entity->name),
                        'customer_id' => $code['generate_id'],
                        'email' => $entity->email,
                        'address' => $entity->address,
                        'is_default_customer' => true,
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }


            // Step 3: Create the inventory configuration (config)
            $currency = CurrencyModel::find(1);

            $config = $config =  ConfigModel::create([
                'domain_id' => $entity->id,
                'currency_id' => $currency->id,
                'zero_stock' => true,
                'is_sku' => true,
                'is_measurement' => true,
                'is_product_gallery' => true,
                'is_multi_price' => true,
                'business_model_id' => $entity->business_model_id,
            ]);

            // Step 4: Create the accounting data
            ConfigProductModel::create([
                'config_id' => $config->id,
            ]);

            // Step 4: Create the accounting data
            ConfigSalesModel::create([
                'config_id' => $config->id,
                'customer_group_id' => $getCustomerGroupId->id,
            ]);

            // Step 4: Create the accounting data
            ConfigPurchaseModel::create([
                'config_id' => $config->id,
            ]);

            // Step 4: Create the accounting data
            $accountingConfig = AccountingModel::create([
                'domain_id' => $entity->id,
                'financial_start_date' =>  now(),
                'financial_end_date' =>  now(),
            ]);

            // Step 4: Create the accounting data
            NbrVatConfigModel::create([
                'domain_id' => $entity->id,
            ]);

             // Step 5: Create the Production data
            ProductionConfig::create([
                'domain_id' => $entity->id,
            ]);

            $getProductType = UtilitySettingModel::getEntityDropdown('product-type');
            if (count($getProductType) > 0) {
                // If no inventory config found, return JSON response.
                if (!$config) {
                    DB::rollBack();
                    $response = new Response();
                    $response->headers->set('Content-Type', 'application/json');
                    $response->setContent(json_encode([
                        'message' => 'Inventory config not found',
                        'status' => Response::HTTP_NOT_FOUND,
                    ]));
                    $response->setStatusCode(Response::HTTP_OK);
                    return $response;
                }

                // Loop through each product type and either find or create inventory setting.
                foreach ($getProductType as $type) {
                    // If the inventory setting is not found, create a new one.
                    InventorySettingModel::create([
                        'config_id' => $config->id,
                        'setting_id' => $type->id,
                        'name' => $type->name,
                        'slug' => $type->slug,
                        'parent_slug' => 'product-type',
                        'is_production' => in_array($type->slug,
                            ['post-production', 'mid-production', 'pre-production']) ? 1 : 0,
                    ]);
                }

                TransactionModeModel::create([
                    'config_id' => $accountingConfig->id,
                    'account_owner' => 'Cash',
                    'authorised' => 'Cash',
                    'name' => 'Cash',
                    'short_name' => 'Cash',
                    'slug' => 'cash',
                    'is_selected' => true,
                    'path' => null,
                    'account_type' => 'Current',
                    'method_id' => 20,
                    'status' => true
                ]);
            }


            // Commit all database operations
            DB::commit();
            $em->getRepository(AccountHead::class)->generateAccountHead($accountingConfig->id);
            // Return the response
            $service = new JsonRequestResponse();
            return $service->returnJosnResponse($entity);

        } catch (Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();

            // Optionally log the exception for debugging purposes
            \Log::error('Error storing domain and related data: ' . $e->getMessage());

            // Return an error response
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'An error occurred while saving the domain and related data.',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }
    }

    private function generateCustomerCode($patternCodeService): array
    {
        $params = [
            'domain' => $this->domain['global_id'],
            'table' => 'cor_customers',
            'prefix' => 'CUS-',
        ];
        $pattern = $patternCodeService->customerCode($params);
        return $pattern;
    }


    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();

        // Fetch the domain entity
        $entity = DomainModel::find($id);

        if (!$entity) {
            return $service->returnJosnResponse([
                'message' => 'Domain not found',
                'status' => Response::HTTP_NOT_FOUND,
            ]);
        }

        // Retrieve the inventory config id based on domain_id
        $config = ConfigModel::where('domain_id', $id)->first();

        if (!$config) {
            return $service->returnJosnResponse([
                'message' => 'Inventory config not found for this domain',
                'status' => Response::HTTP_NOT_FOUND,
            ]);
        }

        $getInvConfigId = $config->id;

        // if product type not exists then create
        $productTypeExists = InventorySettingModel::where('config_id', $getInvConfigId)
            ->where('parent_slug', 'product-type')
            ->exists();

        if (!$productTypeExists) {
            $getProductType = UtilitySettingModel::getEntityDropdown('product-type');
            if (count($getProductType) > 0) {
                // Loop through each product type and either find or create inventory setting.
                foreach ($getProductType as $type) {
                    // If the inventory setting is not found, create a new one.
                    InventorySettingModel::create([
                        'config_id' => $getInvConfigId,
                        'setting_id' => $type->id,
                        'name' => $type->name,
                        'slug' => $type->slug,
                        'parent_slug' => 'product-type',
                        'is_production' => in_array($type->slug,
                            ['post-production', 'mid-production', 'pre-production']) ? 1 : 0,
                    ]);
                }
            }
        }

        // Fetch relevant product types settings as setting_id array
        $getInvProductType = InventorySettingModel::where('config_id', $getInvConfigId)
            ->where('parent_slug', 'product-type')
            ->where('status', 1)
            ->get('id')
            ->toArray();

        // Extract ids as strings
        $ids = array_map(function($module) {
            return (string)$module['id'];
        }, $getInvProductType);

        // Attach the product types to the entity
        $entity['product_types'] = $ids;

        // fetch inventory setting product type for generate checkbox
        $getInvProductTypeForCheckbox = InventorySettingModel::where('config_id', $getInvConfigId)
            ->where('parent_slug', 'product-type')
            ->get()
            ->toArray();
        $entity['product_types_checkbox'] = $getInvProductTypeForCheckbox;

        // Return a structured JSON response using your service
        return $service->returnJosnResponse($entity);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $service = new JsonRequestResponse();
        $entity = DomainModel::find($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(DomainRequest $request, $id)
    {
        $data = $request->validated();

        // Start the transaction.
        DB::beginTransaction();

        try {
            // Find inventory config id.
            $getInvConfigId = ConfigModel::where('domain_id', $id)->first('id')->id;

            // If no inventory config found, return JSON response.
            if (!$getInvConfigId) {
                DB::rollBack();  // Rollback if inventory config is not found.

                $response = new Response();
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent(json_encode([
                    'message' => 'Inventory config not found',
                    'status' => Response::HTTP_NOT_FOUND,
                ]));
                $response->setStatusCode(Response::HTTP_OK);
                return $response;
            }

            $getInvSetting = InventorySettingModel::where('config_id', $getInvConfigId)
                ->where('parent_slug', 'product-type')
                ->get();

            // Loop through each product type and either find or create inventory setting.
            foreach ($getInvSetting as $type) {
                if (in_array($type->id, $data['product_types'])) {
                    $type->update(['status'=>true]);
                }else{
                    $type->update(['status'=>false]);
                }
            }

            // Find and update the domain entity.
            $entity = DomainModel::find($id);
            $entity->update($data);

            // If we got this far, everything is okay, commit the transaction.
            DB::commit();

            // Return a json response using your service.
            $service = new JsonRequestResponse();
            return $service->returnJosnResponse($entity);

        } catch (Exception $e) {
            // If there's an exception, rollback the transaction.
            DB::rollBack();

            // Optionally log the exception (for debugging purposes)
            \Log::error('Error updating domain and inventory settings: '.$e->getMessage());

            // Return an error response.
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'An error occurred while updating.',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function subDomain(Request $request,$id, EntityManager $em)
    {

        $subDomain = $request->sub_domain;
        $entity = $em->getRepository(GlobalOption::class)->find($id);
        $em->getRepository(SubDomain::class)->insertUpdate($entity,$subDomain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }

     /**
     * Update the specified resource in storage.
     */
    public function inventorySetting(Request $request,$id, EntityManager $em)
    {
        $setting_id = $request->setting_id;
        $entity = $em->getRepository(GlobalOption::class)->find($id);
        $em->getRepository(Setting::class)->insertUpdate($entity,$setting_id);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        DomainModel::find($id)->delete();
        $entity = ['message'=>'delete'];
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Reset the specified resource from storage.
     */

    public function resetData($id)
    {
        // Ensure the domain exists
        $findDomain = DomainModel::findOrFail($id);

        // Fetch domain config
        $allConfigId = DomainModel::getDomainConfigData($id)->toArray();

        if (empty($allConfigId['inv_config'])) {
            return response()->json(['message' => 'Inventory config not found', 'status' => Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
        }



       /* // Delete purchases and related data using chunking
        PurchaseModel::with('purchaseItems.stock.stockItemHistory')
            ->where('config_id', $allConfigId['inv_config'])
            ->chunk(100, function ($purchases) {
                $purchases->each(function ($purchase) {
                    $purchase->purchaseItems->each(function ($purchaseItem) {
                        if ($purchaseItem->stock && $purchaseItem->stock->stockItemHistory->isNotEmpty()) {
                            $purchaseItem->stock->stockItemHistory->each(function ($history) {
                                $history->delete();
                            });
                        }
                    });
                    $purchase->delete();
                });
            });*/


        /*// Delete sales and related data using chunking
        SalesModel::with('salesItems.stock.stockItemHistory')
            ->where('config_id', $allConfigId['inv_config'])
            ->chunk(100, function ($sales) {
                $sales->each(function ($sale) {
                    $sale->salesItems->each(function ($salesItem) {
                        if ($salesItem->stock && $salesItem->stock->stockItemHistory->isNotEmpty()) {
                            $salesItem->stock->stockItemHistory->each(function ($history) {
                                $history->delete();
                            });
                        }
                    });
                    $sale->delete();
                });
            });*/


        $invConfig = $allConfigId['inv_config'];
        StockItemHistoryModel::where('config_id', $invConfig)->delete();
        SalesModel::where('config_id', $invConfig)->delete();
        PurchaseItemModel::where('config_id', $invConfig)->delete();
        PurchaseModel::where('config_id', $invConfig)->delete();


        // Bulk update stock item quantities
        StockItemModel::where('config_id', $allConfigId['inv_config'])->update(['quantity' => 0]);
        return response()->json(['message' => 'Domain reset successfully', 'status' => Response::HTTP_OK], Response::HTTP_OK);
    }

    public function restoreData($id, EntityManager $em)
    {
        $findDomain = DomainModel::findOrFail($id);
        if(empty($findDomain)){
            return response()->json(
                [
                    'message' => 'Inventory config not found',
                    'status' => Response::HTTP_NOT_FOUND
                ],
                Response::HTTP_NOT_FOUND
            );
        }
        $entity = UserModel::getDomainData($id);
        $domain = $entity;
        $invConfig = $entity['inv_config'];
        StockItemHistoryModel::where('config_id', $invConfig)->delete();
        SalesModel::where('config_id', $invConfig)->delete();
        PurchaseItemModel::where('config_id', $invConfig)->delete();
        PurchaseModel::where('config_id', $invConfig)->delete();
        // Bulk update stock item quantities
        StockItemModel::where('config_id',$invConfig)->update(['quantity' => 0]);

        // Start the transaction
        DB::beginTransaction();

        try {

            $getCoreSettingTypeId = SettingTypeModel::where('slug', 'customer-group')->first();
            $getCustomerGroupId = SettingModel::updateOrCreate(
                [
                    'domain_id' => $id,
                    'setting_type_id' => $getCoreSettingTypeId->id,
                    'name' => 'Domain',
                    'is_private' => true,
                ],
                [
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            SettingModel::updateOrCreate(
                [
                    'domain_id' => $id,
                    'setting_type_id' => $getCoreSettingTypeId->id,
                    'name' => 'Default',
                    'is_private' => true,
                ],
                [
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            CustomerModel::updateOrCreate(
                [
                    'domain_id' => $id,
                    'name' => 'Default',
                    'mobile' => $entity['license_no'],
                    'customer_group_id' => $getCustomerGroupId->id ?? null,
                ],
                [
                    'slug' => Str::slug($entity->name),
                    'email' => $entity->email,
                    'address' => $entity->address,
                    'is_default_customer' => true,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $vendorSettingType = SettingTypeModel::where('slug', 'vendor-group')->first();

            $getVendorGroupId = SettingModel::updateOrCreate(
                [
                    'domain_id' => $id,
                    'setting_type_id' => $vendorSettingType->id,
                    'name' => 'Domain',
                    'is_private' => true,
                ],
                [
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            SettingModel::updateOrCreate(
                [
                    'domain_id' => $id,
                    'setting_type_id' => $vendorSettingType->id,
                    'name' => 'Default',
                    'is_private' => true,
                ],
                [
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );



            // Step 3: Create the inventory configuration (config)
            $currency = CurrencyModel::find(1);

            $config =  ConfigModel::updateOrCreate([
                'domain_id' => $id,
                ],[
                'currency_id' => $currency->id,
                'zero_stock' => true,
                'is_sku' => false,
                'is_measurement' => false,
                'is_product_gallery' => false,
                'is_multi_price' => false,
                'business_model_id' => $entity->business_model_id,
            ]);


            // Step 4: Create the accounting data
            ConfigProductModel::updateOrCreate([
                'config_id' => $domain->config_id,
            ]);

            // Step 4: Create the accounting data
            ConfigDiscountModel::updateOrCreate([
                'config_id' => $domain->config_id,
            ]);

            // Step 4: Create the accounting data
            ConfigSalesModel::updateOrCreate(
                ['config_id' => $domain->config_id],
                ['default_customer_group_id' => $getCustomerGroupId->id ?? null]
            );

            // Step 4: Create the accounting data
            ConfigPurchaseModel::updateOrCreate(
                ['config_id' => $domain->config_id],
                ['default_vendor_group_id' => $getVendorGroupId->id ?? null]
            );

            // Step 4: Create the accounting data
            ConfigVatModel::updateOrCreate([
                'config_id' => $domain->config_id,
            ]);


            // Step 4: Create the accounting data
            $accountingConfig = AccountingModel::updateOrCreate([
                'domain_id' => $entity->id,
                'financial_start_date' =>  now(),
                'financial_end_date' =>  now(),
            ]);

            // Step 4: Create the accounting data
            NbrVatConfigModel::updateOrCreate([
                'domain_id' => $entity->id,
            ]);

            // Step 5: Create the Production data
            ProductionConfig::updateOrCreate([
                'domain_id' => $entity->id,
            ]);

            $getProductType = UtilitySettingModel::getEntityDropdown('product-type');
            if (count($getProductType) > 0) {
                // If no inventory config found, return JSON response.
                if (!$config) {
                    DB::rollBack();
                    $response = new Response();
                    $response->headers->set('Content-Type', 'application/json');
                    $response->setContent(json_encode([
                        'message' => 'Inventory config not found',
                        'status' => Response::HTTP_NOT_FOUND,
                    ]));
                    $response->setStatusCode(Response::HTTP_OK);
                    return $response;
                }

                // Loop through each product type and either find or create inventory setting.
                foreach ($getProductType as $type) {
                    // If the inventory setting is not found, create a new one.
                    InventorySettingModel::create([
                        'config_id' => $config->id,
                        'setting_id' => $type->id,
                        'name' => $type->name,
                        'slug' => $type->slug,
                        'parent_slug' => 'product-type',
                        'is_production' => in_array($type->slug,
                            ['post-production', 'mid-production', 'pre-production']) ? 1 : 0,
                    ]);
                }

                TransactionModeModel::updateOrCreate([
                    'config_id' => $accountingConfig->id],[
                    'account_owner' => 'Cash',
                    'authorised' => 'Cash',
                    'name' => 'Cash',
                    'short_name' => 'Cash',
                    'slug' => 'cash',
                    'is_selected' => true,
                    'path' => null,
                    'account_type' => 'Current',
                    'method_id' => 20,
                    'is_private' => 1,
                    'status' => true
                ]);
            }


            // Commit all database operations
            DB::commit();
            $em->getRepository(AccountHead::class)->generateAccountHead($accountingConfig->id);
            $em->getRepository(AccountVoucher::class)->resetVoucher($accountingConfig->id);


            $entity = DomainModel::with('accountConfig',
                'accountConfig.capital_investment','accountConfig.account_cash','accountConfig.account_bank','accountConfig.account_mobile','accountConfig.account_user','accountConfig.account_vendor','accountConfig.account_customer','accountConfig.account_product_group','accountConfig.account_category',
                'accountConfig.voucher_stock_opening','accountConfig.voucher_purchase','accountConfig.voucher_sales','accountConfig.voucher_purchase_return','accountConfig.voucher_stock_reconciliation',
                'productionConfig','gstConfig','inventoryConfig','inventoryConfig.configPurchase','inventoryConfig.configSales','inventoryConfig.configProduct','inventoryConfig.configDiscount','inventoryConfig.configVat','inventoryConfig.businessModel','inventoryConfig.currency')->find($id);


            // Return the response
            $service = new JsonRequestResponse();
            return $service->returnJosnResponse($entity);

        } catch (Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();

            // Optionally log the exception for debugging purposes
            \Log::error('Error storing domain and related data: ' . $e->getMessage());

            // Return an error response
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'An error occurred while saving the domain and related data.',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }

        return response()->json(
            [
                'message' => 'Domain reset successfully',
                'status' => Response::HTTP_OK
            ],
            Response::HTTP_OK
        );
    }


    /**
     * Reset the specified resource from storage.
     */
    public function users(Request $request)
    {

        $data = UserModel::getRecordsForDomain($request);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities']
        ]));
        return $response->setStatusCode(Response::HTTP_OK);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteData($id)
    {

        $service = new JsonRequestResponse();
        $userData = DomainModel::getDomainConfigData($id);
        if($userData['acc_config']){
            AccountingModel::find($userData['acc_config'])->delete();
        }
        if($userData['pro_config']) {
            ProductionConfig::find($userData['pro_config'])->delete();
        }
        if($userData['nbr_config']) {
            NbrVatConfigModel::find($userData['nbr_config'])->delete();
        }
        if($userData['config_id']) {
            ConfigModel::find($userData['config_id'])->delete();
        }
        TransactionModeModel::whereNull('config_id')->delete();
        DomainModel::find($id)->delete();
        $entity = ['message'=>'delete'];
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

}
