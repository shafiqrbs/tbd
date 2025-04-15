<?php

namespace Modules\Domain\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Entities\DomainChild;
use Modules\Domain\App\Http\Requests\B2bCategoryWiseProductRequest;
use Modules\Domain\App\Models\B2BCategoryPriceMatrixModel;
use Modules\Domain\App\Models\B2BStockPriceMatrixModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Domain\App\Models\SubDomainModel;
use Modules\Inventory\App\Models\CategoryModel;
use Modules\Inventory\App\Models\ParticularModel;
use Modules\Inventory\App\Models\ProductModel;
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


    public function domainInlineUpdate(Request $request)
    {
        $validated = $request->validate([
            'domain_id'  => 'required|integer',
            'field_name' => 'required|string',
            'value'      => 'required',
        ]);

        $domainId   = $validated['domain_id'];
        $fieldName  = $validated['field_name'];
        $value      = $validated['value'];
        $globalDomainId = $this->domain['global_id'];

        $findDomain = DomainModel::find($domainId);
        if (!$findDomain) {
            return response()->json(['message' => 'Domain not found', 'status' => ResponseAlias::HTTP_NOT_FOUND], ResponseAlias::HTTP_NOT_FOUND);
        }

        // Assuming $this->domain is defined globally
        if (!isset($globalDomainId)) {
            return response()->json(['message' => 'Global domain context missing', 'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            switch ($fieldName) {
                case 'domain_type':
                    $subDomain = SubDomainModel::firstOrCreate(
                        [
                            'domain_id'     => $globalDomainId,
                            'sub_domain_id' => $domainId,
                        ],
                        ['status' => 0]
                    );
                    $subDomain->update(['domain_type' => $value]);
                    break;

                case 'status':
                    $subDomain = SubDomainModel::where('domain_id', $globalDomainId)
                        ->where('sub_domain_id', $domainId)
                        ->first();

                    if (!$subDomain) {
                        return response()->json([
                            'message' => 'Assign domain type before updating status',
                            'status'  => ResponseAlias::HTTP_NOT_FOUND
                        ], ResponseAlias::HTTP_NOT_FOUND);
                    }

                    $subDomain->update(['status' => $value]);
                    break;

                default:
                    return response()->json([
                        'message' => 'Field not supported',
                        'status'  => ResponseAlias::HTTP_BAD_REQUEST
                    ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            return response()->json([
                'message' => 'Success',
                'status'  => ResponseAlias::HTTP_OK
            ], ResponseAlias::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
                'status'  => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
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

        $updatedMappings = B2BCategoryPriceMatrixModel::where('sub_domain_id', $findSubDomain->id)->get();

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

        // Update child product's status
        $productUpdate->update([
            'status' => $status,
//            'vendor_id' => $findVendor->id
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
        // Create a new child product
        $childProduct = ProductModel::create([
            'category_id' => $category->sub_domain_category_id,
            'name' => $parentProduct->name,
            'slug' => $this->generateUniqueSlug($parentProduct->slug),
            'config_id' => $childAccConfig,
            'barcode' => $parentProduct->barcode,
            'alternative_name' => $parentProduct->alternative_name,
            'unit_id' => $parentProduct->unit_id,
            'product_type_id' => $parentProduct->product_type_id,
            'parent_id' => $parentProduct->id,
            'description' => $parentProduct->description,
//            'vendor_id' => $findVendor->id,
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

        $salesPrice = $parentStock->sales_price * ($modifier / 100);

        $baseStockData = [
            'purchase_price' => $findSubDomain->purchase_percent? $salesPrice * ((100 - $findSubDomain->purchase_percent) / 100) : 0.0,
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


    public function b2bSubDomainSetting($id)
    {
        $entity = SubDomainModel::getB2BDomainSetting($id);
        return response()->json([
            'message' => 'success',
            'status'  => ResponseAlias::HTTP_OK,
            'data' => $entity
        ], ResponseAlias::HTTP_OK);
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


    public function b2bSubDomainProduct(Request $request,$id)
    {
        $domain = UserModel::getDomainData($id);
        $invConfig = $domain['inv_config'];
        $entities = B2BCategoryPriceMatrixModel::getB2BDomainCategory($invConfig);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $domains
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

}
