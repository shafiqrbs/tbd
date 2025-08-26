<?php

namespace Modules\Hospital\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\FileUploadModel;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Hospital\App\Models\MedicineModel;
use Modules\Hospital\App\Models\ParticularMatrixModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularModeModel;
use Modules\Hospital\App\Models\ParticularModuleModel;
use Modules\Hospital\App\Models\ParticularTypeMasterModel;
use Modules\Hospital\App\Models\ParticularTypeModel;
use Modules\Inventory\App\Models\ProductBrandModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\SettingModel;
use Modules\Medicine\App\Models\MedicineGenericModel;
use Modules\Production\App\Models\ProductionItems;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

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
        $data = ParticularMatrixModel::getRecords($domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($data);
    }


     /**
     * Show the form for editing the specified resource.
     */
    public function settingMatrixCreate(Request $request)
    {
        $userId = $request->header('X-Api-User');
        $domain = $this->domain;
        $config = $domain['hms_config'];
        $data = $request->request->all();
        ParticularMatrixModel::updateOrCreate(
            [
                'config_id' => $config,
                'module' => $data['module'],
                'module_mode' => $data['module_mode']
            ],
            [
                'created_by_id' => $userId,
                'particular_types' => json_encode($data['particular_types'])
            ]
        );
        $data = ParticularMatrixModel::getRecords($domain);
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
    public function medicineDropdown(Request $request)
    {
        $domain = $this->domain;
        $term = $request->get('term');
        $dropdown = MedicineModel::getMedicineDropdown($domain,$term);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function medicineGenericDropdown(Request $request)
    {
        $term = $request->get('term');
        $dropdown = MedicineGenericModel::getMedicineGenericDropdown($term);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function particularTypeDropdown(Request $request)
    {
        $domain = $this->domain;
        $types = ParticularTypeModel::getParticularType($domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($types);
    }

     /**
     * Show the form for editing the specified resource.
     */
    public function particularMasterTypeDropdown()
    {

        $types = ParticularTypeMasterModel::all();
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
    public function particularModuleChildDropdown()
    {
        $data = ParticularModuleModel::getRecords();
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($data);
    }


    /**
     * dropdown the specified resource from storage.
     */
    public function operationParticularType(Request $request,$id)
    {
        $dropdown = ParticularMatrixModel::getOperationParticularType($this->domain,$id);
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



    // process finish goods product

    public  function insertMedicineStock()
    {
        set_time_limit(0);
        $domain = $this->domain;
        $filePath = public_path('/uploads/medicine/medicine.xlsx');

        // Load file based on extension
        $reader = match (pathinfo($filePath, PATHINFO_EXTENSION)) {
        'xlsx' => new Xlsx(),
            'csv' => new \PhpOffice\PhpSpreadsheet\Reader\Csv(),
            default => throw new Exception('Unsupported file format.')
        };

        $allData = $reader->load($filePath)->getActiveSheet()->toArray();

        // for process data with header
        $spreadsheet = $reader->load($filePath);
        $data = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        // Use the first row as headers
        $headers = array_shift($data);

        // Map rows to headers
        $dataWithHeaders = [];
        foreach ($data as $row) {
            $mappedRow = [];
            foreach ($headers as $column => $headerName) {
                $mappedRow[$headerName] = $row[$column] ?? null; // Use header name as key
            }
            $dataWithHeaders[] = $mappedRow;
        }

        // Remove headers
        $keys = array_map('trim', array_shift($allData));
        // Only proceed if it's 'Product' and structure is correct

        foreach ($dataWithHeaders as $index => $values) {

         //   $values = array_map(fn($item) => is_string($item) ? trim($item) : $item, $data);

            $name = $values['name'] ?? null;
            $generic_id = $values['generic_id'] ?? 0;
            $dose_details = trim($values['dose_details']) ?? null;
            $by_meal = trim($values['by_meal']) ?? null;
            $duration_month = trim($values['duration_month']) ?? null;
            $duration_day = trim($values['duration_day']) ?? 0;
            $generic = trim($values['generic']) ?? null;
            $company = trim($values['company']) ?? null;
            $formulation = trim($values['formulation']) ?? null;
            $batch = [
                'config_id' => $this->domain['hms_config'],
                'name' => $name,
                'display_name' => $name,
                'generic_id' => $generic_id,
                'dose_details' => $dose_details,
                'by_meal' => $by_meal,
                'duration_month' => $duration_month,
                'duration_day' => $duration_day,
                'generic' => $generic,
                'company' => $company,
                'formulation' => $formulation,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            MedicineModel::insert($batch);

        }
    }

    /**
     * dropdown the specified resource from storage.
     */
    public function mealDropdown()
    {
        $dropdown = MedicineModel::getMealDropdown($this->domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }

    /**
     * dropdown the specified resource from storage.
     */
    public function dosageDropdown()
    {
        $dropdown = MedicineModel::getDosageDropdown($this->domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }




}
