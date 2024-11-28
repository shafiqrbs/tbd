<?php

namespace Modules\Domain\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Accounting\App\Entities\AccountHead;
use Modules\Accounting\App\Entities\Config;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Entities\Customer;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Domain\App\Entities\DomainChild;
use Modules\Domain\App\Entities\GlobalOption;
use Modules\Domain\App\Entities\SubDomain;
use Modules\Domain\App\Http\Requests\BranchPriceUpdateRequest;
use Modules\Domain\App\Http\Requests\BranchRequest;
use Modules\Domain\App\Http\Requests\DomainRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\CurrencyModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Domain\App\Models\SubdomainCategory;
use Modules\Inventory\App\Entities\Setting;
use Modules\Inventory\App\Models\CategoryModel;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Utility\App\Models\SettingModel as UtilitySettingModel;
use Modules\Inventory\App\Models\SettingModel as InventorySettingModel;

class BranchController extends Controller
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

    public function domainForBranch()
    {
        $domains = DomainModel::getDomainsForBranch($this->domain['global_id']);
        $data = [];

        if (count($domains) > 0) {
            foreach ($domains as $domain) {
                // sub domain exists
                $getCustomerPriceData = CustomerModel::where('sub_domain_id', $domain['id'])
                    ->where('domain_id', $this->domain['global_id'])
                    ->select('discount_percent', 'bonus_percent', 'monthly_target_amount','id')
                    ->first();

                if ($getCustomerPriceData) {
                    $domain['customer_id'] = $getCustomerPriceData->id;
                    $domain['is_sub_domain'] = true;
                    $domain['prices'] = [
                        ['discount_percent' => $getCustomerPriceData->discount_percent,'label'=> 'Discount Percent'],
                        ['bonus_percent' => $getCustomerPriceData->bonus_percent,'label'=> 'Bonus Percent'],
                        ['monthly_target_amount' => $getCustomerPriceData->monthly_target_amount,'label'=> 'Monthly Target Amount'],
                    ];
                } else {
                    $domain['customer_id'] = null;
                    $domain['is_sub_domain'] = false;
                    $domain['prices'] = [
                        ['discount_percent' => null,'label'=> 'Discount Percent'],
                        ['bonus_percent' => null,'label'=> 'Bonus Percent'],
                        ['monthly_target_amount' => null,'label'=> 'Monthly Target Amount'],
                    ];
                }
                $domain['categories'] = CategoryModel::getCategoryDropdown($this->domain);

                // get assign category
                $invConfig = ConfigModel::where('domain_id', $domain['id'])->value('id');

                $categories = SubdomainCategory::where('config_id', $invConfig)
                    ->pluck('category_id')
                    ->toArray();

                // Directly map "category_id" to the required format "category_id#domain_id"
                $checkCategory = array_map(fn($categoryId) => $categoryId . '#' . $domain['id'], $categories);

                $domain['check_category'] = $checkCategory;

                /*$invConfig = ConfigModel::where('domain_id', $domain['id'])->value('id');
                $categories = SubdomainCategory::where('config_id', $invConfig)->select('category_id')->get()->toArray();
                $checkCategory = [];
                if (count($categories) > 0) {
                    foreach ($categories as $subCategory) {
                        $checkCategory[] = $subCategory['category_id'].'#'.$domain['id'];
                    }
                }
                $domain['check_category'] = $checkCategory;*/
                $data[] = $domain;
            }
        }

        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => count($data),
            'data' => $data
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }


    /**
     * Store a newly created resource in storage.
     */

    public function store(BranchRequest $request, GeneratePatternCodeService $patternCodeService, JsonRequestResponse $service) {
        // Validate input
        $input = $request->validated();

        DB::beginTransaction();

        try {
            // Fetch the child and parent domains
            $childDomain = DomainModel::findOrFail($input['child_domain_id']);
            $parentDomain = DomainModel::findOrFail($input['parent_domain_id']);

            // Create customer
            $customerData = $this->prepareCustomerData($childDomain, $patternCodeService);
            $customer = CustomerModel::create($customerData);
            $this->ensureCustomerLedger($customer);

            // Create vendor
            $vendorData = $this->prepareVendorData($childDomain, $parentDomain, $patternCodeService);
            $vendor = VendorModel::create($vendorData);
            $this->ensureVendorLedger($vendor, $childDomain->id);

            // Commit transaction
            DB::commit();

            //get customer

            // Return success response
            return $service->returnJosnResponse(CustomerModel::getCustomerDetails($customer->id));
        } catch (\Exception $e) {
            // Rollback transaction on failure
            DB::rollBack();

            // Handle the exception and return error response
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while storing the branch data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function prepareCustomerData($childDomain, $patternCodeService): array
    {
        $code = $this->generateCustomerCode($patternCodeService);

        return [
            'domain_id' => $this->domain['global_id'],
            'customer_unique_id' => "{$this->domain['global_id']}@{$childDomain->mobile}-{$childDomain->name}",
            'code' => $code['code'],
            'name' => $childDomain->name,
            'mobile' => $childDomain->mobile,
            'email' => $childDomain->email,
            'status' => true,
            'address' => $childDomain->address,
            'customer_group_id' => 1, // Default group
            'slug' => Str::slug($childDomain->name),
            'sub_domain_id' => $childDomain->id,
            'customerId' => $code['generateId'], // Generated ID from the pattern code
        ];
    }

    private function generateCustomerCode($patternCodeService): array
    {
        $params = [
            'domain' => $this->domain['global_id'],
            'table' => 'cor_customers',
            'prefix' => 'EMP-',
        ];

        $pattern = $patternCodeService->customerCode($params);

        return $pattern;
    }


    private function prepareVendorData($childDomain, $parentDomain, $patternCodeService): array
    {
        $params = [
            'domain' => $this->domain['global_id'],
            'table' => 'cor_vendors',
            'prefix' => '',
        ];

        $pattern = $patternCodeService->customerCode($params);

        return [
            'name' => $parentDomain->name,
            'company_name' => $parentDomain->company_name,
            'mobile' => $parentDomain->mobile,
            'email' => $parentDomain->email,
            'status' => true,
            'domain_id' => $childDomain->id,
            'sub_domain_id' => $this->domain['global_id'],
            'slug' => Str::slug($childDomain->name),
            'code' => $pattern['code'],
            'vendor_code' => $pattern['generateId'],
        ];
    }

    private function ensureCustomerLedger(CustomerModel $customer)
    {
        $ledgerExist = AccountHeadModel::where('customer_id', $customer->id)
            ->where('config_id', $this->domain['acc_config'])
            ->exists();

        if (!$ledgerExist) {
            AccountHeadModel::insertCustomerLedger($this->domain['acc_config'], $customer);
        }
    }

    private function ensureVendorLedger(VendorModel $vendor, $childDomainId)
    {
        $childAccConfig = DB::table('acc_config')
            ->where('domain_id', $childDomainId)
            ->value('id');

        $ledgerExist = AccountHeadModel::where('vendor_id', $vendor->id)
            ->where('config_id', $childAccConfig)
            ->exists();

        if (!$ledgerExist) {
            AccountHeadModel::insertVendorLedger($childAccConfig, $vendor);
        }
    }


    public function priceUpdate(BranchPriceUpdateRequest $request)
    {
        $input = $request->validated();
        CustomerModel::findOrFail($input['customer_id'])->update([$input['field_name'] => $input['value']]);
        return response()->json(['status'=>200,'success' => true,'data'=>$input]);
    }


    public function categoryUpdate(Request $request)
    {
        try {
            // Validate the input
            $request->validate([
                'value' => ['required', 'string', 'regex:/^\d+#\d+$/']
            ], [
                'value.regex' => 'The value must be in the format "categoryId#domainId".',
            ]);

            // Split and parse the input
            [$categoryId, $domainId] = explode('#', $request->input('value'));

            // Validate numeric IDs
            if (!is_numeric($categoryId) || !is_numeric($domainId)) {
                return response()->json(['status' => 422, 'success' => false, 'message' => 'Category ID or Domain ID is invalid.']);
            }

            // Fetch configuration
            $childAccConfig = ConfigModel::where('domain_id', $domainId)->value('id');
            if (!$childAccConfig) {
                return response()->json(['status' => 404, 'success' => false, 'message' => 'Configuration not found.']);
            }

            // Fetch category and category group
            $category = CategoryModel::find($categoryId);
            if (!$category) {
                return response()->json(['status' => 404, 'success' => false, 'message' => 'Category not found.']);
            }
            $categoryGroup = $category->parent;

            // Fetch or create SubdomainCategory
            $subCategory = SubdomainCategory::where('category_id', $categoryId)
                ->where('config_id', $childAccConfig)
                ->first();

            if (!$subCategory) {
                $subCategory = SubdomainCategory::create([
                    'category_id' => $categoryId,
                    'config_id' => $childAccConfig,
                    'category_group_id' => $categoryGroup ?? null,
                    'created_by_id' => $this->domain['user_id'],
                ]);
            } else {
                $subCategory->update([
                    'category_id' => $categoryId,
                    'config_id' => $childAccConfig,
                    'category_group_id' => $categoryGroup ?? null,
                ]);
            }

            // Success response
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Category updated successfully.',
                'data' => $subCategory,
            ]);

        } catch (\Exception $e) {
            // Log and return error response
            \Log::error('Category update failed: ' . $e->getMessage());
            return response()->json(['status' => 500, 'success' => false, 'message' => 'An internal error occurred.']);
        }
    }



}
