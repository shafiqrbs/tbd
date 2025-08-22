<?php

namespace Modules\Hospital\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\JsonRequestResponse;

use Modules\Core\App\Models\UserModel;
use Modules\Hospital\App\Http\Requests\ParticularTypeRequest;
use Modules\Hospital\App\Models\ParticularMatrixModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularModeModel;
use Modules\Hospital\App\Models\ParticularTypeModel;
use Modules\Production\App\Http\Requests\SettingRequest;
use Modules\Inventory\App\Models\SettingModel;
use Modules\Inventory\App\Models\SettingTypeModel;


class ParticularTypeController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if ($userId && !empty($userId)) {
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }

    public function index(Request $request)
    {
        $domain = $this->domain;
        $types = ParticularTypeModel::getParticularType($domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($types);
    }


     /**
     * Store a newly created resource in storage.
     */
    public function store(ParticularTypeRequest $request)
    {

        $domain = $this->domain;
        dd($domain);
        $input = $request->validated();
        $entity = ParticularTypeModel::find($input['particular_type_id']);
        $data['data_type'] = $input['data_type'];
        $entity->update($data);
        /*$operations = $input['operation_modes'] ?? [];
        if (is_string($operations)) {
            $operations = json_decode($operations, true) ?? [];
        }
        foreach ($operations as $operation){
            ParticularMatrixModel::updateOrCreate(
                [
                    'config_id' => $entity->config_id,
                    'particular_type_id' => $entity->id,
                    'particular_mode_id' => $operation
                ]
            );
        }*/
        $types = ParticularTypeModel::getParticularType($domain);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($types);
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = SettingModel::find($id);
        if (!$entity) {
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the specified resource for edit.
     */
    public function edit($id)
    {
        $entity = SettingModel::find($id);
        $status = $entity ? Response::HTTP_OK : Response::HTTP_NOT_FOUND;
        return response()->json([
            'message' => 'success',
            'status' => $status,
            'data' => $entity ?? []
        ], Response::HTTP_OK);

    }


    /**
     * Update the specified resource in storage.
     */
    public function update(SettingRequest $request, $id)
    {
        $data = $request->validated();
        $entity = SettingModel::find($id);
        $getConfigId = SettingModel::getConfigId($this->domain['global_id']);
        $data['config_id'] = $getConfigId;
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
        SettingModel::find($id)->delete();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }


    public function settingTypeDropdown()
    {
        $data = SettingTypeModel::getDropdown($this->domain);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => sizeof($data)>0 ? $data : []
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    public function measurementInput()
    {
        $data = SettingModel::getMeasurementInputGenerate($this->domain['global_id']);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => sizeof($data)>0 ? $data : []
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

}
