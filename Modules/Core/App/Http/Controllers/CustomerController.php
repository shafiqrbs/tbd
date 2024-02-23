<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Entities\Customer;
use Modules\Core\App\Http\Requests\CustomerRequest;
use Modules\Core\App\Models\CustomerModel;
use Barryvdh\Form\CreatesForms;
use Barryvdh\Form\ValidatesForms;



class CustomerController extends Controller
{

    use ValidatesForms, CreatesForms;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request,EntityManagerInterface $em){
        $service = new JsonRequestResponse();
        $service->clearCaches('Customer');
        $entities = $em->getRepository(Customer::class)->listWithSearch($request->query());
        $data = $service->returnJosnResponse($entities);
        return $data;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request, GeneratePatternCodeService $patternCodeService )
    {
        $service = new JsonRequestResponse();
        $input = $request->all();
        $domain = 65;
        $input['global_option_id'] = $domain;
        $input['customer_unique_id'] = "{$domain}@{$input['mobile']}-{$input['name']}";
        $params = ['domain' => $domain,'table' => 'cor_customers','prefix' => 'EMP-'];
        $pattern = $patternCodeService->customerCode($params);
        $input['code'] = $pattern['code'];
        $input['customerId'] = $pattern['generateId'];
        DB::beginTransaction();
        try {
            $entity = CustomerModel::create($input);
            DB::commit();
            $data = $service->returnJosnResponse($entity);
        } catch(QueryException $ex){
            DB::rollback();
            $data = $service->returnJosnResponse($ex->getMessage());
        }
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = CustomerModel::find($id);
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
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, $id)
    {
        $input = $request->all();
        $service = new JsonRequestResponse();
        $entity = CustomerModel::find($id);
        $input['customer_unique_id'] = "{$entity['global_option_id']}@{$input['mobile']}-{$input['name']}";
        DB::beginTransaction();
        try {
                $entity->update($input);
                DB::commit();
                $data = $service->returnJosnResponse($entity);
            } catch(QueryException $ex){
                DB::rollback();
                $data = $service->returnJosnResponse($ex->getMessage());
            }
            return $data;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        $service->clearCaches('Customer');
        $user=CustomerModel::find($id);
        $entity = ['message'=>'false'];
        DB::beginTransaction();
        try {
            if($user and $user->delete()){
                $entity = ['message'=>'success'];
            }
            DB::commit();
            $data = $service->returnJosnResponse($entity);
            return $data;
        } catch (\Exception $e) {
            DB::rollback();
        }
    }


}
