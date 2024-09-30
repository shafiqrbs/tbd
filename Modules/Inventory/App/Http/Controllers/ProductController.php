<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Entities\StockItem;
use Modules\Inventory\App\Http\Requests\ProductGalleryRequest;
use Modules\Inventory\App\Http\Requests\ProductMeasurementRequest;
use Modules\Inventory\App\Http\Requests\ProductRequest;
use Modules\Inventory\App\Models\ProductGalleryModel;
use Modules\Inventory\App\Models\ProductMeasurementModel;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Inventory\App\Repositories\StockItemRepository;

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


}
