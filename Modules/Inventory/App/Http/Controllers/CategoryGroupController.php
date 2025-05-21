<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Inventory\App\Http\Requests\CategoryGroupRequest;
use Modules\Inventory\App\Models\CategoryModel;

class CategoryGroupController extends Controller
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

        $data = CategoryModel::getRecords($request,$this->domain);
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
    public function store(CategoryGroupRequest $request)
    {
        $service = new JsonRequestResponse();
        $input = $request->validated();
        $input['config_id'] = $this->domain['config_id'];
        $entity = CategoryModel::create($input);
        if(empty($entity->parent)){
            $ledgerExist = AccountHeadModel::where('product_group_id',$entity->id)->where('config_id', $this->domain['acc_config'])->first();
            if(empty($ledgerExist)){
                AccountHeadModel::insertCategoryGroupLedger( $this->domain['acc_config'],$entity);
            }
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = CategoryModel::find($id);

        if (!$entity){
            $entity = 'Data not found';
        }

        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function details($id)
    {
        $service = new JsonRequestResponse();
        $entity = CategoryModel::find($id);

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
        $entity = CategoryModel::find($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryGroupRequest $request, $id)
    {
        $data = $request->validated();
        $entity = CategoryModel::find($id);
        $entity->update($data);
        if($entity->parent){
            $head = AccountHeadModel::where('category_id',$entity->id)->first();
        }else{
            $head = AccountHeadModel::where('product_group_id',$entity->id)->first();
        }
        $head->name = $entity->name;
        $head->save();
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $isParent = CategoryModel::getCategoryIsParent($id);
        $isDeletable = true;
        if ($isParent){
            $isDeletable = CategoryModel::getCategoryIsDeletable($id);
        }
        if ($isDeletable){
            CategoryModel::find($id)->delete();
            $message = 'delete';
        }else{
            $message = 'exists';
        }

        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => $message,
            'status' => Response::HTTP_OK,
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }



}
