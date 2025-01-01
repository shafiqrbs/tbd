<?php

namespace Modules\Domain\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Accounting\App\Entities\AccountHead;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\Accounting\App\Models\TransactionModeModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Domain\App\Entities\DomainChild;
use Modules\Domain\App\Entities\GlobalOption;
use Modules\Domain\App\Entities\SubDomain;
use Modules\Domain\App\Http\Requests\DomainRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\CurrencyModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Inventory\App\Entities\Config;
use Modules\Inventory\App\Entities\Setting;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\NbrVatTax\App\Models\NbrVatModel;
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

    public function store(DomainRequest $request , EntityManager $em)
    {
        $data = $request->validated();

        // Start the transaction
        DB::beginTransaction();

        try {
            // Step 1: Create the domain the entity
            $data['modules'] = json_encode($data['modules'], JSON_PRETTY_PRINT);
            $entity = DomainModel::create($data);

            // Step 2: Prepare email and password, then create user
            $password = "@123456";
            $email = $data['email'] ?? "{$data['username']}@gmail.com"; // If email is not present, default to username@gmail.com

            UserModel::create([
                'username' => $data['username'],
                'email' => $email,
                'password' => Hash::make($password),
                'domain_id' => $entity->id,
            ]);

            // Step 3: Create the inventory configuration (config)
            $business = UtilitySettingModel::whereSlug('general')->first();
            $currency = CurrencyModel::find(1);

            $config =  ConfigModel::create([
                'domain_id' => $entity->id,
                'currency_id' => $currency->id,
                'zero_stock' => true,
                'business_model_id' => $business->id,
            ]);

            // Step 4: Create the accounting data
            $accountingConfig = AccountingModel::create([
                'domain_id' => $entity->id,
                'financial_start_date' => date('Y-m-d'),
                'financial_end_date' => date('Y-m-d'),
            ]);

            // Step 4: Create the accounting data
            $nbrConfig = NbrVatModel::create([
                'domain_id' => $entity->id,
                'financial_start_date' => date('Y-m-d'),
                'financial_end_date' => date('Y-m-d'),
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
                    'authorised' => 'bKash',
                    'name' => 'Cash',
                    'short_name' => 'Cash',
                    'slug' => 'cash',
                    'path' => null,
                    'account_type' => 'Current',
                    'authorised_mode_id' => 14,
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
     * Remove the specified resource from storage.
     */
    public function resetData($id)
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
            NbrVatModel::find($userData['nbr_config'])->delete();
        }
        if($userData['config_id']) {
            ConfigModel::find($userData['config_id'])->delete();
        }
        DomainModel::find($id)->delete();
        $entity = ['message'=>'delete'];
        $data = $service->returnJosnResponse($entity);
        return $data;
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
            NbrVatModel::find($userData['nbr_config'])->delete();
        }
        if($userData['config_id']) {
            ConfigModel::find($userData['config_id'])->delete();
        }
        DomainModel::find($id)->delete();
        $entity = ['message'=>'delete'];
        $data = $service->returnJosnResponse($entity);
        return $data;
    }
}
