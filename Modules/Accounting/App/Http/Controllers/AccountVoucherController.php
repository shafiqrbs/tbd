<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Accounting\App\Http\Requests\AccountHeadRequest;
use Modules\Accounting\App\Http\Requests\AccountVoucherRequest;
use Modules\Accounting\App\Models\AccountVoucherModel;
use Modules\Accounting\App\Models\SettingModel;
use Modules\Accounting\App\Models\TransactionModeModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Domain\App\Http\Requests\DomainRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\DomainModel;


class AccountVoucherController extends Controller
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
        $data = AccountVoucherModel::getRecords($request,$this->domain);
        $service = new JsonRequestResponse();
        return $service->returnPagingJosnResponse($data);

    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountVoucherRequest $request)
    {
        $data = $request->validated();
        $data['status'] = true;
        $data['config_id'] = $this->domain['acc_config'];
        $entity = AccountVoucherModel::create($data);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = AccountVoucherModel::find($id);
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
        $entity = AccountVoucherModel::find($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountVoucherRequest $request, $id)
    {

        $data = $request->validated();
        $entity = AccountVoucherModel::find($id);
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
        AccountVoucherModel::find($id)->delete();
        $entity = ['message'=>'delete'];
        $data = $service->returnJosnResponse($entity);
        return $data;
    }


    public function LocalStorage(Request $request){
        $data = AccountVoucherModel::getRecordsForLocalStorage($request,$this->domain);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $data
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }


    public function accountVoucherWiseLedger(Request $request)
    {
        $data = AccountVoucherModel::getVoucherWiseLedgerDetails($request,$this->domain);
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Voucher Wise Ledger Details',
            'data' => $data??[],
        ]);
    }

}
