<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\App\Http\Requests\AccountHeadMasterRequest;
use Modules\Accounting\App\Http\Requests\AccountHeadRequest;
use Modules\Accounting\App\Models\AccountHeadMasterModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;



class AccountHeadMasterController extends Controller
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

        $data = AccountHeadMasterModel::getRecords($request,$this->domain);
        return response()->json([
            'status' => 200,
            'message' => 'success',
            'total' => $data['count'],
            'data' => $data['entities']
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountHeadMasterRequest $request)
    {
        $data = $request->validated();
        $data['config_id'] = $this->domain['acc_config'];
        $entity = AccountHeadMasterModel::create($data);
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Account head created successfully.',
            'data' => $entity,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountHeadRequest $request, $id)
    {
        try {
            // Validate and get the validated data
            $validatedData = $request->validated();

            // Find the entity or fail
            $entity = AccountHeadMasterModel::findOrFail($id);

            // Update the entity
            $updated = $entity->update($validatedData);

            if (!$updated) {
                throw new \RuntimeException('Failed to update account head');
            }
            // Reload the model to get any database-default values
            $entity->refresh();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Account head updated successfully.',
                'data' => $entity,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'Account head not found.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to update account head.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }


    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = AccountHeadMasterModel::find($id);
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
        $entity = AccountHeadMasterModel::find($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        AccountHeadMasterModel::find($id)->delete();
        $entity = ['message'=>'delete'];
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

}
