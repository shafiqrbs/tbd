<?php

namespace Modules\Medicine\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Medicine\App\Http\Requests\MedicineStockInlineRequest;
use Modules\Medicine\App\Models\StockItemModel;
use Modules\Medicine\App\Models\ProductModel;
use Modules\Inventory\App\Models\CategoryModel;
use Modules\Medicine\App\Http\Requests\MedicineInlineRequest;
use Modules\Medicine\App\Http\Requests\StockRequest;
use Modules\Medicine\App\Models\MedicineDetailsModel;
use Modules\Medicine\App\Models\MedicineModel;
use Modules\Medicine\App\Models\MedicineStockModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class MedicineStockController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if ($userId && !empty($userId)){
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request){


        $data = MedicineStockModel::getRecords($request,$this->domain);
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



     /**
     * Display a listing of the resource.
     */
    public function generic(Request $request){

        $data = MedicineStockModel::getGenericRecords($request,$this->domain);
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


    /**
     * Store a newly created resource in storage.
     */
    public function store(StockRequest $request)
    {
        $service = new JsonRequestResponse();
        $input = $request->validated();
        $input['config_id'] = $this->domain['config_id'];
        $entity = ProductModel::create($input);

        $stock['config_id'] = $this->domain['config_id'];
        $stock['product_id'] = $entity->id;
        $itemStock = StockItemModel::create($stock);

        $medicineStock['config_id'] = $this->domain['hms_config'];
        $medicineStock['product_id'] = $entity->id;
        $medicineStock['stock_item_id'] = $itemStock->id;
        $medicineStock['opd_quantity'] = $input['opd_quantity'];
        $medicineStock['medicine_dosage_id'] = $input['medicine_dosage_id'] ?? null;
        $medicineStock['medicine_bymeal_id'] = $input['medicine_bymeal_id'] ?? null;
        $medicineStock['opd_status'] = true;
        $medicineStock['ipd_status'] = true;
        $medicineStock = MedicineStockModel::create($medicineStock);
        $data = $service->returnJosnResponse($medicineStock);
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = MedicineStockModel::getProductDetails($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }



    /**
     * Show the specified resource for edit.
     */
    public function medicineStockInlineUpdate(MedicineStockInlineRequest $request, $id)
    {
        $input = $request->validated();
        $entity = MedicineStockModel::find($id);
        $category = (isset($input['category_id']) and $input['category_id']) ? $input['category_id']:'';
        if($category){
            $entity->product()->update(['category_id' => $category]);
        }
        if ($entity) {
            $updateDetails = [];
            if (array_key_exists('opd_quantity', $input)) {
                $updateDetails['opd_quantity'] = $input['opd_quantity'];
            }
            if (array_key_exists('medicine_dosage_id', $input)) {
                $updateDetails['medicine_dosage_id'] = $input['medicine_dosage_id'];
            }
            if (array_key_exists('medicine_bymeal_id', $input)) {
                $updateDetails['medicine_bymeal_id'] = $input['medicine_bymeal_id'];
            }
            if (array_key_exists('admin_status', $input)) {
                $updateDetails['admin_status'] = $input['admin_status'] ? 0:1;
            }
            if (array_key_exists('opd_status', $input)) {
                $updateDetails['opd_status'] = $input['opd_status'] ? 0:1;
            }
            if (array_key_exists('ipd_status', $input)) {
                $updateDetails['ipd_status'] = $input['ipd_status'] ? 0:1;
            }
            if (!empty($updateDetails)) {
                $entity->update($updateDetails);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'success',
            'data'    => $entity,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function stockDropdown(Request $request)
    {
        $term = $request->get('term');
        $dropdown = MedicineStockModel::getStockDropdown($this->domain,$term);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        $product = ProductModel::find($id);
        if ($product) {
            $product->update(['is_delete' => 1]);
            $product->stockItems()->update(['is_delete' => 1]);
            $product->medicineStocks()->update(['is_delete' => 1]);
        }
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }

    public function stockItemMatrix(Request $request)
    {
        $data = StockItemModel::getStockItemMatrix($this->domain, $request->all());

        return response()->json([
            'message' => 'success',
            'status' => ResponseAlias::HTTP_OK,
            'total' => $data['total'],
            'data' => $data['data'],
            'warehouses' => $data['warehouses']
        ], ResponseAlias::HTTP_OK);
    }


}
