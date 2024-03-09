<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\UserLoginRequest;
use Modules\Core\App\Http\Requests\UserRequest;
use Modules\Core\App\Models\UserModel;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request){

        $data = UserModel::getRecords($request);
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
    public function store(UserRequest $request)
    {
        $data = $request->validated();

        $domain = 65;
        $data['global_option_id'] = $domain;
        $data['email_verified_at']= now();
        $data['password']= Hash::make($data['password']);
        $data['isDelete']= 0;

        $user = UserModel::create($data);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($user);
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = UserModel::find($id);
        if (!$entity){
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
        $service = new JsonRequestResponse();
        $entity = UserModel::find($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, $id)
    {

        $data = $request->validated();
        $entity = UserModel::find($id);
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
        UserModel::find($id)->delete();
        $entity = ['message'=>'delete'];
        $data = $service->returnJosnResponse($entity);
        return $data;
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

        $arrayData=[
            'id'=>$userExists->id,
            'name'=>$userExists->name,
            'mobile'=>$userExists->mobile,
            'email'=>$userExists->email,
            'username'=>$userExists->username,
            'global_option_id'=>$userExists->global_option_id,
        ];
        return new JsonResponse([
            'status'=>200,
            'message'=>'success',
            'data'=>$arrayData
        ]);
    }
}
