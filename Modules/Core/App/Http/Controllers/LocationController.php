<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Entities\Customer;
use Modules\Core\App\Entities\Location;
use Modules\Core\App\Forms\CustomerType;
use Modules\Core\App\Http\Requests\CustomerRequest;
use Modules\Core\App\Http\Requests\FormValidationManager;
use Modules\Core\App\Models\CustomerModel;
use Barryvdh\Form\CreatesForms;
use Barryvdh\Form\ValidatesForms;
use Illuminate\Support\Facades\Validator;
use Modules\Core\App\Repositories\LocationRepository;


class LocationController extends Controller
{

    use ValidatesForms, CreatesForms;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request,EntityManagerInterface $em){
        $service = new JsonRequestResponse();
        $service->clearCaches('Customer');
        $entities = $em->getRepository(Location::class)->listWithSearch($request->query());
        $data = $service->returnJosnResponse($entities);
        return $data;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function selectSearch(Request $request,EntityManagerInterface $em)
    {

        $term = $request['term'];
        $entities = [];
        $service = new JsonRequestResponse();
        if ($term) {
            $entities = $em->getRepository(Location::class)->searchAutoComplete($term);
        }
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
        $params = ['domain' => $domain,'table' => 'cor_customers'];
        $pattern = $patternCodeService->customerCode($params);
        $input['code'] = $pattern['code'];
        $input['customerId'] = $pattern['generateId'];
        DB::beginTransaction();
        try {
            $entity = CustomerModel::create($input);
            $data = $service->returnJosnResponse($entity);
            return $data;
        } catch (\Exception $e) {
            DB::rollback();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
      //  $service->clearCaches('Customer');
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
        $service->clearCaches('Customer');
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
        $service->clearCaches('Customer');
        $entity = CustomerModel::find($id);
        DB::beginTransaction();
        try {
            $entity->update($input);
            $data = $service->returnJosnResponse($entity);
            return $data;
        } catch (\Exception $e) {
            DB::rollback();
        }
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
            $data = $service->returnJosnResponse($entity);
            return $data;
        } catch (\Exception $e) {
            DB::rollback();
        }
    }


}
