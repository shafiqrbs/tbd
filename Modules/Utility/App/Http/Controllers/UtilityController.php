<?php

namespace Modules\Utility\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Inventory\App\Models\ProductBrandModel;
use Modules\Utility\App\Models\ProductUnitModel;
use Modules\Utility\App\Models\SettingModel;

class UtilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('utility::index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function settingDropdown(Request $request)
    {
        $mode = $request->get('dropdown-type');
        $dropdown = SettingModel::getEntityDropdown($mode);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function productUnitDropdown(Request $request)
    {

        $dropdown = ProductUnitModel::getEntityDropdown();
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }



}
