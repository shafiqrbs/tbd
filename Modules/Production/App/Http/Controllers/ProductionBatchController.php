<?php

namespace Modules\Production\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Models\InvoiceBatchTransactionModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Production\App\Entities\ProductionBatch;
use Modules\Production\App\Http\Requests\BatchItemRequest;
use Modules\Production\App\Http\Requests\BatchRequest;
use Modules\Production\App\Models\ProductionBatchItemnModel;
use Modules\Production\App\Models\ProductionBatchModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductionBatchController extends Controller
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
        $data = ProductionBatchModel::getRecords($request, $this->domain);
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
     * Show the form for creating a new resource.
     */
    public function store(BatchRequest $request, GeneratePatternCodeService $patternCodeService)
    {
        $service = new JsonRequestResponse();
        $config = $this->domain['pro_config'];
        $input = $request->validated();
        $params = ['config' => $this->domain['pro_config'],'table' => 'pro_batch','prefix' => 'PB-'];
        $pattern = $patternCodeService->productBatch($params);
        $input['config_id'] = $config;
        $input['code'] = $pattern['code'];
        $input['invoice'] = $pattern['generateId'];
        $input['process'] = 'created';
        $input['created_by_id'] = $this->domain['user_id'];
        $entity = ProductionBatchModel::create($input);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function insertBatchItem(BatchItemRequest $request)
    {
        $service = new JsonRequestResponse();
        $input = $request->validated();
        $entity = ProductionBatchItemnModel::create($input);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = ProductionBatchModel::getShow($id, $this->domain);
        if (!$entity) {
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('production::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BatchRequest $request, $id)
    {
        $input = $request->validated();

        $batch = ProductionBatchModel::find($id);
        if (!$batch) {
            return response()->json([
                'message' => 'Data not found',
                'status' => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        if ($input['process'] == 'approved') {
            $input['approved_by_id'] = $this->domain['user_id'];

            foreach($batch->batchItems as $batchItem) {
                foreach($batchItem->productionItems as $productionItem) {
                    $stockItem = StockItemModel::find($productionItem->material_id);
                    $productionItem->needed_quantity = $batchItem->issue_quantity * $productionItem->quantity;
                    StockItemHistoryModel::openingStockQuantity($stockItem,'production',$this->domain);
                }
            }
        }

        $batch->update($input);


        return response()->json([
            'message' => 'success',
            'status' => ResponseAlias::HTTP_OK
        ],ResponseAlias::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
