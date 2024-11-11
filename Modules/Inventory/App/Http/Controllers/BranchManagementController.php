<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Http\Requests\ParticularRequest;
use Modules\Inventory\App\Models\ParticularModel;
use Modules\Inventory\App\Models\ParticularTypeModel;




class BranchManagementController extends Controller
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $service = new JsonRequestResponse();
        $input['config_id'] = $this->domain['config_id'];
        $entity = ParticularModel::create($input);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }



}
