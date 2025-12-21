<?php

namespace Modules\Domain\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\SettingModel;
use Modules\Core\App\Models\SettingTypeModel;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\UserWarehouseModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Domain\App\Http\Requests\B2bCategoryWiseProductRequest;
use Modules\Domain\App\Http\Requests\B2bDomainWiseCategoryRequest;
use Modules\Domain\App\Models\B2BCategoryPriceMatrixModel;
use Modules\Domain\App\Models\B2BStockPriceMatrixModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Domain\App\Models\SubDomainModel;
use Modules\Inventory\App\Models\CategoryModel;
use Modules\Inventory\App\Models\ParticularModel;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class B2bController extends Controller
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

    public function index(Request $request){
        $data = DomainModel::getSubDomain($request);
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

    public function domainInlineUpdate(Request $request, GeneratePatternCodeService $patternCodeService)
    {
        $validated = $request->validate([
            'domain_id'  => 'required|integer',
            'field_name' => 'required|string',
            'value'      => 'required',
        ]);

        DB::beginTransaction();
        try {
            $domainId = $validated['domain_id'];
            $fieldName = $validated['field_name'];
            $value = $validated['value'];
            $globalDomainId = $this->domain['global_id'] ?? null;

            if (!$globalDomainId) {
                throw new \RuntimeException('Global domain context missing');
            }

            $findDomain = DomainModel::findOrFail($domainId);
            $updateData = [];

            switch ($fieldName) {
                case 'domain_type':
                    $subDomain = SubDomainModel::firstOrCreate(
                        ['domain_id' => $globalDomainId, 'sub_domain_id' => $domainId],
                        ['status' => 0]
                    );
                    $subDomain->update(['domain_type' => $value]);
                    break;

                case 'status':
                    $subDomain = SubDomainModel::where([
                        'domain_id' => $globalDomainId,
                        'sub_domain_id' => $domainId
                    ])->firstOrFail();

                    $childDomain = DomainModel::findOrFail($subDomain->sub_domain_id);
                    $parentDomain = DomainModel::findOrFail($subDomain->domain_id);

                    if (!$subDomain->customer_id) {
                        $customer = $this->handleEntity(
                            CustomerModel::class,
                            [
                                'sub_domain_id' => $subDomain->sub_domain_id,
                                'domain_id' => $subDomain->domain_id
                            ],
                            true,
                            fn() => $this->prepareCustomerData($childDomain, $patternCodeService)
                        );
                        $this->ensureCustomerLedger($customer);

                        $updateData['customer_id'] = $customer->id;
                    }

                    if (!$subDomain->vendor_id) {
                        $customer = $customer ?? CustomerModel::find($subDomain->customer_id);
                        $vendor = $this->handleEntity(
                            VendorModel::class,
                            [
                                'sub_domain_id' => $subDomain->sub_domain_id,
                                'domain_id' => $subDomain->domain_id
                            ],
                            true,
                            fn() => $this->prepareVendorData($childDomain, $parentDomain, $patternCodeService, $customer)
                        );
                        $this->ensureVendorLedger($vendor);
                        $updateData['vendor_id'] = $vendor->id;
                    }

                    $updateData['status'] = $value;
                    $subDomain->update($updateData);
                    break;

                default:
                    throw new \InvalidArgumentException('Field not supported');
            }

            DB::commit();
            return response()->json([
                'message' => 'Success',
                'status'  => ResponseAlias::HTTP_OK,
                'data'    => $subDomain
            ]);

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
    public function domainInlineUpdateCategory(Request $request)
    {
        $validated = $request->validate([
            'id'  => 'required|integer',
            'field_name' => 'required|string',
            'value'      => 'required',
        ]);

        DB::beginTransaction();
        try {
            $id = $validated['id'];
            $fieldName = $validated['field_name'];
            $value = $validated['value'];
            $globalDomainId = $this->domain['global_id'] ?? null;

            if (!$globalDomainId) {
                throw new \RuntimeException('Global domain context missing');
            }

            $finaCategoryMatrix = B2BCategoryPriceMatrixModel::findOrFail($id);
            switch ($fieldName) {
                case 'mrp_percent':
                    $finaCategoryMatrix->update(['mrp_percent' => $value,'not_process'=>1]);
                    break;

                case 'percent_mode':
                    $finaCategoryMatrix->update(['percent_mode' => $value,'not_process'=>1]);
                    break;

                case 'purchase_percent':
                    $finaCategoryMatrix->update(['purchase_percent' => $value,'not_process'=>1]);
                    break;

                case 'bonus_percent':
                    $finaCategoryMatrix->update(['bonus_percent' => $value,'not_process'=>1]);
                    break;

                default:
                    throw new \InvalidArgumentException('Field not supported');
            }

            DB::commit();
            return response()->json([
                'message' => 'Success',
                'status'  => ResponseAlias::HTTP_OK
            ]);

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
    public function domainInlineUpdateProduct(Request $request)
    {
        $validated = $request->validate([
            'stock_id'  => 'required|integer',
            'b2b_id'  => 'required|integer',
            'field_name' => 'required|string',
            'value'      => 'required',
        ]);

        DB::beginTransaction();
        try {
            $stockId = $validated['stock_id'];
            $b2bid = $validated['b2b_id'];
            $fieldName = $validated['field_name'];
            $value = $validated['value'];
            $globalDomainId = $this->domain['global_id'] ?? null;

            if (!$globalDomainId) {
                throw new \RuntimeException('Global domain context missing');
            }

            $findStock = StockItemModel::findOrFail($stockId);
            $findProductPriceMatrix = B2BStockPriceMatrixModel::where('sub_domain_stock_item_id',$stockId)->where('sub_domain_id',$b2bid)->first();
            switch ($fieldName) {
                case 'sales_price':
                    $findStock->update(['sales_price' => $value]);
                    $findProductPriceMatrix->update(['sales_price' => $value]);
                    break;

                case 'purchase_price':
                    $findStock->update(['purchase_price' => $value]);
                    $findProductPriceMatrix->update(['purchase_price' => $value]);
                    break;

                default:
                    throw new \InvalidArgumentException('Field not supported');
            }

            DB::commit();
            return response()->json([
                'message' => 'Success',
                'status'  => ResponseAlias::HTTP_OK
            ]);

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

    protected function handleEntity($modelClass, $whereConditions, $isChecked, $createCallback)
    {
        $entity = $modelClass::where($whereConditions)->first();

        if ($isChecked) {
            // Create or Update the entity
            if (!$entity) {
                $entityData = $createCallback();
                return $modelClass::create($entityData);
            }
            $entity->update(['status' => true]);
            return $entity->fresh();
        } else {
            if ($entity) {
                $entity->update(['status' => false]);
            }
            return null;
        }
    }
    private function prepareCustomerData($childDomain, $patternCodeService): array
    {
        $code = $this->generateCustomerCode($patternCodeService);
        $getCoreSettingTypeId = SettingTypeModel::where('slug','customer-group')->first();

        $getCustomerGroupId = SettingModel::where('setting_type_id', $getCoreSettingTypeId->id)
            ->where('name', 'Domain')->where('domain_id', $this->domain['global_id'])->first();

        if(empty($getCustomerGroupId)){
            $getCustomerGroupId = SettingModel::create([
                'domain_id' => $this->domain['global_id'],
                'name' => 'Domain',
                'setting_type_id' => $getCoreSettingTypeId->id, // Ensure this variable has a value
                'slug' => 'domain',
                'status' => 1,
                'created_at' => now(),  // Add the current timestamp
                'updated_at' => now()   // If you also have `updated_at`
            ]);
        }

        return [
            'domain_id' => $this->domain['global_id'],
            'customer_unique_id' => "{$this->domain['global_id']}@{$childDomain->mobile}-{$childDomain->name}",
            'code' => $code['code'],
            'name' => $childDomain->name,
            'mobile' => $childDomain->mobile,
            'email' => $childDomain->email,
            'status' => true,
            'address' => $childDomain->address,
            'customer_group_id' => $getCustomerGroupId->id ?? null, // Default group
            'slug' => Str::slug($childDomain->name),
            'sub_domain_id' => $childDomain->id,
            'customer_id' => $code['generateId'], // Generated ID from the pattern code
        ];
    }
    private function ensureCustomerLedger(CustomerModel $customer)
    {
        $ledgerExist = AccountHeadModel::where('customer_id', $customer->id)
            ->where('config_id', $this->domain['acc_config'])
            ->exists();

        if (!$ledgerExist) {
            $accountConfig = AccountingModel::find($this->domain['acc_config']);
            AccountHeadModel::insertCustomerLedger($accountConfig, $customer);
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

    private function prepareVendorData($childDomain, $parentDomain, $patternCodeService,$customer): array
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
            'customer_id' => $customer->id,
        ];
    }

    private function ensureVendorLedger(VendorModel $vendor)
    {
        $childAccConfig = AccountingModel::where('domain_id',$vendor->domain_id)->first();
        $ledgerExist = AccountHeadModel::where('vendor_id', $vendor->id)
            ->where('config_id', $childAccConfig)
            ->exists();
        if (!$ledgerExist) {
            AccountHeadModel::insertVendorLedger($childAccConfig, $vendor);
        }
    }

    public function b2bSubDomain()
    {
        $domains = SubDomainModel::getB2BDomain($this->domain['global_id']);
        return response()->json([
            'message' => 'Success',
            'status'  => ResponseAlias::HTTP_OK,
            'data'    => $domains
        ], ResponseAlias::HTTP_OK);
    }

    public function categoryWiseProductManage(B2bCategoryWiseProductRequest $request)
    {
        $validate = $request->validated();

        $findSubDomain = SubDomainModel::find($validate['id']);

        if (!$findSubDomain || $findSubDomain->status==0) {
            return response()->json([
                'message' => 'Domain not found or domain inactive',
                'status'  => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        $findSubDomain->update($validate);

        $this->handleCategory($validate['categories'],$findSubDomain,$validate);

        return response()->json([
            'message' => 'success',
            'status'  => ResponseAlias::HTTP_OK
        ], ResponseAlias::HTTP_OK);

    }

    private function handleCategory($categoryInput, $findSubDomain, $inputData)
    {
        // Step 1: Get child domain config
        $findSubDomainIds = UserModel::getDomainData($findSubDomain->sub_domain_id);
        $childAccConfig = $findSubDomainIds['config_id'];

        $processedCategoryIds = [];

        foreach ($categoryInput as $categoryId) {
            // Step 2: Ensure subdomain category exists
            $subDomainCategory = $this->manageSubDomainCategory($categoryId, $childAccConfig);

            // Step 3: Create or update the price matrix row
            B2BCategoryPriceMatrixModel::updateOrCreate(
                [
                    'sub_domain_id' => $findSubDomain->id,
                    'domain_category_id' => $categoryId,
                ],
                [
                    'config_id' => $childAccConfig,
                    'sub_domain_category_id' => $subDomainCategory->id,
                    'created_by_id' => $this->domain['user_id'],
                    'percent_mode' => $inputData['percent_mode'],
                    'status' => 1,
                    'sales_target_amount' => $inputData['sales_target_amount'],
                    'bonus_percent' => $inputData['bonus_percent'],
                    'purchase_percent' => $inputData['purchase_percent'],
                    'mrp_percent' => $inputData['mrp_percent'],
                    'not_process' => 0
                ]
            );

            $processedCategoryIds[] = $categoryId;
        }

        // Step 4: Disable status for categories that were not selected
        $existingMappings = B2BCategoryPriceMatrixModel::where('sub_domain_id', $findSubDomain->id)->get();
        $idsToKeepActive = $existingMappings->whereIn('domain_category_id', $processedCategoryIds)->pluck('id');
        $idsToDisable = $existingMappings->whereNotIn('domain_category_id', $processedCategoryIds)->pluck('id');

        B2BCategoryPriceMatrixModel::whereIn('id', $idsToKeepActive)->update(['status' => 1]);
        B2BCategoryPriceMatrixModel::whereIn('id', $idsToDisable)->update(['status' => 0]);

        $updatedMappings = B2BCategoryPriceMatrixModel::where('sub_domain_id', $findSubDomain->id)->where('status' , 1)->get();

        $findSubDomainIds = UserModel::getDomainData($findSubDomain->sub_domain_id);
        $childAccConfig = $findSubDomainIds['config_id'];

        foreach ($updatedMappings as $category){
            $this->handleCategoryProduct($category, $findSubDomain,$childAccConfig);
        }
    }

    private function handleCategoryProduct($categoryMatrix, $findSubDomain,$childAccConfig) {

        // Fetch all products for the given category and config
        $products = ProductModel::where('category_id', $categoryMatrix->domain_category_id)
            ->where('config_id', $this->domain['config_id'])
            ->where('status', true)
            ->get();

        // Fetch all child products for given config in one query
        $productUpdates = ProductModel::where('config_id', $childAccConfig)
            ->whereIn('parent_id', $products->pluck('id')) // Batch query
            ->get()
            ->keyBy('parent_id');

        // Pre-fetch all stock items for efficiency
        $parentStocks = StockItemModel::whereIn('product_id', $products->pluck('id'))
            ->get()
            ->keyBy('product_id');

        // Iterate through all parent products
        foreach ($products as $parentProduct) {
            $productUpdate = $productUpdates[$parentProduct->id] ?? null;
            $parentStock = $parentStocks[$parentProduct->id] ?? null;

            if (!$productUpdate) {
                $this->createChildProduct($parentProduct, $categoryMatrix, $childAccConfig, $parentStock,$findSubDomain);
            } elseif ($productUpdate) {
                $this->updateProductAndStock($parentProduct, $productUpdate, $parentStock, $findSubDomain,$categoryMatrix);
            }
        }

        return true;
    }

    private function updateProductAndStock($parentProduct, $productUpdate, $parentStock, $findSubDomain,$categoryMatrix) {
        $status = $categoryMatrix->status;
        $findProductTypeId = \Modules\Inventory\App\Models\SettingModel::getProductTypeOrCreate($parentProduct->product_type_id,$productUpdate->config_id);

        // Update child product's status
        $productUpdate->update([
            'status' => $status,
            'product_type_id' => $findProductTypeId,
            'vendor_id' => $findSubDomain->vendor_id,
        ]);

        $productStockUpdate = StockItemModel::where('product_id', $productUpdate->id)->get();

        if ($productStockUpdate) {
            // Update child stock
            foreach ($productStockUpdate as $productStock) {
                $productStock->update(
                    $this->prepareStockData($parentStock, $findSubDomain, ['status' => $status])
                );

                B2BStockPriceMatrixModel::updateOrCreate(
                    [
                        'sub_domain_id' => $findSubDomain->id,
                        'category_price_matrix_id' => $categoryMatrix->id,
                        'domain_stock_item_id' => $parentStock->id,
                        'sub_domain_stock_item_id' => $productStock->id,
                    ],
                    [
                        'mrp' => $productStock->sales_price,
                        'purchase_price' => $productStock->purchase_price,
                        'sales_price' => $productStock->sales_price,
                        'status' => $status,
                    ]
                );
            }
        }

        return true;
    }

    private function createChildProduct($parentProduct, $category, $childAccConfig, $parentStock,$findSubDomain) {
        $findProductTypeId = \Modules\Inventory\App\Models\SettingModel::getProductTypeOrCreate($parentProduct->product_type_id,$childAccConfig);

        // Create a new child product
        $childProduct = ProductModel::create([
            'category_id' => $category->sub_domain_category_id,
            'name' => $parentProduct->name,
            'slug' => $this->generateUniqueSlug($parentProduct->slug),
            'config_id' => $childAccConfig,
            'barcode' => $parentProduct->barcode,
            'alternative_name' => $parentProduct->alternative_name,
            'unit_id' => $parentProduct->unit_id,
            'product_type_id' => $findProductTypeId,
            'parent_id' => $parentProduct->id,
            'description' => $parentProduct->description,
            'vendor_id' => $findSubDomain->vendor_id,
        ]);

        // Fetch parent product stock
        $getStocks = StockItemModel::where([
            ['product_id', $parentProduct->id],
            ['config_id', $parentProduct->config_id],
            ['status', 1],
            ['is_delete', 0]
        ])->get();

        if (count($getStocks) > 0) {
            foreach ($getStocks as $stock) {
                // Prepare stock data and insert
                $stockData = $this->prepareStockData($stock, $findSubDomain,[
                    'product_id'         => $childProduct->id,
                    'config_id'          => $childAccConfig,
                    'barcode'            => random_int(10000000, 99999999),
                    'sku'                => random_int(10000000, 99999999),
                    'status'             => $stock->status ?? null,
                    'is_delete'          => $stock->is_delete ?? null,
                    'is_master'          => $stock->is_master ?? null,
                    'name'               => $stock->name ?? null,
                    'display_name'       => $stock->display_name ?? null,
                    'uom'                => $stock->uom ?? null,
                    'bangla_name'        => $stock->bangla_name ?? null,
                    'parent_stock_item'  => $stock->id ?? null,
                ]);

                // Attributes processing
                $attributes = ['color_id', 'grade_id', 'brand_id', 'size_id', 'model_id'];
                foreach ($attributes as $attribute) {
                    if (!empty($stock->$attribute)) {
                        $stockData[$attribute] = $this->createParticularForBranch($stock->$attribute, $childAccConfig);
                    }
                }
                $stockData['barcode'] = random_int(10000000, 99999999);
                $stockData['sku'] = $stockData['barcode'];
                // Save stock data
                $stock = StockItemModel::create($stockData);

                B2BStockPriceMatrixModel::updateOrCreate(
                    [
                        'sub_domain_id' => $findSubDomain->id,
                        'category_price_matrix_id' => $category->id,
                        'domain_stock_item_id' => $parentStock->id,
                        'sub_domain_stock_item_id' => $stock->id,
                    ],
                    [
                        'mrp' => $stock->sales_price,
                        'purchase_price' => $stock->purchase_price,
                        'sales_price' => $stock->sales_price,
                        'status' => 1,
                    ]
                );
            }
        }
        return true;
    }

    private function prepareStockData($parentStock, $findSubDomain, $additionalData = []) {
        $modifier = ($findSubDomain->percent_mode == 'Increase')
            ? (100 + $findSubDomain->mrp_percent)
            : (100 - $findSubDomain->mrp_percent);
        $purchaseModifier = ($findSubDomain->percent_mode == 'Increase')
            ? (100 + $findSubDomain->purchase_percent)
            : (100 - $findSubDomain->purchase_percent);

        $salesPrice = $parentStock->sales_price * ($modifier / 100);
        $purchasePrice = $parentStock->sales_price * ($purchaseModifier / 100);

        $baseStockData = [
//            'purchase_price' => $findSubDomain->purchase_percent? $salesPrice * ((100 - $findSubDomain->purchase_percent) / 100) : 0.0,
            'purchase_price' => $purchasePrice,
            'average_price' => $purchasePrice,
            'sales_price' => $salesPrice ?? 0.0,
            'min_quantity' => $parentStock->min_quantity ?? 0.0,
        ];

        return array_merge($baseStockData, $additionalData);
    }

    private function createParticularForBranch($parentParticularId, $childAccConfig) {
        $parentParticular = ParticularModel::find($parentParticularId);

        if ($parentParticular) {
            $childParticular = ParticularModel::firstOrCreate(
                [
                    'particular_type_id' => $parentParticular->particular_type_id,
                    'config_id' => $childAccConfig,
                ],
                [
                    'name' => $parentParticular->name,
                    'slug' => $parentParticular->slug,
                    'status' => 1,
                ]
            );
            return $childParticular->id;
        }
    }

    private function generateUniqueSlug($slug) {
        // Generate a unique slug by appending random characters
        return $slug . '-' . substr(uniqid(), -6);
    }

    private function manageSubDomainCategory($categoryId,$childAccConfig)
    {
        $findParentCategory = CategoryModel::find($categoryId);
        $parentId = null;

        if ($findParentCategory->parent) {
            $findCategoryParent = CategoryModel::find($findParentCategory->parent);

            $parentCategory = CategoryModel::firstOrCreate(
                [
                    'slug' => $findCategoryParent->slug,
                    'config_id' => $childAccConfig
                ],
                [
                    'status' => 1,
                    'name' => $findCategoryParent->name,
                    'parent' => null
                ]
            );

            $parentId = $parentCategory->id;
        }

        $currentCategory = CategoryModel::firstOrCreate(
            [
                'slug' => $findParentCategory->slug,
                'config_id' => $childAccConfig,
                'parent' => $parentId
            ],
            [
                'status' => 1,
                'name' => $findParentCategory->name
            ]
        );
        return $currentCategory;
    }

    public function b2bSubDomainDelete($id)
    {
        $subDomainModel = SubDomainModel::find($id);
        $subDomainModel->update(['status' => 1]);
        return response()->json([
            'message' => 'success',
            'status'  => ResponseAlias::HTTP_OK,
            'data' => ''
        ], ResponseAlias::HTTP_OK);
    }

    public function b2bSubDomainSetting($id)
    {
        $entity = SubDomainModel::getB2BDomainSetting($id);
        return response()->json([
            'message' => 'success',
            'status'  => ResponseAlias::HTTP_OK,
            'data' => $entity
        ], ResponseAlias::HTTP_OK);
    }

    public function b2bCategoryWisePriceUpdate(B2bDomainWiseCategoryRequest $request,$id)
    {
        $input = $request->validated();
        $finaCategoryMatrix = B2BCategoryPriceMatrixModel::findOrFail($id);
        $finaCategoryMatrix->update($input);
        DB::beginTransaction();

        try {
            $findCategoryMatrix = B2BCategoryPriceMatrixModel::findOrFail($id);
            $findSubDomain = SubDomainModel::findOrFail($findCategoryMatrix->sub_domain_id);

            // Validate percentages
            if ($findCategoryMatrix->mrp_percent < 0 || $findCategoryMatrix->mrp_percent > 100) {
                throw new \InvalidArgumentException('Invalid MRP percentage value');
            }

            if ($findCategoryMatrix->purchase_percent &&
                ($findCategoryMatrix->purchase_percent < 0 || $findCategoryMatrix->purchase_percent > 100)) {
                throw new \InvalidArgumentException('Invalid purchase percentage value');
            }

            // Get products with their parent stock
            $products = StockItemModel::with(['parentStock' => function($query) {
                $query->select('id', 'sales_price');
            }])
                ->where('inv_stock.config_id', $findCategoryMatrix->config_id)
                ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
                ->where('inv_product.category_id', $findCategoryMatrix->sub_domain_category_id)
                ->where('inv_product.vendor_id', $findSubDomain->vendor_id)
                ->whereNotNull('inv_stock.parent_stock_item')
                ->select('inv_stock.id', 'inv_stock.parent_stock_item')
                ->get();

            // Prepare updates
            $updates = [];
            foreach ($products as $product) {
                if (!$product->parentStock) {
                    continue; // or log this case
                }

                $modifier = ($findCategoryMatrix->percent_mode == 'Increase')
                    ? (100 + $findCategoryMatrix->mrp_percent)
                    : (100 - $findCategoryMatrix->mrp_percent);

                $purchaseModifier = ($findCategoryMatrix->percent_mode == 'Increase')
                    ? (100 + $findCategoryMatrix->purchase_percent)
                    : (100 - $findCategoryMatrix->purchase_percent);

                $salesPrice = $product->parentStock->sales_price * ($modifier / 100);
                $purchasePrice = $product->parentStock->sales_price * ($purchaseModifier / 100);


                $updates[] = [
                    'id' => $product->id,
                    'purchase_price' => $purchasePrice,
                    'average_price' => $purchasePrice,
                    'sales_price' => $salesPrice
                ];
            }

            // Batch update
            StockItemModel::upsert($updates, ['id'], ['purchase_price', 'average_price', 'sales_price']);
            $findCategoryMatrix->update(['not_process'=>0]);
            DB::commit();

            return response()->json([
                'message' => 'Successfully process ' . count($updates) . ' products',
                'status' => ResponseAlias::HTTP_OK
            ]);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Required records not found',
                'status' => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);

        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
                'status' => ResponseAlias::HTTP_BAD_REQUEST
            ], ResponseAlias::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Price update failed: ' . $e->getMessage(), [
                'exception' => $e,
                'categoryMatrixId' => $id
            ]);

            return response()->json([
                'message' => 'Failed to update prices',
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function b2bSubDomainCategory(Request $request,$id)
    {
        $domain = UserModel::getDomainData($id);
        $invConfig = $domain['inv_config'];
        $entities = B2BCategoryPriceMatrixModel::getB2BDomainCategory($invConfig);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $entities
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    public function b2bSubDomainProduct(Request $request, $id)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        try {
            // Validate inputs and find required models
            $findSubDomain = SubDomainModel::findOrFail($id);
            $findCategoryMatrix = B2BCategoryPriceMatrixModel::where('sub_domain_id', $id)->firstOrFail();

            // Get base products query
            $products = StockItemModel::join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
                ->join('inv_category', 'inv_category.id', '=', 'inv_product.category_id')
                ->leftJoin('inv_stock as parent_stock', 'parent_stock.id', '=', 'inv_stock.parent_stock_item')
                ->leftJoin('inv_b2b_category_price_matrix as matrix', function($join) use ($id) {
                    $join->on('matrix.sub_domain_category_id', '=', 'inv_category.id')
                        ->where('matrix.sub_domain_id', $id);
                })
                ->where('inv_stock.config_id', $findCategoryMatrix->config_id)
                ->where('inv_stock.status', 1)
                ->where('inv_product.vendor_id', $findSubDomain->vendor_id)
                ->whereNotNull('inv_stock.parent_stock_item')
                ->select([
                    'inv_stock.id',
                    'inv_stock.status',
                    'inv_stock.name',
                    'inv_stock.quantity as sub_domain_stock',
                    'inv_stock.sales_price as sub_domain_sales_price',
                    'inv_stock.purchase_price as sub_domain_purchase_price',
                    'inv_category.name as category_name',
                    'inv_category.id as category_id',
                    'parent_stock.quantity as center_stock',
                    'parent_stock.sales_price as center_sales_price',
                    'parent_stock.purchase_price as center_purchase_price',
                    'parent_stock.quantity as center_stock',
                    'matrix.mrp_percent',
                    'matrix.purchase_percent',
                    'matrix.percent_mode',
                    'matrix.sub_domain_id as b2b_id',
                ]);
            if (isset($request['term']) && !empty($request['term'])){
                $products = $products->whereAny(['inv_stock.name','inv_stock.slug','inv_category.name'],'LIKE','%'.$request['term'].'%');
            }

            $total  = $products->count();
            $entities = $products->skip($skip)
                ->take($perPage)
                ->orderBy('inv_stock.id','DESC')
                ->get();

            return response()->json([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'Success',
                'total' => $total,
                'data' => $entities,
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => ResponseAlias::HTTP_NOT_FOUND,
                'message' => 'Subdomain or category matrix not found'
            ], ResponseAlias::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            return response()->json([
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage()
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function impersonate($subDomainId,$user)
    {

        $domain = $this->domain['domain_id'];
        $subDomain = SubDomainModel::where('sub_domain_id', $subDomainId)
            ->where('domain_id',$domain)
            ->firstOrFail();

        if ($subDomain){
            $userExists = UserModel::where('id', $user)
                ->where('domain_id',$subDomainId)
                ->firstOrFail();

            $accessRole = \DB::table('cor_user_role')->where('user_id',$userExists->id)->first();
            $getUserWareHouse = UserWarehouseModel::getUserActiveWarehouse($userExists->id);
            $getUserWareHouseItem = StockItemHistoryModel::getUserWarehouseProductionItem($userExists->id);

            $arrayData=[

                'id'=> $userExists->id,
                'name'=> $userExists->name,
                'mobile'=> $userExists->mobile,
                'email'=> $userExists->email,
                'username'=> $userExists->username,
                'user_group'=> $userExists->user_group,
                'domain_id'=> $userExists->domain_id,
                'access_control_role' => $accessRole?$accessRole->access_control_role:[],
                'android_control_role' => $accessRole?$accessRole->android_control_role:[],
                'user_warehouse'=> $getUserWareHouse? $getUserWareHouse:[],
                'production_item'=> $getUserWareHouseItem? $getUserWareHouseItem:[],

            ];

            return new JsonResponse([
                'status'=>200,
                'message'=>'success',
                'data'=> $arrayData
            ]);
        }

    }

    /**
     * Update B2B products' sales and purchase prices based on domain-wise category price matrix.
     * Each product update is transactional: failure of one product does not affect others.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function b2bDomainWiseProductUpdate(Request $request)
    {
        $childDomainId = $request->domain_id;

        try {
            // Fetch all sub-domains
            $subDomains = SubDomainModel::where('domain_id', $this->domain['domain_id'])
                ->when($childDomainId, fn($q) => $q->where('sub_domain_id', $childDomainId))
                ->get();

            if ($subDomains->isEmpty()) {
                return response()->json([
                    'message' => 'No sub-domains found for the selected domain(s).',
                    'status' => ResponseAlias::HTTP_NOT_FOUND
                ], ResponseAlias::HTTP_NOT_FOUND);
            }

            // Map sub_domain_id => vendor_id
            $subDomainVendors = $subDomains->pluck('vendor_id', 'id');

            // Fetch all relevant category price matrices
            $categoriesMatrix = B2BCategoryPriceMatrixModel::whereIn('sub_domain_id', $subDomains->pluck('id'))->get();

            if ($categoriesMatrix->isEmpty()) {
                return response()->json([
                    'message' => 'No price matrices found for the selected domain(s).',
                    'status' => ResponseAlias::HTTP_NOT_FOUND
                ], ResponseAlias::HTTP_NOT_FOUND);
            }

            // Fetch all stock items and parent stock in bulk
            $matrixConfigIds = $categoriesMatrix->pluck('config_id')->unique();
            $matrixCategoryIds = $categoriesMatrix->pluck('sub_domain_category_id')->unique();

            $products = StockItemModel::with(['parentStock' => fn($q) => $q->select('id','sales_price','name','display_name','bangla_name')])
                ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
                ->whereIn('inv_stock.config_id', $matrixConfigIds)
                ->whereIn('inv_product.category_id', $matrixCategoryIds)
                ->whereIn('inv_product.vendor_id', $subDomainVendors->values())
                ->whereNotNull('inv_stock.parent_stock_item')
                ->select('inv_stock.*', 'inv_product.category_id', 'inv_product.vendor_id')
                ->get();

            if ($products->isEmpty()) {
                return response()->json([
                    'message' => 'No products found to update.',
                    'status' => ResponseAlias::HTTP_NOT_FOUND
                ], ResponseAlias::HTTP_NOT_FOUND);
            }

            // Map matrices for easy lookup: vendor_id + category_id => matrix
            $matrixMap = $categoriesMatrix->keyBy(fn($matrix) => $subDomainVendors[$matrix->sub_domain_id] . '_' . $matrix->sub_domain_category_id);

            $successCount = 0;
            $failedProducts = [];

            // Per-product transactional update
            foreach ($products as $product) {
                try {
                    DB::transaction(function () use ($product, $matrixMap) {

                        $matrixKey = $product->vendor_id . '_' . $product->category_id;

                        if (!isset($matrixMap[$matrixKey])) {
                            throw new \Exception("No price matrix found for product ID: {$product->id}");
                        }

                        $matrix = $matrixMap[$matrixKey];

                        // Validate percentages
                        if ($matrix->mrp_percent < 0 || $matrix->mrp_percent > 100) {
                            throw new \InvalidArgumentException('Invalid MRP percentage for matrix ID: ' . $matrix->id);
                        }

                        if ($matrix->purchase_percent && ($matrix->purchase_percent < 0 || $matrix->purchase_percent > 100)) {
                            throw new \InvalidArgumentException('Invalid purchase percentage for matrix ID: ' . $matrix->id);
                        }

                        if (!$product->parentStock) {
                            throw new \Exception("Parent stock not found for stock item ID: {$product->id}");
                        }

                        $modifier = $matrix->percent_mode === 'Increase'
                            ? (100 + $matrix->mrp_percent)
                            : (100 - $matrix->mrp_percent);

                        $purchaseModifier = $matrix->percent_mode === 'Increase'
                            ? (100 + $matrix->purchase_percent)
                            : (100 - $matrix->purchase_percent);

                        $salesPrice = $product->parentStock->sales_price * ($modifier / 100);
                        $purchasePrice = $product->parentStock->sales_price * ($purchaseModifier / 100);

                        $averagePrice = $product->average_price && $product->average_price > 0 ? $product->average_price : $purchasePrice;
                        // Upsert single product
                        StockItemModel::upsert([
                            [
                                'id' => $product->id,
                                'sales_price' => $salesPrice,
                                'purchase_price' => $purchasePrice,
                                'average_price' => $averagePrice,
                                'name' => $product->parentStock->name,
                                'display_name' => $product->parentStock->display_name,
                                'bangla_name' => $product->parentStock->bangla_name,
                            ]
                        ], ['id'], ['sales_price', 'purchase_price', 'average_price', 'name', 'display_name', 'bangla_name']);
                    });

                    $successCount++;

                } catch (\Exception $e) {
//                    \Log::error("Failed to update product ID {$product->id}: " . $e->getMessage());
                    $failedProducts[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->parentStock->name ?? null,
                        'category_id' => $product->category_id,
                    ];
                    continue; // skip this product, continue with others
                }
            }

            return response()->json([
                'message' => "Successfully updated {$successCount} products.",
                'failed_products' => $failedProducts,
                'status' => ResponseAlias::HTTP_OK
            ]);

        } catch (\Exception $e) {
            /*\Log::error('B2B domain-wise product update failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);*/

            return response()->json([
                'message' => 'Failed to process price updates',
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function domainStock(Request $request)
    {
        dump($this->domain['domain_id']);
//        dump($this->domain['config_id']);


        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        try {
            // Validate inputs and find required models
            $findSubDomainsIds = SubDomainModel::where('domain_id',$this->domain['domain_id'])->pluck('id')->toArray();
            dump($findSubDomainsIds);
            $findCategoryMatrix = B2BStockPriceMatrixModel::whereIn('sub_domain_id', $findSubDomainsIds)->get()->toArray();
            dump($findCategoryMatrix);

            /*// Get base products query
            $products = StockItemModel::join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
                ->join('inv_category', 'inv_category.id', '=', 'inv_product.category_id')
                ->leftJoin('inv_stock as parent_stock', 'parent_stock.id', '=', 'inv_stock.parent_stock_item')
                ->leftJoin('inv_b2b_category_price_matrix as matrix', function($join) use ($id) {
                    $join->on('matrix.sub_domain_category_id', '=', 'inv_category.id')
                        ->where('matrix.sub_domain_id', $id);
                })
                ->where('inv_stock.config_id', $findCategoryMatrix->config_id)
                ->where('inv_stock.status', 1)
                ->where('inv_product.vendor_id', $findSubDomain->vendor_id)
                ->whereNotNull('inv_stock.parent_stock_item')
                ->select([
                    'inv_stock.id',
                    'inv_stock.status',
                    'inv_stock.name',
                    'inv_stock.quantity as sub_domain_stock',
                    'inv_stock.sales_price as sub_domain_sales_price',
                    'inv_stock.purchase_price as sub_domain_purchase_price',
                    'inv_category.name as category_name',
                    'inv_category.id as category_id',
                    'parent_stock.quantity as center_stock',
                    'parent_stock.sales_price as center_sales_price',
                    'parent_stock.purchase_price as center_purchase_price',
                    'parent_stock.quantity as center_stock',
                    'matrix.mrp_percent',
                    'matrix.purchase_percent',
                    'matrix.percent_mode',
                    'matrix.sub_domain_id as b2b_id',
                ]);
            if (isset($request['term']) && !empty($request['term'])){
                $products = $products->whereAny(['inv_stock.name','inv_stock.slug','inv_category.name'],'LIKE','%'.$request['term'].'%');
            }

            $total  = $products->count();
            $entities = $products->skip($skip)
                ->take($perPage)
                ->orderBy('inv_stock.id','DESC')
                ->get();

            return response()->json([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'Success',
                'total' => $total,
                'data' => $entities,
            ]);*/

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => ResponseAlias::HTTP_NOT_FOUND,
                'message' => 'Subdomain or category matrix not found'
            ], ResponseAlias::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            return response()->json([
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage()
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }




}
