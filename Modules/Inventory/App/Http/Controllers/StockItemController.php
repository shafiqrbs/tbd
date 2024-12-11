<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Entities\StockItem;
use Modules\Inventory\App\Http\Requests\ProductRequest;
use Modules\Inventory\App\Http\Requests\StockSkuRequest;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\ParticularModel;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\SettingModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Inventory\App\Models\StockItemPriceMatrixModel;
use Modules\Inventory\App\Repositories\StockItemRepository;

class StockItemController extends Controller
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
        $data = StockItemModel::getRecords($request, $this->domain);
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
        $entity = StockItemModel::create($input);

        $entities = $em->getRepository(StockItem::class)->insertStockItem($entity['id']);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function stockSkuAdded(StockSkuRequest $request,EntityManagerInterface $em)
    {
        $service = new JsonRequestResponse();
        $input = $request->validated();

        $input['config_id'] = $this->domain['config_id'];
        $findProduct = ProductModel::getProductDetails($input['product_id'],$this->domain);
        if (!$findProduct){
            throw ValidationException::withMessages([
                'exists' => ['Product not found'],
            ]);
        }

        $input['price'] = $findProduct->price;
        $input['sales_price'] = $findProduct->sales_price;
        $productName = $findProduct->product_name.' (';
        $input['purchase_price'] = $findProduct->purchase_price;
        $input['uom'] = $findProduct->unit_name;
        $input['is_master'] = 0;
        $input['status'] = 1;

        $existingProduct = StockItemModel::where('is_master',0)->where('product_id',$input['product_id']);
        if ($request->has('brand_id') && !empty($input['brand_id'])) {
            $existingProduct=$existingProduct->where('brand_id', $request->brand_id);
            $findBrandName = ParticularModel::find($request->brand_id)->name;
            $productName .=  $findBrandName.'-';
        }

        if ($request->has('color_id') && !empty($input['color_id'])) {
            $existingProduct=$existingProduct->where('color_id', $request->color_id);
            $findColorName = ParticularModel::find($request->color_id)->name;
            $productName .=  $findColorName.'-';
        }

        if ($request->has('size_id') && !empty($input['size_id'])) {
            $existingProduct=$existingProduct->where('size_id', $request->size_id);
            $findSizeName = ParticularModel::find($request->size_id)->name;
            $productName .= $findSizeName.'-';
        }

        if ($request->has('grade_id') && !empty($input['grade_id'])) {
            $existingProduct=$existingProduct->where('grade_id', $request->grade_id);
            $findGradeName = ParticularModel::find($request->grade_id)->name;
            $productName .= $findGradeName.'-';
        }

        if ($request->has('model_id') && !empty($input['model_id'])) {
            $existingProduct=$existingProduct->where('model_id', $request->model_id);
            $findModelName = ParticularModel::find($request->model_id)->name;
            $productName .=  $findModelName.'-';
        }
        $existingProduct = $existingProduct->get();


        if (count($existingProduct) > 0) {
            throw ValidationException::withMessages([
                'exists' => ['Already Exists'],
            ]);
        }
        $productName = rtrim($productName, '-');
        $input['name'] = $productName.' )';
        $input['display_name'] = $productName.' )';

        $entity = StockItemModel::create($input);

        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    public function stockItemDelete(Request $request,$id)
    {
        $findStockItem = StockItemModel::find($id);
        $stockHistoryExists = StockItemHistoryModel::where('stock_item_id',$id)->exists();
        if ($stockHistoryExists) {
            $findStockItem->update(['is_delete' => 1]);
        }else{
            $findStockItem->delete();
        }

        $service = new JsonRequestResponse();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }


    public function stockSkuList(Request $request, $product_id)
    {
        $getItems = StockItemModel::getStockSkuItem($product_id, $this->domain);

        $isMultiplePrice = ConfigModel::where('id', $this->domain['inv_config'])->value('is_multi_price');
        $data = [];

        foreach ($getItems as $item) {
            if ($isMultiplePrice == 1) {
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
                        $multiPriceData = StockItemPriceMatrixModel::where('product_id', $product_id)
                            ->where('stock_item_id', $item['stock_id'])
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
            $data[] = $item;
        }

        // Generate response
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => count($getItems),
            'data' => $data
        ]));
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    public function stockSkuInlineUpdate(Request $request,$id)
    {
        $findStockItem = StockItemModel::find($id);
        if (!$findStockItem){
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'Item not found',
                'status' => Response::HTTP_NOT_FOUND,
            ]));
            $response->setStatusCode(Response::HTTP_OK);
            return $response;
        }
        // for price update
        if ($request->get('field')=='price'){
            $findStockItem->update(['price'=>$request->get('value')]);
        }

        // for multi-price price update
        if ($request->get('field')!='price'){
            $wholeSaleExists = StockItemPriceMatrixModel::where('stock_item_id',$id)
                ->where('price_unit_id',$request->get('setting_id'))
                ->where('product_id',$findStockItem->product_id)
                ->first();
            if (!$wholeSaleExists){
                StockItemPriceMatrixModel::create([
                    'stock_item_id'=>$id,
                    'price_unit_id'=>$request->get('setting_id'),
                    'price'=>$request->get('value'),
                    'product_id'=>$findStockItem->product_id,
                ]);
            }else{
                $wholeSaleExists->update(['price'=>$request->get('value')]);
            }
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'updated successfully',
            'status' => Response::HTTP_OK,
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = StockItemModel::getProductDetails($id, $this->domain);

        if (!$entity) {
            $entity = 'Data not found';
        }

        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, $id)
    {
        $data = $request->validated();
        $entity = StockItemModel::find($id);
        $entity->update($data);

        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        StockItemModel::find($id)->delete();

        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }

    public function stockItem()
    {
        $service = new JsonRequestResponse();
        $data = StockItemModel::getStockItem($this->domain);

        return $service->returnJosnResponse($data);
    }

    public function productForRecipe()
    {
        $service = new JsonRequestResponse();
        $data = StockItemModel::getProductForRecipe($this->domain);
        return $service->returnJosnResponse($data);
    }

}
