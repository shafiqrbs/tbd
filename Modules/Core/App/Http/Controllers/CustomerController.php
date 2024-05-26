<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\CustomerRequest;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\UserModel;

class CustomerController extends Controller
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
    public function index(Request $request,EntityManagerInterface $em){
        $data = CustomerModel::getRecords($this->domain,$request);
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
    public function store(CustomerRequest $request, GeneratePatternCodeService $patternCodeService )
    {
        $service = new JsonRequestResponse();
        $input = $request->validated();
        $input['domain_id'] = $this->domain['global_id'];
        $input['customer_unique_id'] = "{$this->domain['global_id']}@{$input['mobile']}-{$input['name']}";
        $params = ['domain' => $this->domain['global_id'],'table' => 'cor_customers','prefix' => 'EMP-'];
        $pattern = $patternCodeService->customerCode($params);
        $input['code'] = $pattern['code'];
        $input['customerId'] = $pattern['generateId'];
        $entity = CustomerModel::create($input);
        AccountHeadModel::insertCustomerLedger( $this->domain['acc_config_id'],$entity);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {

        $service = new JsonRequestResponse();
        $entity = CustomerModel::find($id);
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
        $entity = CustomerModel::find($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function ledger($id)
    {
        $service = new JsonRequestResponse();
        $entity = CustomerModel::find($id);
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
        $entity = CustomerModel::find($id);

        if (!$entity){
            $entity = 'Data not found';
        }

        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, $id)
    {

        $data = $request->validated();
        $entity = CustomerModel::find($id);
        $data['customer_unique_id'] = "{$entity['domain_id']}@{$data['mobile']}-{$data['name']}";

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
        CustomerModel::find($id)->delete();

        $entity = ['message'=>'delete'];
        return $service->returnJosnResponse($entity);
    }


    /**
     * Display a listing of the resource.
     */
    public function localStorage(Request $request,EntityManagerInterface $em){
        $data = CustomerModel::getRecordsForLocalStorage($this->domain,$request);
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
