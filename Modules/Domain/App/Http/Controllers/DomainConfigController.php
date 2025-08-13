<?php

namespace Modules\Domain\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\SettingModel;
use Modules\Core\App\Models\SettingTypeModel;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Hospital\App\Models\HospitalConfigModel;
use Modules\Inventory\App\Entities\Config;
use Modules\Inventory\App\Models\ConfigDiscountModel;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\ConfigProductModel;
use Modules\Inventory\App\Models\ConfigPurchaseModel;
use Modules\Inventory\App\Models\ConfigRequsitionModel;
use Modules\Inventory\App\Models\ConfigSalesModel;
use Modules\Inventory\App\Models\ConfigVatModel;
use Modules\NbrVatTax\App\Models\NbrVatConfigModel;
use Modules\Production\App\Models\ProductionConfig;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class DomainConfigController extends Controller
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

    public function domainConfig()
    {
        $entity = DomainModel::with(['accountConfig',
            'accountConfig.capital_investment','accountConfig.account_cash','accountConfig.account_bank','accountConfig.account_mobile','accountConfig.account_user','accountConfig.account_vendor','accountConfig.account_customer','accountConfig.account_product_group','accountConfig.account_category',
            'accountConfig.voucher_stock_opening','accountConfig.voucher_purchase','accountConfig.voucher_sales','accountConfig.voucher_purchase_return','accountConfig.voucher_stock_reconciliation',
            'productionConfig','gstConfig','inventoryConfig','hospitalConfig','inventoryConfig.configPurchase','inventoryConfig.configSales','inventoryConfig.configProduct','inventoryConfig.configDiscount','inventoryConfig.configVat','inventoryConfig.businessModel','inventoryConfig.currency',
            'hospitalConfig.admission_fee:id,name as admission_fee_name,price as admission_fee_price',
            'hospitalConfig.opd_ticket_fee:id,name as opd_ticket_fee_name,price as opd_ticket_fee_price',
            'hospitalConfig.emergency_fee:id,name as emergency_fee_name,price as emergency_fee_price',
            'hospitalConfig.ot_fee:id,name as ot_fee_name,price as ot_fee_price',
        ])->find($this
            ->domain['global_id']);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);

    }

    public function domainConfigById($id)
    {
        $entity = DomainModel::with(['accountConfig',
            'accountConfig.capital_investment','accountConfig.account_cash','accountConfig.account_bank','accountConfig.account_mobile','accountConfig.account_user','accountConfig.account_vendor','accountConfig.account_customer','accountConfig.account_product_group','accountConfig.account_category',
            'accountConfig.voucher_stock_opening','accountConfig.voucher_purchase','accountConfig.voucher_sales','accountConfig.voucher_purchase_return','accountConfig.voucher_stock_reconciliation',
            'productionConfig','gstConfig','inventoryConfig','hospitalConfig','inventoryConfig.configPurchase','inventoryConfig.configSales','inventoryConfig.configProduct','inventoryConfig.configDiscount','inventoryConfig.configVat','inventoryConfig.businessModel',
            'inventoryConfig.currency',
            'hospitalConfig.admission_fee:id,name as admission_fee_name,price as admission_fee_price',
            'hospitalConfig.opd_ticket_fee:id,name as opd_ticket_fee_name,price as opd_ticket_fee_price',
            'hospitalConfig.emergency_fee:id,name as emergency_fee_name,price as emergency_fee_price',
            'hospitalConfig.ot_fee:id,name as ot_fee_name,price as ot_fee_price',
        ])->find($id);
        return $entity;
    }

    public function domainConfigurationForm(Request $request,$id)
    {

        DB::beginTransaction();
        $domain = UserModel::getDomainData($id);
        $config = $domain['config_id'];
        $entity = ConfigModel::find($config);
        $domainConfig = DomainModel::find($id);

        try {
            $data = $request->all();
            $entity->update($data);
            $configData =array(
                'name'=> $data['shop_name'],
                'business_model_id'=> $data['business_model_id'],
            );
            $domainConfig->update($configData);
            DB::commit();
            $service = new JsonRequestResponse();
            $return = $service->returnJosnResponse($this->domainConfigById($id));
            return $return;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Resource not found',
                'status'  => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Operation failed: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function inventoryConfig(Request $request,$id)
    {

        DB::beginTransaction();
        $domain = UserModel::getDomainData($id);
        $config = $domain['config_id'];
        $entity = ConfigModel::find($config);
        try {
            $entity->update($request->all());
            DB::commit();
            $service = new JsonRequestResponse();
            $return = $service->returnJosnResponse($this->domainConfigById($id));
            return $return;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Resource not found',
                'status'  => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Operation failed: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function inventoryProductConfig(Request $request,$id)
    {

        $domain = UserModel::getDomainData($id);
        $config = $domain['config_id'];
        $entity = ConfigProductModel::updateOrCreate([
            'config_id' => $config,
        ]);
        DB::beginTransaction();
        try {
            $entity->update($request->all());
            DB::commit();
            $service = new JsonRequestResponse();
            $return = $service->returnJosnResponse($this->domainConfigById($id));
            return $return;

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Resource not found',
                'status'  => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Operation failed: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }


    }

    public function inventoryPurchaseConfig(Request $request,$id)
    {

        $domain = UserModel::getDomainData($id);
        $config = $domain['config_id'];
        $entity = ConfigPurchaseModel::updateOrCreate([
            'config_id' => $config,
        ]);
        DB::beginTransaction();
        try {
            $entity->update($request->all());
            DB::commit();
            $service = new JsonRequestResponse();
            $return = $service->returnJosnResponse($this->domainConfigById($id));
            return $return;

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Resource not found',
                'status'  => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Operation failed: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function inventorySalesConfig(Request $request,$id)
    {
        $domain = UserModel::getDomainData($id);

        $config = $domain['config_id'];
        $entity = ConfigSalesModel::updateOrCreate([
            'config_id' => $config,
        ]);

        DB::beginTransaction();
        try {
            $entity->update($request->all());
            DB::commit();
            $service = new JsonRequestResponse();
            $return = $service->returnJosnResponse($this->domainConfigById($id));
            return $return;

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Resource not found',
                'status'  => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Operation failed: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function inventoryVatConfig(Request $request,$id)
    {

        $domain = UserModel::getDomainData($id);

        $config = $domain['config_id'];
        $entity = ConfigVatModel::updateOrCreate([
            'config_id' => $config,
        ]);
        DB::beginTransaction();
        try {
            $entity->update($request->all());
            DB::commit();
            $service = new JsonRequestResponse();
            $return = $service->returnJosnResponse($this->domainConfigById($id));
            return $return;

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Resource not found',
                'status'  => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Operation failed: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function inventoryDiscountConfig(Request $request,$id)
    {

        $domain = UserModel::getDomainData($id);
        $config = $domain['config_id'];
        $entity = ConfigDiscountModel::updateOrCreate([
            'config_id' => $config,
        ]);
        DB::beginTransaction();
        try {
            $entity->update($request->all());
            DB::commit();
            $service = new JsonRequestResponse();
            $return = $service->returnJosnResponse($this->domainConfigById($id));
            return $return;

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Resource not found',
                'status'  => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Operation failed: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function inventoryRequisitionConfig(Request $request,$id)
    {

        $domain = UserModel::getDomainData($id);
        $config = $domain['config_id'];
        $entity = ConfigRequsitionModel::updateOrCreate([
            'config_id' => $config,
        ]);
        DB::beginTransaction();
        try {
            $entity->update($request->all());
            DB::commit();
            $service = new JsonRequestResponse();
            $return = $service->returnJosnResponse($this->domainConfigById($id));
            return $return;

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Resource not found',
                'status'  => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Operation failed: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function inventoryPosConfig(Request $request,$id)
    {

        $entity = ConfigModel::updateOrCreate([
            'domain_id' =>$id,
        ]);
        DB::beginTransaction();
        try {
            $entity->update($request->all());
            DB::commit();
            $service = new JsonRequestResponse();
            $return = $service->returnJosnResponse($this->domainConfigById($id));
            return $return;

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Resource not found',
                'status'  => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Operation failed: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function accountingConfig(Request $request,$id)
    {

        $entity = AccountingModel::updateOrCreate([
            'domain_id' => $id,
        ]);
        DB::beginTransaction();
        try {
            $entity->update($request->all());
            DB::commit();
            $service = new JsonRequestResponse();
            $return = $service->returnJosnResponse($this->domainConfigById($id));
            return $return;

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Resource not found',
                'status'  => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Operation failed: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function productionConfig(Request $request,$id)
    {

        $entity = ProductionConfig::updateOrCreate([
            'domain_id' => $id,
        ]);
        DB::beginTransaction();
        try {
            $entity->update($request->all());
            DB::commit();
            $service = new JsonRequestResponse();
            $return = $service->returnJosnResponse($this->domainConfigById($id));
            return $return;

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Resource not found',
                'status'  => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Operation failed: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function vatConfig(Request $request,$id)
    {

        $entity = NbrVatConfigModel::updateOrCreate([
            'domain_id' => $id,
        ]);
        DB::beginTransaction();
        try {
            $entity->update($request->all());
            DB::commit();
            $service = new JsonRequestResponse();
            $return = $service->returnJosnResponse($this->domainConfigById($id));
            return $return;

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Resource not found',
                'status'  => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Operation failed: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function hospitalConfig(Request $request,$id)
    {

        $entity = HospitalConfigModel::updateOrCreate([
            'domain_id' => $id,
        ]);

        DB::beginTransaction();
        try {

            $domain = UserModel::getDomainData($id);
            HospitalConfigModel::investigationMasterReport($domain);

            $entity->update($request->all());
            DB::commit();

            $service = new JsonRequestResponse();
            $return = $service->returnJosnResponse($this->domainConfigById($id));
            return $return;

        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Operation failed: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function hospitalResetConfig(Request $request,$id)
    {

        $entity = HospitalConfigModel::updateOrCreate([
            'domain_id' => $id,
        ]);
        DB::beginTransaction();
        try {

            $domain = UserModel::getDomainData($id);
            HospitalConfigModel::resetMasterData($domain);
            HospitalConfigModel::investigationMasterReport($domain);
            DB::commit();
            $service = new JsonRequestResponse();
            $return = $service->returnJosnResponse($this->domainConfigById($id));
            return $return;

        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Resource not found',
                'status'  => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Operation failed: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function resetConfig($id)
    {
        $domain = UserModel::getDomainData($id);

        ConfigModel::resetConfig($domain->config_id);

        if($domain->inv_config_product){
            ConfigProductModel::resetConfig($domain->inv_config_product);
        }

        if($domain->inv_config_discount){
            ConfigDiscountModel::resetConfig($domain->inv_config_discount);
        }

        if($domain->inv_config_purchase){
            ConfigPurchaseModel::resetConfig($domain->inv_config_purchase);
        }

        if($domain->inv_config_sales){
            ConfigSalesModel::resetConfig($domain->inv_config_sales);
        }

        if($domain->inv_config_vat){
            ConfigVatModel::resetConfig($domain->inv_config_sales);
        }

        if($domain->acc_config){
            AccountingModel::resetConfig($domain->acc_config);
        }

        if($domain->pro_config){
            ProductionConfig::resetConfig($domain->pro_config);
        }

        if($domain->nbr_config){
             NbrVatConfigModel::resetConfig($domain->nbr_config);
        }

        // Step 4: Create the accounting data
        ConfigProductModel::updateOrCreate([
            'config_id' => $domain->config_id,
        ]);

        // Step 4: Create the accounting data
        ConfigDiscountModel::updateOrCreate([
            'config_id' => $domain->config_id,
        ]);

        // Step 4: Create the accounting data
        ConfigSalesModel::updateOrCreate([
            'config_id' => $domain->config_id,
        ]);

        // Step 4: Create the accounting data
        ConfigPurchaseModel::updateOrCreate([
            'config_id' => $domain->config_id,
        ]);

        // Step 4: Create the accounting data
        ConfigVatModel::updateOrCreate([
            'config_id' => $domain->config_id,
        ]);


        $getCoreSettingTypeId = SettingTypeModel::where('slug', 'customer-group')->first();
        SettingModel::updateOrCreate(
            [
                'domain_id' => $domain->domain_id,
                'setting_type_id' => $getCoreSettingTypeId->id,
                'name' => 'Domain',
                'is_system' => true,
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
                'domain_id' => $domain->domain_id,
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

        $service = new JsonRequestResponse();
        $return = $service->returnJosnResponse($this->domainConfigById($id));
        return $return;

    }


}
