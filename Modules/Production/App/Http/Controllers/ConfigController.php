<?php

namespace Modules\Production\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\JsonRequestResponse;

use Modules\Core\App\Models\UserModel;
use Modules\Production\App\Entities\Config;
use Modules\Production\App\Http\Requests\ConfigRequest;
use Modules\Production\App\Http\Requests\SettingRequest;
use Modules\Production\App\Models\ProductionConfig;
use Modules\Production\App\Models\SettingModel;
use Modules\Production\App\Models\SettingTypeModel;
use function Symfony\Component\HttpFoundation\Session\Storage\Handler\getInsertStatement;

class ConfigController extends Controller
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

    /**
     * Show the specified resource for edit.
     */
    public function show()
    {
        $entity = ProductionConfig::where('domain_id',$this->domain['global_id'])->first();
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
    public function update(ConfigRequest $request)
    {
        $data = $request->validated();
        $entity = ProductionConfig::where('domain_id',$this->domain['global_id'])->first();
        $entity->update($data);

        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }




    public function configDropdown()
    {
        $data = SettingModel::getConfigDropdown($this->domain);
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
