<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HandlesFileUploads;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Entities\Product;
use Modules\Inventory\App\Entities\StockItem;
use Modules\Inventory\App\Http\Requests\ProductGalleryRequest;
use Modules\Inventory\App\Http\Requests\ProductMeasurementRequest;
use Modules\Inventory\App\Http\Requests\ProductMeasurementSalesPurchaseRequest;
use Modules\Inventory\App\Http\Requests\ProductRequest;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\ProductGalleryModel;
use Modules\Inventory\App\Models\ProductMeasurementModel;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\SettingModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Inventory\App\Models\StockItemPriceMatrixModel;
use Modules\Inventory\App\Repositories\StockItemRepository;
use Modules\NbrVatTax\App\Models\NbrItemVat;
use Modules\NbrVatTax\App\Models\NbrTaxTariff;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductController extends Controller
{
    use HandlesFileUploads;
    protected $domain;

    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if ($userId && !empty($userId)) {
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }

    public function index(Request $request)
    {

        $data = ProductModel::getRecords($request,$this->domain);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
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
    public function store(ProductRequest $request,EntityManagerInterface $em)
    {
        $service = new JsonRequestResponse();
        $input = $request->validated();

        $input['config_id'] = $this->domain['config_id'];
        $entity = ProductModel::create($input);
        $productId = $entity['id'];

        // base unit added measurement table
        $unitDataConditions = [
            'product_id' => $productId,
            'config_id' => $this->domain['config_id'],
            'is_base_unit' => 1
        ];

        $unitData = [
            'unit_id' => $input['unit_id'],
            'quantity' => 1,
            'is_sales' => 1,
            'is_purchase' => 1
        ];

        ProductMeasurementModel::updateOrCreate($unitDataConditions, $unitData);
        $this->createMasterStockItem($productId,$input);
      //  $em->getRepository(StockItem::class)->insertStockItem($productId,$input);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    public static function createMasterStockItem($id, array $data)
    {
        // Start transaction (optional)
        DB::beginTransaction();

        try {
            $product = ProductModel::find($id);

            if (!$product) {
                throw new \InvalidArgumentException("Product with ID {$id} not found.");
            }

            $exist = StockItemModel::where('product_id', $id)->where('is_master', 1)->first();

            if (!$exist) {
                $unitName = $product->unit->name ?? null;

                $stockItem = new StockItemModel();
                $stockItem->config_id       = $product->config_id;
                $stockItem->product_id      = $product->id;

                $stockItem->name            = $product->name;
                $stockItem->display_name    = $product->name;
                $stockItem->uom             = $unitName;

                $stockItem->purchase_price  = $data['purchase_price'] ?? 0.0;
                $stockItem->average_price   = $data['purchase_price'] ?? 0;
                $stockItem->price           = $data['sales_price'] ?? 0.0;
                $stockItem->sales_price     = $data['sales_price'] ?? 0.0;
                $stockItem->sku             = $data['sku'] ?? null;

                $stockItem->bangla_name     = $data['bangla_name'] ?? null;
                $stockItem->min_quantity    = (!empty($data['min_quantity']) && $data['min_quantity'] > 1)
                    ? $data['min_quantity'] : 0;
                $stockItem->item_size       = $data['item_size'] ?? null;

                // Optional foreign keys (Particulars)
                $stockItem->color_id  = $data['color_id']  ?? null;
                $stockItem->brand_id  = $data['brand_id']  ?? null;
                $stockItem->size_id   = $data['size_id']   ?? null;
                $stockItem->grade_id  = $data['grade_id']  ?? null;
                $stockItem->model_id  = $data['model_id']  ?? null;
                $stockItem->is_master = 1;
                $stockItem->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {

        $service = new JsonRequestResponse();

        $isMultiplePrice = ConfigModel::where('id', $this->domain['inv_config'])->value('is_multi_price');

        $entity = ProductModel::getProductDetails($id, $this->domain);

        if ($isMultiplePrice == 1 && $entity->stock_item_id) {
            $getPriceField = SettingModel::where('inv_setting.status', 1)
                ->where('parent_slug', 'price-mode')
                ->select([
                    'inv_setting.id',
                    'inv_setting.name as price_field_name',
                    'inv_setting.slug as price_field_slug',
                    'inv_setting.parent_slug',
                ])
                ->get()->toArray();

            if ($getPriceField) {
                foreach ($getPriceField as &$val) {
                    $multiPriceData = StockItemPriceMatrixModel::where('product_id', $id)
                        ->where('stock_item_id', $entity->stock_item_id)
                        ->where('price_unit_id', $val['id'])
                        ->select(['price'])
                        ->first();

                    $val['price'] = $multiPriceData ? $multiPriceData->price : null;
                }
            }

            $item['price_field'] = $getPriceField;
            $item['price_field_array'] = array_column($getPriceField, 'price_field_name');
        } else {
            $item['price_field'] = null;
            $item['price_field_array'] = [];
        }

        if ($entity->stock_item_id){
            $warehouseWiseStock = StockItemHistoryModel::allWarehouseWiseStockItem($entity->stock_item_id,$this->domain['inv_config'],null);
            $entity->warehouse_wise_stock = $warehouseWiseStock;
        }

        if (!$entity) {
            $entity = 'Data not found';
        }
        $entity->multi_price_field = $item;

        return $service->returnJosnResponse($entity);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, $id,EntityManagerInterface $em)
    {
        $data = $request->validated();
        $entity = ProductModel::find($id);
        $entity->update($data);
        $productId = $entity['id'];
        $em->getRepository(StockItem::class)->updateStockItem($productId,$data);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
//        StockItemHistoryModel::where()
        ProductModel::find($id)->delete();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'delete'
        ]);
    }


    public function productStatusInlineUpdate($id)
    {
        $product = ProductModel::find($id);

        if (!$product) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'Product not found.'
            ]);
        }

        $stockItem = StockItemModel::where('product_id', $id)->first();

        if (!$stockItem) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'Stock not found.'
            ]);
        }

        try {
            // Toggle status for product and stockItem
            $newStatus = $product->status == 1 ? 0 : 1;

            $product->update(['status' => $newStatus]);
            $stockItem->update(['status' => $newStatus]);

            return response()->json([
                'message' => 'Status updated successfully.',
                'status' => 200,
                'data' => [
                    'product_id' => $product->id,
                    'new_status' => $newStatus
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'An error occurred while updating status.',
                'error' => $e->getMessage()
            ]);
        }
    }
    public function productInlineUpdate(Request $request, $id)
    {
        $fieldName = $request['field_name'];
        $value = $request['value'];

        if (!$fieldName || !$value) {
            return response()->json([
                'status' => ResponseAlias::HTTP_BAD_REQUEST,
                'success' => false,
                'message' => 'Field name or value is required.'
            ],ResponseAlias::HTTP_BAD_REQUEST);
        }

        $product = ProductModel::find($id);

        if (!$product) {
            return response()->json([
                'status' => ResponseAlias::HTTP_NOT_FOUND,
                'success' => false,
                'message' => 'Product not found.'
            ],ResponseAlias::HTTP_NOT_FOUND);
        }

        try {
            if ($fieldName == 'expiry_duration'){
                $product->update(['expiry_duration' => $value]);
            }

            return response()->json([
                'message' => 'Status updated successfully.',
                'status' => ResponseAlias::HTTP_OK
            ], ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
                'success' => false,
                'message' => 'An error occurred while updating status.',
                'error' => $e->getMessage()
            ],ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function stockItem()
    {
        $service = new JsonRequestResponse();
        $data = ProductModel::getStockItem($this->domain);
        return $service->returnJosnResponse($data);
    }

    public function productForRecipe()
    {
        $service = new JsonRequestResponse();
        $data = StockItemModel::getProductForRecipe($this->domain);
        return $service->returnJosnResponse($data);
    }

    public function measurementAdded(ProductMeasurementRequest $request)
    {
        $service = new JsonRequestResponse();
        $input = $request->validated();
        $existsByUnit = ProductMeasurementModel::where('product_id', $input['product_id'])->where('unit_id',$input['unit_id'])->where('config_id',$this->domain['config_id'])->first();
        if ($existsByUnit){
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'unit_exists',
                'status' => Response::HTTP_OK,
            ]));
            $response->setStatusCode(Response::HTTP_OK);
            return $response;
        }
        $input['is_base_unit'] = 0;
        $input['config_id'] = $this->domain['config_id'];
        $input['is_sales'] = false;
        $input['is_purchase'] = false;

        $entity = ProductMeasurementModel::create($input);
        return $service->returnJosnResponse($entity);
    }

    public function measurementSalesPurchaseUpdate(ProductMeasurementSalesPurchaseRequest $request, $productId)
    {
        $input = $request->validated();

        // Validate 'type' to ensure it matches an allowed column
        $allowedTypes = ['is_sales', 'is_purchase']; // Define allowed columns
        if (!isset($input['type']) || !in_array($input['type'], $allowedTypes)) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid type provided.',
            ], 400);
        }

        // Retrieve only required columns from the database
        $measurements = ProductMeasurementModel::where('product_id', $productId)
            ->where('config_id', $this->domain['config_id'])
            ->select(['id', $input['type']])
            ->get();

        // If no measurement units found, return error response
        if ($measurements->isEmpty()) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'No measurement units found.',
            ], 404);
        }

        // Perform updates in a single query if possible (batch update)
        foreach ($measurements as $measurementUnit) {
            $condition = isset($input['check']) && $input['check'] === true && $measurementUnit['id'] == $input['unit_id'];
            $measurementUnit->update([$input['type'] => $condition ? 1 : 0]);
        }

        // Return appropriate response
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'updated',
        ]);
    }


    public function measurementList(Request $request,$id)
    {
        $data = ProductMeasurementModel::getRecords($id,$this->domain['config_id']);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $data
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    public function measurementDelete($id)
    {
        $data = ProductMeasurementModel::find($id);
        $data->delete();
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    public function galleryAdded(ProductGalleryRequest $request)
    {
        $data = $request->validated();
        $findProduct = ProductGalleryModel::where('product_id', $data['product_id'])->first();

        $targetMap = [
            'feature_image' => 'uploads/inventory/product/feature_image',
            'path_one' => 'uploads/inventory/product/path_one',
            'path_two' => 'uploads/inventory/product/path_two',
            'path_three' => 'uploads/inventory/product/path_three',
            'path_four' => 'uploads/inventory/product/path_four',
        ];

        $targetLocation = $targetMap[$data['type']] ?? null;
        $productPath = $this->handleFileUpload($request, $findProduct,'image',$targetLocation,'public');

        $data[$data['type']] = $productPath;

        $findProduct ? $findProduct->update($data) : ProductGalleryModel::create($data);

        return response()->json([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $findProduct ?? ProductGalleryModel::where('product_id', $data['product_id'])->first(),
        ], Response::HTTP_OK);
    }

    public function galleryDelete(Request $request)
    {
        $data = $request->only('product_id','type');
        $type = $data['type'];

        $findProduct = ProductGalleryModel::where('product_id', $data['product_id'])->first();

        $productPath = $this->deleteFile($findProduct[$type],'public');
        if ($productPath) {
            $findProduct->update([$data['type'] => null]);
        }
        $findProduct->refresh();
        return response()->json([
            'message' => 'success',
            'status' => ResponseAlias::HTTP_OK,
            'data' => $findProduct,
        ], ResponseAlias::HTTP_OK);
    }

    public function nbrTariff($id)
    {
        // Validate product exists
        $product = ProductModel::find($id);
        if (!$product) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'Product not found.'
            ]);
        }

        // Define VAT fields and labels
        $vatFields = [
            'customs_duty' => 'Customs Duty',
            'supplementary_duty' => 'Supplementary Duty',
            'value_added_tax' => 'Value Added Tax',
            'advance_tax' => 'Advance Tax',
            'advance_income_tax' => 'Advance Income Tax',
            'recurring_deposit' => 'Recurring Deposit',
            'advance_trade_vat' => 'Advance Trade Vat',
            'regulatory_duty' => 'Regulatory Duty',
            'total_tax_incidence' => 'Total Tax Incidence'
        ];

        // Fetch VAT data for the product
        $getProductVat = NbrItemVat::where('item_id', $id)->first();

        // Fetch applicable tariff data (if `hscode_id` exists and is valid)
        $getNbrVat = $getProductVat && $getProductVat->hscode_id
            ? NbrTaxTariff::find($getProductVat->hscode_id)
            : null;

        // Build VAT items response
        $vatItems = [];
        foreach ($vatFields as $fieldName => $fieldLabel) {
            $vatItems[] = [
                'field_name' => $fieldName,
                'field_label' => $fieldLabel,
                'value' => $getProductVat[$fieldName] ?? null,
                'nbr_vat' => $getNbrVat[$fieldName] ?? null,
                'hscode_id' => $getProductVat->hscode_id ?? null
            ];
        }

        // Return JSON response
        return response()->json([
            'status' => 200,
            'success' => true,
            'data' => $vatItems
        ]);
    }


    public function nbrTariffInlineUpdate(Request $request, $id)
    {
        // Define the list of allowed fields
        $allowedFields = [
            'customs_duty',
            'supplementary_duty',
            'value_added_tax',
            'advance_tax',
            'advance_income_tax',
            'recurring_deposit',
            'advance_trade_vat',
            'regulatory_duty',
            'total_tax_incidence',
            'hscode_id',
        ];

        // Validate request input
        $validatedData = $request->validate([
            'hscode_id' => 'required|integer',
            'field_name' => 'required|string',
            'value' => 'required|numeric|min:0',
        ]);

        if ($validatedData['field_name']=='hscode_id'){
            $conditions = [
                'item_id' => $id,
                'config_id' => $this->domain->nbr_config
            ];

            $findNbrTariff = NbrTaxTariff::find($validatedData['value']);

            $vatData = [
                'hscode_id' => $validatedData['value'],
                'customs_duty' => $findNbrTariff->customs_duty ?? null,
                'supplementary_duty' => $findNbrTariff->supplementary_duty ?? null,
                'value_added_tax' => $findNbrTariff->value_added_tax ?? null,
                'advance_tax' => $findNbrTariff->advance_tax ?? null,
                'advance_income_tax' => $findNbrTariff->advance_income_tax ?? null,
                'recurring_deposit' => $findNbrTariff->recurring_deposit ?? null,
                'advance_trade_vat' => $findNbrTariff->advance_trade_vat ?? null,
                'regulatory_duty' => $findNbrTariff->regulatory_duty ?? null,
                'total_tax_incidence' => $findNbrTariff->total_tax_incidence ?? null
            ];
            $vat = NbrItemVat::updateOrCreate($conditions, $vatData);
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => $vat
            ]);
        }

        // Validate the field name against the allowed fields
        $fieldName = $validatedData['field_name'];
        if (!in_array($fieldName, $allowedFields)) {
            return response()->json(['message' => 'Invalid field name.', 'status' => 400], 400);
        }

        // Construct the conditions and update data
        $conditions = [
            'hscode_id' => $validatedData['hscode_id'],
            'item_id' => $id,
            'config_id' => $this->domain->nbr_config
        ];

        $vatData = [
            $fieldName => $validatedData['value'],
        ];

        try {
            // Update or create the VAT data record
            $vat = NbrItemVat::updateOrCreate($conditions, $vatData);

            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => $vat
            ]);
        } catch (\Exception $e) {
            // Handle exceptions gracefully
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'An error occurred while updating the record.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



}
