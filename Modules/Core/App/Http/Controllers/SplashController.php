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
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\SettingModel;
use Modules\Inventory\App\Models\StockItemModel;

class SplashController extends Controller
{

    public function splashInfo(Request $request){
        $licenseKey = $request->license_key;
        $activeKey = $request->active_key;

        if (empty($licenseKey) || empty($activeKey)) {
            return response()->json([
                'status' => 404,
                'message' => 'License Key and Active Key are required.',
            ], 404);
        }

        $findDomain = DomainModel::where('mobile', $licenseKey)->where('unique_code',$activeKey)
            ->select(['dom_domain.id','acc_config.id as acc_config','inv_config.id as config_id'])
            ->leftjoin('inv_config','inv_config.domain_id','=','dom_domain.id')
            ->leftjoin('acc_config','acc_config.domain_id','=','dom_domain.id')
            ->first();

        if (!$findDomain) {
            return response()->json([
                'status' => 404,
                'message' => 'Invalid License Key or Active Key.',
            ], 404);
        }

        $domainData = [
          'global_id' => $findDomain->id,
          'acc_config' => $findDomain->acc_config,
          'config_id' => $findDomain->config_id,
        ];

        $allUsers = UserModel::getRecordsForLocalStorage('',$domainData)['entities'];
        $allCustomers = CustomerModel::getRecordsForLocalStorage($domainData,$request)['entities'];
        $allVendors = VendorModel::getRecordsForLocalStorage($request,$domainData)['entities'];
        $allTransactionMode = TransactionModeModel::getRecordsForLocalStorage($request,$domainData)['entities'];
        $inventoryConfig = $this->getInventoryConfig($domainData['config_id'],$domainData['global_id']);
        $stockItem = StockItemModel::getStockItem($domainData);


        return response()->json([
            'status' => 200,
            'message' => 'success',
            'data' => [
                'users' => $allUsers,
                'customers' => $allCustomers,
                'vendors' => $allVendors,
                'transaction_modes' => $allTransactionMode,
                'inventory_config' => $inventoryConfig,
                'stock_item' => $stockItem,
            ]
        ], 200);
    }

    private function getInventoryConfig($id,$globalId)
    {
        $entity = ConfigModel::with('domain','currency','businessModel','pos_invoice_mode')->find($id);

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
