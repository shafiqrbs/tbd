<?php

namespace Modules\Utility\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\CustomerRequest;
use Barryvdh\Form\CreatesForms;
use Barryvdh\Form\ValidatesForms;
use Modules\Utility\App\Http\Requests\SiteMapRequest;
use Modules\Utility\App\Models\SiteMapModel;


class SiteMapController extends Controller
{

    use ValidatesForms, CreatesForms;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request){
        $service = new JsonRequestResponse();
        $entities = SiteMapModel::listWithSearch();
        $data = $service->returnJosnResponse($entities);
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SiteMapRequest $request)
    {
        $service = new JsonRequestResponse();
        $input = $request->all();
        DB::beginTransaction();
        try {
            $entity = SiteMapModel::create($input);
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
        $entity = SiteMapModel::find($id);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $service = new JsonRequestResponse();
        $entity = SiteMapModel::find($id);
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
        $entity = SiteMapModel::find($id);
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
        $user=SiteMapModel::find($id);
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
