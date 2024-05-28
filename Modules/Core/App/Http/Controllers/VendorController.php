<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\VendorRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;

class VendorController extends Controller
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

        $data = VendorModel::getRecords($request,$this->domain);
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
    public function store(VendorRequest $request, GeneratePatternCodeService $patternCodeService)
    {
        $service = new JsonRequestResponse();
        $input = $request->validated();
        $input['domain_id'] = $this->domain['global_id'];
        $params = ['domain' => $this->domain['global_id'],'table' => 'cor_vendors','prefix' => ''];
        $pattern = $patternCodeService->customerCode($params);
        $input['code'] = $pattern['code'];
        $input['vendor_code'] = $pattern['generateId'];
        $entity = VendorModel::create($input);
        $ledgerExist = AccountHeadModel::where('vendor_id',$entity->id)->where('source', 'vendor')->where('config_id', $this->domain['acc_config_id'])->first();
        if (empty($ledgerExist)){
            AccountHeadModel::insertVendorLedger( $this->domain['acc_config_id'],$entity);
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
        $entity = VendorModel::find($id);

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
        $entity = VendorModel::find($id);

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
        $entity = VendorModel::find($id);

        if (!$entity){
            $entity = 'Data not found';
        }

        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VendorRequest $request, $id)
    {
        $data = $request->validated();
        $entity = VendorModel::find($id);
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
        VendorModel::find($id)->delete();

        $entity = ['message'=>'delete'];
        return $service->returnJosnResponse($entity);

    }


    public function localStorage(Request $request){

        $data = VendorModel::getRecordsForLocalStorage($request,$this->domain);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }


}
