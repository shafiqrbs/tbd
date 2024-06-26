<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Utility\App\Models\SettingModel;

class AccountingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('accounting::index');
    }

    /**
     * dropdown the specified resource from storage.
     */
    public function transactionMethodDropdown(Request $request)
    {
        $dropdown = SettingModel::getEntityDropdown('method');
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }



}
