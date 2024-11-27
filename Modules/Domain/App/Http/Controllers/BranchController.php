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
use Modules\Domain\App\Http\Requests\BranchRequest;
use Modules\Domain\App\Http\Requests\DomainRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\CurrencyModel;
use Modules\Domain\App\Models\DomainModel;
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
                $getCustomerPriceData = CustomerModel::where('domain_id', $domain['id'])
                    ->select('discount_percent', 'bonus_percent', 'monthly_target_amount')
                    ->first();

                if ($getCustomerPriceData) {
                    $domain['prices'] = [
                        ['discount_percent' => $getCustomerPriceData->discount_percent,'label'=> 'Discount Percent'],
                        ['bonus_percent' => $getCustomerPriceData->bonus_percent,'label'=> 'Bonus Percent'],
                        ['monthly_target_amount' => $getCustomerPriceData->monthly_target_amount,'label'=> 'Monthly Target Amount'],
                    ];
                } else {
                    $domain['prices'] = [
                        ['discount_percent' => null,'label'=> 'Discount Percent'],
                        ['bonus_percent' => null,'label'=> 'Bonus Percent'],
                        ['monthly_target_amount' => null,'label'=> 'Monthly Target Amount'],
                    ];
                }
                $domain['categories'] = CategoryModel::getCategoryDropdown($this->domain);
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

}
