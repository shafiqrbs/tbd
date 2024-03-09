<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\CustomerRequest;
use Modules\Core\App\Models\CustomerModel;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request,EntityManagerInterface $em){
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;

        $customers = CustomerModel::
        select([
            'id',
            'name',
            'mobile',
            'created_at'
        ]);

        if (isset($request['term']) && !empty($request['term'])){
            $customers = $customers->where('name','LIKE','%'.$request['term'].'%')
                ->orWhere('mobile','LIKE','%'.$request['term'].'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $customers = $customers->where('name',$request['name']);
        }

        if (isset($request['mobile']) && !empty($request['mobile'])){
            $customers = $customers->where('mobile',$request['mobile']);
        }


        $totalUsers  = $customers->count();
        $customers = $customers->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')->get();

        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => $totalUsers,
            'data' => $customers
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

        $domain = 65;
        $input['global_option_id'] = $domain;
        $input['customer_unique_id'] = "{$domain}@{$input['mobile']}-{$input['name']}";

        $params = ['domain' => $domain,'table' => 'cor_customers','prefix' => 'EMP-'];
        $pattern = $patternCodeService->customerCode($params);

        $input['code'] = $pattern['code'];
        $input['customerId'] = $pattern['generateId'];

        $entity = CustomerModel::create($input);
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
        $data['customer_unique_id'] = "{$entity['global_option_id']}@{$data['mobile']}-{$data['name']}";

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


}
