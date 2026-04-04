<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Modules\Accounting\App\Models\TransactionModeModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\UserRequest;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\UserProfileModel;
use Modules\Core\App\Models\UserRoleGroupModel;
use Modules\Core\App\Models\UserRoleModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Inventory\App\Models\CategoryModel;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\ParticularModel;
use Modules\Inventory\App\Models\SettingModel;
use Modules\Inventory\App\Models\StockItemModel;

class FlutterController extends Controller
{

    public function onboard()
    {
        return response()->json([
            'status' => 200,
            'message' => 'success',
            'data' => [
                "video_url" => "https://youtu.be/CPclGyYCGtY?si=12eJJnM7yUQvh7RU",
                "terms_condition" => "<p>1. Acceptance of Terms ...</p>",
                "aboutus" => "<p>Founded in 2018, POSKeeper Global IT began its journey...</p>",
                "onboard" => 1,
                "demo" => [
                    [
                        "app_slug" => "medicine",
                        "name" => "Medicine",
                        "title" => "Medicine Management System",
                        "license_no" => "01828148148",
                        "active_key" => "0915332674",
                        "username" => "manager",
                        "password" => "manager@123",
                        "content" => "<ul><li><strong>Inventory Tracking:</strong> ...</li></ul>"
                    ],
                    [
                        "app_slug" => "restaurant",
                        "name" => "Restaurant",
                        "title" => "Restaurant Management System",
                        "license_no" => "01852892044",
                        "active_key" => "1551378444",
                        "username" => "jp_rubel",
                        "password" => "@123456",
                        "content" => "<ul><li><strong>Table Management:</strong> ...</li></ul>"
                    ],
                    [
                        "app_slug" => "invoice",
                        "name" => "Business Invoice",
                        "title" => "Trading Business POS System",
                        "license_no" => "01706250725",
                        "active_key" => "1577633987",
                        "username" => "rpf_kajol",
                        "password" => "@123456",
                        "content" => "<ul><li><strong>Multi-Warehouse Management:</strong> ...</li></ul>"
                    ]
                ]
            ]
        ], 200);
    }
    public function splash(Request $request){
        $licenseKey = $request->license_key;
        $activeKey = $request->active_key;
        if (empty($licenseKey) || empty($activeKey)) {
            return response()->json([
                'status' => 404,
                'message' => 'License and Active Key are required.',
            ], 404);
        }

        $findDomain = DomainModel::where('mobile', $licenseKey)->where('unique_code',$activeKey)
            ->select(['dom_domain.id','acc_config.id as acc_config','inv_config.id as config_id'])
            ->leftjoin('inv_config','inv_config.domain_id','=','dom_domain.id')
            ->leftjoin('acc_config','acc_config.domain_id','=','dom_domain.id')
            ->first();

        $findDomain = DomainModel::where('license_no', $licenseKey)->where('unique_code',$activeKey)
            ->with('accountConfig',
                'accountConfig.capital_investment','accountConfig.account_cash','accountConfig.account_bank','accountConfig.account_mobile','accountConfig.account_user','accountConfig.account_vendor','accountConfig.account_customer','accountConfig.account_product_group','accountConfig.account_category',
                'accountConfig.voucher_stock_opening','accountConfig.voucher_purchase','accountConfig.voucher_sales','accountConfig.voucher_purchase_return','accountConfig.voucher_stock_reconciliation',
                'productionConfig','gstConfig','inventoryConfig','inventoryConfig.configPurchase','inventoryConfig.configSales','inventoryConfig.configProduct','inventoryConfig.configDiscount','inventoryConfig.configVat','inventoryConfig.businessModel','inventoryConfig.currency')->first();


        if (!$findDomain) {
            return response()->json([
                'status' => 404,
                'message' => 'Invalid License Key or Active Key.',
            ], 404);
        }


        $domainData = [
          'global_id' => $findDomain->id,
          'acc_config' => $findDomain->accountConfig->id,
          'config_id' => $findDomain->inventoryConfig->id,
        ];

        $allUsers = UserModel::getRecordsForLocalStorage('',$domainData)['entities'];

        $allCustomers = CustomerModel::getRecordsForLocalStorage($domainData,$request)['entities'];

        $vendors = VendorModel::getRecordsForLocalStorage($request,$domainData);
        $brands = CategoryModel::getBrandFlutter($domainData);
        $categories = CategoryModel::getCategoryFlutter($domainData);
        $units = ParticularModel::getProductUnitFlutter($domainData,'product-unit');
    //    $allVendors = isset($vendors['entities']) ? $vendors['entities'] : [];
        $transactionMode = TransactionModeModel::getRecordsForLocalStorage($request,$domainData)['entities'];
        $allTransactionMode = isset($transactionMode['entities']) ? $transactionMode['entities'] : [];
        $stockItem = StockItemModel::getPosStockItem($domainData);

        return response()->json([
            'status' => 200,
            'message' => 'success',
            'data' => [
                'brands' => $brands,
                'categories' =>$categories,
                'expense_category' => '',
                'units' => $units,
                'users' => $allUsers,
                'customers' => $allCustomers,
                'vendors' => $vendors,
                'transaction_methods' => $transactionMode,
                'setup' => $findDomain,
                'stocks' => $stockItem,
            ]
        ], 200);
    }

