<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\UserTransactionModel;
use Modules\Inventory\App\Models\ConfigDiscountModel;
use Modules\Production\App\Models\ProductionBatchItemModel;
use Modules\Production\App\Models\ProductionElements;
use Modules\Production\App\Models\ProductionExpense;


class DiscountConfigController extends Controller
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
        $config = $this->domain['config_id'];
        $entity = ConfigDiscountModel::updateOrCreate([
            'config_id' => $config,
        ]);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($entity);
        return $data;
    }


    public function userDiscount(Request $request)
    {
        UserModel::getAllUsers($request,$this->domain);

        $data = UserModel::getAllUserTransaction($this->domain);

        return response()->json([
           'status' => 200,
           'message' => 'success',
           'data' => $data
        ]);
//        dump($data);
        /*exit;

        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;*/
    }

}
