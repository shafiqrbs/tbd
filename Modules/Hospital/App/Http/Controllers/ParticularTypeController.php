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
    public function store(ParticularTypeRequest $request): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|Response
    {
        $domain = $this->domain;
        $input = $request->validated();

        $particularType = ParticularTypeModel::findOrFail($input['particular_type_id']);

        // Update the data_type
        $dataType = $input['data_type'] ?? null;
        $particularType->update(['data_type' => $dataType]);

        // Decode operations if passed as JSON string
        $operations = $input['operation_modes'] ?? [];
        if (is_string($operations)) {
            $operations = json_decode($operations, true) ?? [];
        }

        // Sanitize operation ids to integers
        $operations = array_map('intval', $operations);
        $existingMatrixIds = $particularType->particularMatrix()->pluck('particular_mode_id')->toArray();

        // Determine which to delete and which to add
        $toDelete = array_diff($existingMatrixIds, $operations);
        $toInsert = array_diff($operations, $existingMatrixIds);

        // Delete unselected operation mappings
        if (!empty($toDelete)) {
            ParticularMatrixModel::where('particular_type_id', $particularType->id)
                ->whereIn('particular_mode_id', $toDelete)
                ->delete();
        }

        // Insert new operations (if any)
        foreach ($toInsert as $operationId) {
            ParticularMatrixModel::create([
                'config_id'          => $particularType->config_id,
                'particular_type_id' => $particularType->id,
                'particular_mode_id' => $operationId,
            ]);
        }

        // Fetch updated data
        $types = ParticularTypeModel::getParticularType($domain);

        $response = new JsonRequestResponse();
        return $response->returnJosnResponse($types);
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
