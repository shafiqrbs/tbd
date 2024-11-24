<?php

namespace Modules\Domain\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Entities\AccountHead;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Entities\Customer;
use Modules\Core\App\Models\CustomerModel;
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

    public function store(BranchRequest $request , EntityManager $em)
    {
        $data = $request->validated();

        dump($data);

        /*// Start the transaction
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
                'password' => bcrypt($password),
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
                        'parent_slug' => 'product_type',
                        'is_production' => in_array($type->slug,
                            ['post-production', 'mid-production', 'pre-production']) ? 1 : 0,
                    ]);
                }
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
        }*/
    }
}
