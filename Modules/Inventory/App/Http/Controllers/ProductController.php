<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Entities\Product;
use Modules\Inventory\App\Entities\StockItem;
use Modules\Inventory\App\Http\Requests\ProductGalleryRequest;
use Modules\Inventory\App\Http\Requests\ProductMeasurementRequest;
use Modules\Inventory\App\Http\Requests\ProductRequest;
use Modules\Inventory\App\Models\ProductGalleryModel;
use Modules\Inventory\App\Models\ProductMeasurementModel;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Inventory\App\Repositories\StockItemRepository;
use Modules\NbrVatTax\App\Models\NbrItemVat;
use Modules\NbrVatTax\App\Models\NbrTaxTariff;

class ProductController extends Controller
{
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
        $data = ProductModel::getRecords($request, $this->domain);
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
        $em->getRepository(StockItem::class)->insertStockItem($productId,$input);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {

        $service = new JsonRequestResponse();
        $entity = ProductModel::getProductDetails($id, $this->domain);
        if (!$entity) {
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
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
        $service = new JsonRequestResponse();
        ProductModel::find($id)->delete();

        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
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
        $existsByUnit = ProductMeasurementModel::where('product_id', $input['product_id'])->where('unit_id',$input['unit_id'])->first();
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
        $entity = ProductMeasurementModel::create($input);
        return $service->returnJosnResponse($entity);
    }

    public function measurementList(Request $request,$id)
    {
        $data = ProductMeasurementModel::getRecords($id);
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
        $deletePath = $findProduct ? $findProduct->{$data['type']} : null;

        $productPath = $this->processFileUpload($request->file('image'), $targetLocation);

        if ($findProduct) {
            File::delete(public_path($targetLocation . '/' . $deletePath));
        }

        $data[$data['type']] = $productPath;

        $findProduct ? $findProduct->update($data) : ProductGalleryModel::create($data);

        return response()->json([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $findProduct ?? ProductGalleryModel::where('product_id', $data['product_id'])->first(),
        ], Response::HTTP_OK);
    }


    private function processFileUpload($file, $uploadDir)
    {
        if ($file) {
            $uploadDirPath = public_path($uploadDir);
            if (!file_exists($uploadDirPath)) {
                mkdir($uploadDirPath, 0777, true);
            }

            $fileName = time() . '.' . $file->extension();

            $file->move($uploadDirPath, $fileName);
            return $fileName;
        }

        return null;
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
