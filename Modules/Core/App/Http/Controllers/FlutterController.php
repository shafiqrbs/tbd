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

    public function themes()
    {
        return response()->json([
            'status' => 200,
            'message' => 'success',
            'data' => [
                [
                    "theme_name" => "Default",
                    "theme_color" => "0xFF008000",
                    "primary_color_900" => "0xFF004021",
                    "primary_color_800" => "0xFF00542B",
                    "primary_color_700" => "0xFF006D38",
                    "primary_color_600" => "0xFF008B48",
                    "primary_color_500" => "0xFF00994F",
                    "primary_color_400" => "0xFF33AD72",
                    "primary_color_300" => "0xFF54BB89",
                    "primary_color_200" => "0xFF8AD0AE",
                    "primary_color_100" => "0xFFB0DFC8",
                    "primary_color_50" => "0xFFF4F4F5",

                    "secondary_color_900" => "0xFF141D21",
                    "secondary_color_800" => "0xFF1A252B",
                    "secondary_color_700" => "0xFF223038",
                    "secondary_color_600" => "0xFF2C3E48",
                    "secondary_color_500" => "0xFF30444F",
                    "secondary_color_400" => "0xFF596972",
                    "secondary_color_300" => "0xFF748289",
                    "secondary_color_200" => "0xFFA0A9AE",
                    "secondary_color_100" => "0xFFBFC5C8",
                    "secondary_color_50" => "0xFFFDFDFD",

                    "text_color_900" => "0xFF000000",
                    "text_color_800" => "0xFF0D0D0D",
                    "text_color_700" => "0xFF1A1A1A",
                    "text_color_600" => "0xFF262626",
                    "text_color_500" => "0xFF000000",
                    "text_color_400" => "0xFF333333",
                    "text_color_300" => "0xFF595959",
                    "text_color_200" => "0xFF737373",
                    "text_color_100" => "0xFF8C8C8C",
                    "text_color_50" => "0xFFE6E6E6"
                ],

                [
                    "theme_name" => "Black",
                    "theme_color" => "0xFF000000",
                    "primary_color_900" => "0xFF151515",
                    "primary_color_800" => "0xFF1c1c1c",
                    "primary_color_700" => "0xFF242424",
                    "primary_color_600" => "0xFF2e2e2e",
                    "primary_color_500" => "0xFF333333",
                    "primary_color_400" => "0xFF5c5c5c",
                    "primary_color_300" => "0xFF767676",
                    "primary_color_200" => "0xFFa1a1a1",
                    "primary_color_100" => "0xFFc0c0c0",
                    "primary_color_50" => "0xFFebebeb",

                    "secondary_color_900" => "0xFFF5F5F5",
                    "secondary_color_800" => "0xFFF0F0F0",
                    "secondary_color_700" => "0xFFEBEBEB",
                    "secondary_color_600" => "0xFFE6E6E6",
                    "secondary_color_500" => "0xFFE0E0E0",
                    "secondary_color_400" => "0xFFDBDBDB",
                    "secondary_color_300" => "0xFFD6D6D6",
                    "secondary_color_200" => "0xFFD1D1D1",
                    "secondary_color_100" => "0xFFCCCCCC",
                    "secondary_color_50" => "0xFFC7C7C7",

                    "text_color_900" => "0xFFFFFFFF",
                    "text_color_800" => "0xFFF9F9F9",
                    "text_color_700" => "0xFFF2F2F2",
                    "text_color_600" => "0xFFEBEBEB",
                    "text_color_500" => "0xFFE4E4E4",
                    "text_color_400" => "0xFFDDDDDD",
                    "text_color_300" => "0xFFD6D6D6",
                    "text_color_200" => "0xFFCFCFCF",
                    "text_color_100" => "0xFFC8C8C8",
                    "text_color_50" => "0xFFC1C1C1"
                ],

                [
                    "theme_name" => "Red",
                    "theme_color" => "0xFF000000",
                    "primary_color_900" => "0xFF9A1F33",
                    "primary_color_800" => "0xFFB73A3D",
                    "primary_color_700" => "0xFFCA5C45",
                    "primary_color_600" => "0xFFDB7E4C",
                    "primary_color_500" => "0xFFE8A254",
                    "primary_color_400" => "0xFFECB85A",
                    "primary_color_300" => "0xFFEDA85F",
                    "primary_color_200" => "0xFFEFBE67",
                    "primary_color_100" => "0xFFF0C66F",
                    "primary_color_50" => "0xFFF5D77A",

                    "secondary_color_900" => "0xFF38364F",
                    "secondary_color_800" => "0xFF4A4F59",
                    "secondary_color_700" => "0xFF617267",
                    "secondary_color_600" => "0xFF788476",
                    "secondary_color_500" => "0xFF8F8A84",
                    "secondary_color_400" => "0xFF9A9A91",
                    "secondary_color_300" => "0xFFB0B0A0",
                    "secondary_color_200" => "0xFFBEBEAD",
                    "secondary_color_100" => "0xFFD1D1B6",
                    "secondary_color_50" => "0xFFE1E1C5",

                    "text_color_900" => "0xFF1A1A1A",
                    "text_color_800" => "0xFF333333",
                    "text_color_700" => "0xFF4D4D4D",
                    "text_color_600" => "0xFF666666",
                    "text_color_500" => "0xFF808080",
                    "text_color_400" => "0xFFA6A6A6",
                    "text_color_300" => "0xFFBFBFBF",
                    "text_color_200" => "0xFFD1D1D1",
                    "text_color_100" => "0xFFE6E6E6",
                    "text_color_50" => "0xFFF2F2F2"
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
