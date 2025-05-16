<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\UserLoginRequest;
use Modules\Core\App\Http\Requests\UserRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\UserWarehouseModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;

class LoginController extends Controller
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
     * Login the specified resource from storage.
     */
    public function userLogin(UserLoginRequest $request)
    {
        $data = $request->validated();

        $userExists = UserModel::where('username',$data['username'])->first();
        if ($userExists){
            $verify = password_verify($data['password'], $userExists->password);
            if (!$verify){
                return new JsonResponse(['status'=> 404, 'message'=>'Wrong password']);
            }
        }else{
            return new JsonResponse(['status'=>404, 'message'=>'Invalid credentials']);
        }

        $accessRole = \DB::table('cor_user_role')->where('user_id',$userExists->id)->first();

        $getUserWareHouse = UserWarehouseModel::getUserActiveWarehouse($userExists->id);
        $getUserWareHouseItem = StockItemHistoryModel::getUserWarehouseProductionItem($userExists->id);
        $arrayData=[
            'id'=>$userExists->id,
            'name'=>$userExists->name,
            'mobile'=>$userExists->mobile,
            'email'=>$userExists->email,
            'username'=>$userExists->username,
            'user_group'=>$userExists->user_group,
            'domain_id'=>$userExists->domain_id,
            'access_control_role' => $accessRole?$accessRole->access_control_role:[],
            'android_control_role' => $accessRole?$accessRole->android_control_role:[],
            'user_warehouse'=>$getUserWareHouse? $getUserWareHouse:[],
            'production_item'=>$getUserWareHouseItem? $getUserWareHouseItem:[],
        ];
        return new JsonResponse([
            'status'=>200,
            'message'=>'success',
            'data'=>$arrayData
        ]);
    }

    public function changePassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'current_password'      => 'required',
            'new_password'          => 'required|min:8',
        ], [
            'current_password.required' => 'Please enter your current password.',
            'new_password.required' => 'Please enter a new password.',
            'new_password.min' => 'Your new password must have at least :min characters.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
        $user = UserModel::find($this->domain['user_id']); // or any User object you have
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Old password is incorrect.'
            ], 422);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        return response()->json([
            'status' => true,
            'message' => 'Password updated successfully.'
        ]);
    }
}
