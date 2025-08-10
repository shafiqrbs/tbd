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
use Modules\Hospital\App\Models\ParticularModuleModel;
use Modules\Hospital\App\Models\ParticularTypeModel;
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
        $domain = $this->domain;
        $mode = $request->get('dropdown-type');
        $dropdown = ParticularModel::getParticularDropdown($domain,$mode);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function particularTypeDropdown(Request $request)
    {
        $domain = $this->domain;
        $types = ParticularTypeModel::all();
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($types);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function particularTypeChildDropdown(Request $request)
    {
        $domain = $this->domain;
        $types = ParticularTypeModel::all();
        $dropdown = [];
        foreach ($types as $type):
            $dropdown[$this->convertCamelCase($type['slug'])] = ParticularModel::getParticularDropdown($domain,$type['slug']);
        endforeach;
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }

    public static function convertCamelCase($str){
        $camelCase = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $str))));
        return $camelCase;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function particularModule(Request $request)
    {
        $types = ParticularModuleModel::all();
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($types);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function particularModeDropdown(Request $request)
    {
        $mode = $request->get('dropdown-type');
        $dropdown = ParticularModeModel::getParticularModuleDropdown($mode);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function particularModuleChildDropdown(Request $request)
    {
        $types = ParticularModuleModel::all();
        $dropdown = [];
        foreach ($types as $type):
            $dropdown[$this->convertCamelCase($type['slug'])] = ParticularModeModel::getParticularModuleDropdown($type['slug']);
        endforeach;
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
