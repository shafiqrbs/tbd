<?php

namespace Modules\Utility\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $entities = SettingModel::getEntityDropdown($mode);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $entities
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function productUnitDropdown(Request $request)
    {

        $entities = ProductUnitModel::getEntityDropdown();
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $entities
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }



}
