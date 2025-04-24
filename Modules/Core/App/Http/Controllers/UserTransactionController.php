<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\App\Http\Requests\UserTransactionRequest;
use Modules\Core\App\Http\Requests\WarehouseRequest;
use Modules\Core\App\Models\SettingModel;
use Modules\Core\App\Models\SettingTypeModel;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\UserTransactionModel;
use Modules\Core\App\Models\WarehouseModel;
use Modules\Domain\App\Models\B2BCategoryPriceMatrixModel;
use Modules\Inventory\App\Models\ConfigPurchaseModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserTransactionController extends Controller
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

    public function index(Request $request){

        $data = UserModel::getRecordsForLocalStorage($request,$this->domain);
        return response()->json([
            'status' => 200,
            'success' => true,
            'total' => $data['data']['count'],
            'data' => $data['data']['entities'],
        ], 200);
    }

    public function userTransactionUpdate($request)
    {
        $validated = $request->validate([
            'user_id'  => 'required|integer',
            'max_discount'  => 'required|integer',
            'sales_target' => 'required|string'
        ]);

        $user_id = $validated['user_id'];
        UserTransactionModel::updateOrCreate(
            [
                'user_id' => $user_id
            ],
            [
                'max_discount' => $validated['max_discount'] ?? null,
                'sales_target' => $validated['sales_target'] ?? null,
            ]
        );
        return response()->json(['status' => 200, 'success' => true, 'data' => $input]);
    }

}
