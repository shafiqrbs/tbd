<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Models\AccountTransactionModel;
use Modules\Inventory\App\Models\ProductBrandModel;
use Modules\Utility\App\Models\SettingModel;


class InventoryController extends Controller
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
    public function index()
    {
        return view('inventory::index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function settingDropdown(Request $request)
    {
        $mode = $request->get('dropdown-type');
        $dropdown = SettingModel::getSettingDropdown($mode);
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

    /**
     * dropdown the specified resource from storage.
     */
    public function categoryGroupDropdown(Request $request)
    {
        $dropdown = AccountTransactionModel::getCategoryGroupDropdown($this->domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }

    /**
     * dropdown the specified resource from storage.
     */
    public function categoryDropdown(Request $request)
    {
        $dropdown = AccountTransactionModel::getCategoryDropdown($this->domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }



}
