<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\UserTransactionModel;
use Modules\Core\App\Models\UserWarehouseModel;
use Modules\Core\App\Models\WarehouseModel;
use Modules\Inventory\App\Models\ConfigDiscountModel;
use Modules\Production\App\Models\ProductionBatchItemModel;
use Modules\Production\App\Models\ProductionElements;
use Modules\Production\App\Models\ProductionExpense;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


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
        UserModel::insertAllUsersTransactions($this->domain);
        $userTransactions = UserModel::getAllUserTransaction($this->domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($userTransactions);
    }

    public function userDiscountUpdate(Request $request,$id)
    {
        $entity = UserTransactionModel::where('user_id', $id)->firstOrFail();;
        DB::beginTransaction();
        try {
            $entity->update($request->all());
            DB::commit();
            $service = new JsonRequestResponse();
            $userTransactions = UserModel::getAllUserTransaction($this->domain);
            $return = $service->returnJosnResponse($userTransactions);
            return $return;

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

}
