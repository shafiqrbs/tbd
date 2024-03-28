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

        $data = UserModel::getRecords($request,$this->domain);
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

        $data['global_option_id'] = $this->domain['global_id'];
        $data['email_verified_at']= now();
        $data['password']= Hash::make($data['password']);
        $data['is_delete']= 0;

        $user = UserModel::create($data);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($user);
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
}
