<?php

namespace Modules\Hospital\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularModeModel;
use Modules\Inventory\App\Models\ProductBrandModel;
use Modules\Inventory\App\Models\SettingModel;

class HospitalController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if ($userId && !empty($userId)){
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }

    /**
     * Display a listing of the resource.
     */

    public static function index()
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function particularDropdown(Request $request)
    {
        $mode = $request->get('dropdown-type');
        $dropdown = ParticularModel::getParticularModuleDropdown($mode);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function particularModuleDropdown(Request $request)
    {
        $mode = $request->get('dropdown-type');
        $dropdown = ParticularModeModel::getParticularModuleDropdown($mode);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }


    /**
     * dropdown the specified resource from storage.
     */
    public function brandDropdown(Request $request)
    {
        $dropdown = ProductBrandModel::getEntityDropdown($this->domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }



}
