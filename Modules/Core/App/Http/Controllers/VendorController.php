<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\VendorRequest;
use Modules\Core\App\Models\VendorModel;

class VendorController extends Controller
{

    public function index(Request $request){
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;
        $vendors = VendorModel::/*where('isDelete',0)*/
        select([
            'id',
            'name',
            'company_name',
            'email',
            'mobile',
            'created_at'
        ]);

        if (isset($request['term']) && !empty($request['term'])){
            $vendors = $vendors->where('name','LIKE','%'.$request['term'].'%')
                ->orWhere('email','LIKE','%'.$request['term'].'%')
                ->orWhere('company_name','LIKE','%'.$request['term'].'%')
                ->orWhere('mobile','LIKE','%'.$request['term'].'%');
        }

        $totalUsers  = $vendors->count();
        $vendors = $vendors->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')->get();

        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => $totalUsers,
            'data' => $vendors
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

        $domain = 65;
        $input['global_option_id'] = $domain;
        $params = ['domain' => $domain,'table' => 'cor_vendors','prefix' => ''];
        $pattern = $patternCodeService->customerCode($params);
        $input['code'] = $pattern['code'];
        $input['vendor_code'] = $pattern['generateId'];

        $entity = VendorModel::create($input);
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


}
