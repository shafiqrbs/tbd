<?php

namespace Modules\Hospital\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\DomainModel;
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

    public function domainHospitalConfig()
    {
        $entity = DomainModel::with(['accountConfig',
            'accountConfig.capital_investment','accountConfig.account_cash','accountConfig.account_bank','accountConfig.account_mobile','accountConfig.account_user','accountConfig.account_vendor','accountConfig.account_customer','accountConfig.account_product_group','accountConfig.account_category',
            'accountConfig.voucher_stock_opening','accountConfig.voucher_purchase','accountConfig.voucher_sales','accountConfig.voucher_purchase_return','accountConfig.voucher_stock_reconciliation',
            'inventoryConfig','hospitalConfig','inventoryConfig.currency',
            'hospitalConfig.admission_fee:id,name as admission_fee_name,price as admission_fee_price',
            'hospitalConfig.opd_ticket_fee:id,name as ticket_fee_name,price as ticket_fee_price',
            'hospitalConfig.emergency_fee:id,name as emergency_fee_name,price as emergency_fee_price',
            'hospitalConfig.ot_fee:id,name as ot_fee_name,price as ot_fee_price',
        ])->find($this
            ->domain['global_id']);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function settingMatrix(Request $request)
    {
        $domain = $this->domain;
        dd($request->request->all());
        $data = 'success';
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($data);
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
        $types = ParticularTypeModel::where('config_id',$domain['hms_config'])->orderBy('id','DESC')->get();
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($types);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function particularTypeChildDropdown(Request $request)
    {
        $domain = $this->domain;
        $types = ParticularTypeModel::getRecords($domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($types);
    }

    public static function convertCamelCase($str){
        $camelCase = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $str))));
        return $camelCase;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function particularModuleDropdown(Request $request)
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