    private function getDomainConfig($id,$globalId)
    {
//        $entity = ConfigModel::with('domain.productionConfig','currency','businessModel','pos_invoice_mode','configProduct','configPurchase','configSales','configDiscount')->find($id);
        $entity = DomainModel::with('inventoryConfig','inventoryConfig.configPurchase','inventoryConfig.configSales','inventoryConfig.configProduct','inventoryConfig.configDiscount')->find($globalId);
        /*$inv_product_type = SettingModel::where('parent_slug', 'product-type')->where('config_id', $id)
            ->select('id', 'slug', 'name', 'status')
            ->get()
            ->toArray();

        $entity['child_domain_exists'] = VendorModel::where('sub_domain_id', $globalId)->exists();
        if ($inv_product_type) {
            foreach ($inv_product_type as $value) {
                switch ($value['slug']) {
                    case 'raw-materials':
                        $entity['raw_materials'] = $value['status'];
                        break;
                    case 'stockable':
                        $entity['stockable'] = $value['status'];
                        break;
                    case 'post-production':
                        $entity['post_production'] = $value['status'];
                        break;
                    case 'mid-production':
                        $entity['mid_production'] = $value['status'];
                        break;
                    case 'pre-production':
                        $entity['pre_production'] = $value['status'];
                        break;
                }
            }
        }
        if (!$entity){
            $entity = 'Data not found';
        }*/
        return $entity;
    }



    private function getInventoryConfig($id,$globalId)
    {
        $entity = ConfigModel::with('domain','currency','businessModel','pos_invoice_mode','configProduct','configPurchase','configSales','configDiscount')->find($id);
        $inv_product_type = SettingModel::where('parent_slug', 'product-type')->where('config_id', $id)
            ->select('id', 'slug', 'name', 'status')
            ->get()
            ->toArray();

        $entity['child_domain_exists'] = VendorModel::where('sub_domain_id', $globalId)->exists();
        if ($inv_product_type) {
            foreach ($inv_product_type as $value) {
                switch ($value['slug']) {
                    case 'raw-materials':
                        $entity['raw_materials'] = $value['status'];
                        break;
                    case 'stockable':
                        $entity['stockable'] = $value['status'];
                        break;
                    case 'post-production':
                        $entity['post_production'] = $value['status'];
                        break;
                    case 'mid-production':
                        $entity['mid_production'] = $value['status'];
                        break;
                    case 'pre-production':
                        $entity['pre_production'] = $value['status'];
                        break;
                }
            }
        }
        if (!$entity){
            $entity = 'Data not found';
        }
        return $entity;
    }


}
