<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Accounting\App\Http\Requests\TransactionModeRequest;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\Accounting\App\Models\TransactionModeModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Domain\App\Http\Requests\DomainRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Utility\App\Models\SettingModel;

class TransactionModeController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $entityId = $request->header('X-Api-User');
        if ($entityId && !empty($entityId)){
            $entityData = UserModel::getUserData($entityId);
            $this->domain = $entityData;
        }
    }

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request){

        $data = TransactionModeModel::getRecords($request,$this->domain);
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
    public function store(TransactionModeRequest $request)
    {
        $data = $request->validated();
        $data['status'] = true;
        $data['config_id'] = $this->domain['acc_config_id'];

        $getAuthorized = SettingModel::find($data['authorised_mode_id']);
        if ($getAuthorized){
            $data['authorised'] = $getAuthorized->name;
        }

        $getAccountType = SettingModel::find($data['account_mode_id']);
        if ($getAccountType){
            $data['account_type'] = $getAccountType->name;
        }

        if ($request->file('path')) {
            $imageName = time().'.'.$request->path->extension();
            $request->path->move(public_path('uploads/accounting/transaction-mode/'), $imageName);
             $data['path'] = $imageName;
        }
        $entity = TransactionModeModel::create($data);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = TransactionModeModel::find($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     */
    /*public function edit($id)
    {
        $service = new JsonRequestResponse();
        $entity = TransactionModeModel::find($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }*/

    /**
     * Update the specified resource in storage.
     */
    public function update(DomainRequest $request, $id)
    {

        $data = $request->validated();
        $entity = DomainModel::find($id);
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
        DomainModel::find($id)->delete();
        $entity = ['message'=>'delete'];
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    public function transactionMode(Request $request)
    {
        $entity = TransactionModeModel::getTransactionsModeData($this->domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }
}
