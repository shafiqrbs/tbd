<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Entities\Vendor;
use Modules\Core\App\Http\Requests\CustomerRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;
use Barryvdh\Form\CreatesForms;
use Barryvdh\Form\ValidatesForms;



class VendorController extends Controller
{

    use ValidatesForms, CreatesForms;


    public function index(Request $request){
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;
        $users = VendorModel::/*where('isDelete',0)*/
        select([
            'id',
            'name',
            'company_name',
            'email',
            'mobile',
            'created_at'
        ]);

        if (isset($request['term']) && !empty($request['term'])){
            $users = $users->where('name','LIKE','%'.$request['term'].'%')
                ->orWhere('email','LIKE','%'.$request['term'].'%')
                ->orWhere('company_name','LIKE','%'.$request['term'].'%')
                ->orWhere('mobile','LIKE','%'.$request['term'].'%');
        }

        $totalUsers  = $users->count();
        $users = $users->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')->get();


        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => $totalUsers,
            'data' => $users
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }






    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request, GeneratePatternCodeService $patternCodeService, EntityManager $manager )
    {
        $service = new JsonRequestResponse();
        $input = $request->all();
        $domain = 65;
        $input['global_option_id'] = $domain;
        $params = ['domain' => $domain,'table' => 'cor_vendors','prefix' => ''];
        $pattern = $patternCodeService->customerCode($params);
        $input['code'] = $pattern['code'];
        $input['vendor_code'] = $pattern['generateId'];
        DB::beginTransaction();
        try{
            $entity = VendorModel::create($input);
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
      //  $service->clearCaches('Customer');
        $entity = VendorModel::find($id);
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
        $entity = VendorModel::find($id);
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
        $user=VendorModel::find($id);
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
