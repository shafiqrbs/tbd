<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Accounting\App\Models\AccountHeadMasterModel;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Accounting\App\Models\AccountJournalItemModel;
use Modules\Accounting\App\Models\AccountVoucherModel;
use Modules\Accounting\App\Models\SettingModel;
use Modules\Accounting\App\Models\SettingTypeModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;


class AccountingController extends Controller
{

    protected $domain;

    public function __construct(Request $request)
    {
        $entityId = $request->header('X-Api-User');
        if ($entityId && !empty($entityId)){
            $entityData = UserModel::getUserData($entityId);
            $this->domain = $entityData;
        }
    }


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
     * Show the form for editing the specified resource.
     */
    public function settingTypeDropdown(Request $request)
    {
        $dropdown = SettingTypeModel::all();
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function accountVoucherDropdown(Request $request)
    {
        $dropdown = AccountVoucherModel::getEntityDropdown($request,$this->domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }

     /**
     * Show the form for editing the specified resource.
     */
    public function accountHeadDropdown(Request $request)
    {
        $mode = $request->get('dropdown-type');
        $dropdown = AccountHeadModel::getAccountHeadDropdown($this->domain,$mode);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }
    public function accountHeadForReconciliationDropdown(Request $request)
    {
        $mode = $request->get('dropdown-type');
        $dropdown = AccountJournalItemModel::getAccountHeadForJournalItemReconciliation();
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function accountHeadMasterDropdown(Request $request)
    {
        $dropdown = AccountHeadMasterModel::getAccountHeadMasterDropdown();
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function accountLedgerDropdown(Request $request)
    {
        $mode = $request->get('dropdown-type');
        $dropdown = AccountHeadModel::getAccountLedgerDropdown($this->domain,$mode);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function accountAllDropdownBySlug(Request $request)
    {
        $mode = $request->get('dropdown-type');
        $dropdown = AccountHeadModel::getAccountAllDropdownBySlug($this->domain,$mode);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);

    }



}
