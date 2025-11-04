<?php

namespace Modules\Hospital\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\FileUploadModel;
use Modules\Core\App\Models\LocationModel;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\UserProfileModel;
use Modules\Core\App\Models\UserWarehouseModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Hospital\App\Models\CategoryModel;
use Modules\Hospital\App\Models\HospitalConfigModel;
use Modules\Hospital\App\Models\InvoiceModel;
use Modules\Hospital\App\Models\MedicineModel;
use Modules\Hospital\App\Models\ParticularMatrixModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularModeModel;
use Modules\Hospital\App\Models\ParticularModuleModel;
use Modules\Hospital\App\Models\ParticularTypeMasterModel;
use Modules\Hospital\App\Models\ParticularTypeModel;
use Modules\Hospital\App\Models\ProductModel;
use Modules\Hospital\App\Models\ShareHealthModel;
use Modules\Inventory\App\Models\ProductBrandModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\SettingModel;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Medicine\App\Models\MedicineGenericModel;
use Modules\Production\App\Models\ProductionItems;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Throwable;

class HospitalController extends Controller
{
    protected $domain;
    private const BATCH_SIZE = 1000; // Process 1000 records at a time
    private const MEMORY_LIMIT = '2G'; // Increase memory limit for large files

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

        $domain = $this->domain['global_id'];
        $entity = DomainModel::with(['accountConfig',
            'accountConfig.capital_investment','accountConfig.account_cash','accountConfig.account_bank','accountConfig.account_mobile','accountConfig.account_user','accountConfig.account_vendor','accountConfig.account_customer','accountConfig.account_product_group','accountConfig.account_category',
            'accountConfig.voucher_stock_opening','accountConfig.voucher_purchase','accountConfig.voucher_sales','accountConfig.voucher_purchase_return','accountConfig.voucher_stock_reconciliation',
            'inventoryConfig','hospitalConfig','inventoryConfig.configPurchase','inventoryConfig.configSales','inventoryConfig.configProduct','inventoryConfig.configDiscount','inventoryConfig.configVat','inventoryConfig.businessModel',
            'inventoryConfig.currency',
            'hospitalConfig.admission_fee:id,name as admission_fee_name,price as admission_fee_price',
            'hospitalConfig.opd_ticket_fee:id,name as opd_ticket_fee_name,price as opd_ticket_fee_price',
            'hospitalConfig.emergency_fee:id,name as emergency_fee_name,price as emergency_fee_price',
            'hospitalConfig.ot_fee:id,name as ot_fee_name,price as ot_fee_price',
            'hospitalConfig.shareHealth',
        ])->find($domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function patientSearch(Request $request)
    {
        $domain = $this->domain;
        $data = InvoiceModel::getCustomerSearch($domain,$request);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($data);
    }

    public function roomCabin(Request $request)
    {

        $data = ParticularModel::getRoomCabin($request, $this->domain);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function userInfo($id)
    {
        $data = ParticularModel::with('particularDetails')->where('employee_id',$id)->first();
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($data);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function storeUser()
    {
        $domain = $this->domain;
        $data = UserModel::getStoreUsers($domain['id']);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($data);
    }

    /**
     * Show the specified resource for edit.
     */
    public function storeUserInlineUpdate(Request $request, $id)
    {

        $ids = json_decode($request->get('store_id'));
        $entity = UserModel::find($id);
        if ($ids && is_array($ids)) {
            // Remove warehouses that are not selected anymore
            UserWarehouseModel::where('user_id', $id)
                ->whereNotIn('warehouse_id', $ids)
                ->delete();

            // Insert or update warehouses in the list
            foreach ($ids as $storeId) {
                UserWarehouseModel::updateOrCreate(
                    [
                        'user_id' => $id,
                        'warehouse_id' => $storeId,
                    ],
                    [
                        'status' => true,
                        'updated_at' => now(),
                    ]
                );
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'success',
            'data'    => $ids,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function locationDropdown(Request $request)
    {
        $data = LocationModel::getLocationSearch($request);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($data);
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
    public function modeMatrix(Request $request)
    {
        $domain = $this->domain;
        $data = ParticularMatrixModel::getLists($domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($data);
    }

    public function matrixUpdateOrdering(Request $request)
    {
        foreach ($request->order as $row) {
            ParticularMatrixModel::where('id', $row['id'])
                ->update(['ordering' => $row['ordering']]);
        }
        return response()->json(['success' => true]);
    }

    public function matrixInlineUpdate(Request $request,$id)
    {
        $input = $request->all();
        $findParticular = ParticularMatrixModel::find($id);
        $findParticular->is_additional_field = $findParticular->is_additional_field ? 0:1;
        $findParticular->save();
        return response()->json(['success' => true]);
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
    public function healthShare(Request $request)
    {
        $userId = $request->header('X-Api-User');
        $domain = $this->domain;
        $config = $domain['hms_config'];
        $data = $request->request->all();
        $entitiy = ShareHealthModel::updateOrCreate(
            [
                'config_id' => $config,
            ],
            [
                'x_auth_token' => $data['x_auth_token'],
                'client_id' => $data['client_id'],
                'email' => $data['email'],
                'nid_url' => $data['nid_url'],
                'patient_url' => $data['patient_url'],
                'health_share_token' => $data['health_share_token'],
                'health_share_url' => $data['health_share_url'],
                'health_share_password' => $data['health_share_password'],
            ]
        );
        $service = new JsonRequestResponse($entitiy);
        return $service->returnJosnResponse($data);

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
    public function particularContentDropdown(Request $request)
    {
        $domain = $this->domain;
        $mode = $request->get('particular_type');
        $dropdown = ParticularModel::getParticularContentDropdown($domain,$mode);
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
        $dropdown = ProductModel::getMedicineDropdown($domain,$term);
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
    public function opdAllOpdDropdown(Request $request)
    {
        $domain = $this->domain;
        $dropdown = InvoiceModel::getAllOpdRooms($domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }

     /**
     * Show the form for editing the specified resource.
     */
    public function opdRoomDropdown(Request $request)
    {
        $domain = $this->domain;
        $dropdown = InvoiceModel::getOpdRooms($domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function opdReferredRoomDropdown(Request $request)
    {
        $domain = $this->domain;
        $dropdown = InvoiceModel::getOpdReferredRooms($domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }

     /**
     * Show the form for editing the specified resource.
     */
    public function particularMasterTypeDropdown()
    {
        $types = ParticularTypeMasterModel::orderBy('name', 'asc')->get();
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


    /**
     * @throws Throwable
     */
    public function insertMedicineStock()
    {
        set_time_limit(0);
        ini_set('memory_limit', '2G');

        $filePath = public_path('/uploads/medicine/medicine.xlsx');
        try {
            // Load file with minimal memory usage
            $reader = match (pathinfo($filePath, PATHINFO_EXTENSION)) {
                'xlsx' => new Xlsx(),
                'csv' => new \PhpOffice\PhpSpreadsheet\Reader\Csv(),
                default => throw new Exception('Unsupported file format.')
            };

            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);

            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            // Get headers from first row
            $headers = [];
            $headerRow = $worksheet->getRowIterator(1, 1)->current();
            foreach ($headerRow->getCellIterator() as $cell) {
                $headers[] = trim($cell->getValue());
            }

            // Optimize MySQL settings for bulk insert
            DB::statement('SET SESSION sql_mode = ""');
            DB::statement('SET SESSION unique_checks = 0');
            DB::statement('SET SESSION foreign_key_checks = 0');
            DB::statement('SET SESSION autocommit = 0');
            DB::disableQueryLog();

            $batchSize = 2500; // Larger batch for raw SQL
            $totalProcessed = 0;
            $errorCount = 0;
            $batch = [];

            // Cache timestamp and config_id
            $currentTimestamp = now()->format('Y-m-d H:i:s');
            $configId = $this->domain['hms_config'];

            // Get table name
            $tableName = (new MedicineModel())->getTable();

            foreach ($worksheet->getRowIterator(2) as $row) {
                $values = [];
                $columnIndex = 0;

                foreach ($row->getCellIterator() as $cell) {
                    if (isset($headers[$columnIndex])) {
                        $values[$headers[$columnIndex]] = $cell->getCalculatedValue();
                    }
                    $columnIndex++;
                }

                // Skip empty rows
                if (empty(array_filter($values, fn($v) => !is_null($v) && $v !== ''))) {
                    continue;
                }

                // Prepare values for raw SQL (escape strings)
                $name = isset($values['name']) ? DB::connection()->getPdo()->quote(trim($values['name'])) : "''";
                $genericId = isset($values['generic_id']) ? (int) $values['generic_id'] : 0;
                $doseDetails = isset($values['dose_details']) ? DB::connection()->getPdo()->quote(trim($values['dose_details'])) : "''";
                $byMeal = isset($values['by_meal']) ? DB::connection()->getPdo()->quote(trim($values['by_meal'])) : "''";
                $durationMonth = isset($values['duration_month']) ? DB::connection()->getPdo()->quote(trim($values['duration_month'])) : "''";
                $durationDay = isset($values['duration_day']) ? (int) $values['duration_day'] : 0;
                $generic = isset($values['generic']) ? DB::connection()->getPdo()->quote(trim($values['generic'])) : "''";
                $company = isset($values['company']) ? DB::connection()->getPdo()->quote(trim($values['company'])) : "''";
                $formulation = isset($values['formulation']) ? DB::connection()->getPdo()->quote(trim($values['formulation'])) : "''";

                $batch[] = "({$configId}, {$name}, {$name}, {$genericId}, {$doseDetails}, {$byMeal}, {$durationMonth}, {$durationDay}, {$generic}, {$company}, {$formulation}, '{$currentTimestamp}', '{$currentTimestamp}')";

                // Execute batch when size is reached
                if (count($batch) >= $batchSize) {
                    try {
                        $sql = "INSERT INTO {$tableName}
                           (config_id, name, display_name, generic_id, dose_details, by_meal, duration_month, duration_day, generic, company, formulation, created_at, updated_at)
                           VALUES " . implode(',', $batch);

                        DB::statement($sql);
                        $totalProcessed += count($batch);

                    } catch (Exception $e) {
                        $errorCount += count($batch);
                        \Log::error("SQL batch insert failed: " . $e->getMessage());
                    }

                    $batch = [];

                    // Memory management
                    if ($totalProcessed % 2500 === 0) {
                        gc_collect_cycles();
                    }
                }
            }

            // Insert remaining records
            if (!empty($batch)) {
                try {
                    $sql = "INSERT INTO {$tableName}
                       (config_id, name, display_name, generic_id, dose_details, by_meal, duration_month, duration_day, generic, company, formulation, created_at, updated_at)
                       VALUES " . implode(',', $batch);

                    DB::statement($sql);
                    $totalProcessed += count($batch);

                } catch (Exception $e) {
                    $errorCount += count($batch);
                    \Log::error("Final SQL batch failed: " . $e->getMessage());
                }
            }



            // Commit all changes
            DB::statement('COMMIT');

            // Reset MySQL settings
            DB::statement('SET SESSION unique_checks = 1');
            DB::statement('SET SESSION foreign_key_checks = 1');
            DB::statement('SET SESSION autocommit = 1');
            DB::enableQueryLog();

            // Clean up memory
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $worksheet, $batch, $values);
            gc_collect_cycles();

            $message = "Import completed! Processed: {$totalProcessed} records, Errors: {$errorCount}";
            \Log::info($message);

            return [
                'success' => true,
                'message' => $message,
                'data' => [
                    'total_processed' => $totalProcessed,
                    'total_errors' => $errorCount
                ]
            ];

        } catch (Exception $e) {
            // Reset MySQL settings on error
            DB::statement('ROLLBACK');
            DB::statement('SET SESSION unique_checks = 1');
            DB::statement('SET SESSION foreign_key_checks = 1');
            DB::statement('SET SESSION autocommit = 1');
            DB::enableQueryLog();

            \Log::error("Medicine import failed: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * @throws Throwable
     */
    public function insertUpazilaDistrictxxx()
    {
        set_time_limit(0);
        ini_set('memory_limit', '2G');

        $filePath = public_path('/uploads/medicine/bss_locaton_format.xlsx');
        try {
            // Load file with minimal memory usage
            $reader = match (pathinfo($filePath, PATHINFO_EXTENSION)) {
            'xlsx' => new Xlsx(),
                'csv' => new \PhpOffice\PhpSpreadsheet\Reader\Csv(),
                default => throw new Exception('Unsupported file format.')
            };

            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);

            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            // Get headers from first row
            $headers = [];
            $headerRow = $worksheet->getRowIterator(1, 1)->current();
            foreach ($headerRow->getCellIterator() as $cell) {
                $headers[] = trim($cell->getValue());
            }

            // Optimize MySQL settings for bulk insert
            DB::statement('SET SESSION sql_mode = ""');
            DB::statement('SET SESSION unique_checks = 0');
            DB::statement('SET SESSION foreign_key_checks = 0');
            DB::statement('SET SESSION autocommit = 0');
            DB::disableQueryLog();

            $batchSize = 2500; // Larger batch for raw SQL
            $totalProcessed = 0;
            $errorCount = 0;
            $batch = [];

            // Cache timestamp and config_id
            $currentTimestamp = now()->format('Y-m-d H:i:s');

            // Get table name
            $tableName = (new LocationModel())->getTable();

          //  dd($worksheet->getRowIterator(2));

            foreach ($worksheet->getRowIterator(2) as $row) {

                $values = [];
                $columnIndex = 0;
                foreach ($row->getCellIterator() as $cell) {
                    if (isset($headers[$columnIndex])) {
                        $values[$headers[$columnIndex]] = $cell->getCalculatedValue();
                    }
                    $columnIndex++;
                }

                // Skip empty rows
                if (empty(array_filter($values, fn($v) => !is_null($v) && $v !== ''))) {
                    continue;
                }

                // Prepare values for raw SQL (escape strings)
                $Upazila = isset($values['Upazila']) ? DB::connection()->getPdo()->quote(trim($values['Upazila'])) : "''";
                $UpazilaCode = isset($values['UpazilaCode']) ?  $values['UpazilaCode'] : 0;
                $District = isset($values['District']) ? DB::connection()->getPdo()->quote(trim($values['District'])) : "''";
                $DistrictCode = isset($values['DistrictCode']) ?  $values['DistrictCode'] : 0;
                $Divison = isset($values['Divison']) ? DB::connection()->getPdo()->quote(trim($values['Divison'])) : "''";
                $DivisonCode = isset($values['DivisonCode']) ? $values['DivisonCode'] : 0;

                $batch[] = "({$Upazila}, {$UpazilaCode},{$District}, {$DistrictCode},{$Divison},{$DivisonCode})";

                // Execute batch when size is reached
                if (count($batch) >= $batchSize) {
                    try {
                        $sql = "INSERT INTO {$tableName}
                           ( upazila, upazila_code, district, district_code, division, division_code,created_at,updated_at)
                           VALUES " . implode(',', $batch);

                        DB::statement($sql);
                        $totalProcessed += count($batch);

                    } catch (Exception $e) {
                        $errorCount += count($batch);
                        \Log::error("SQL batch insert failed: " . $e->getMessage());
                    }

                    $batch = [];

                    // Memory management
                    if ($totalProcessed % 2500 === 0) {
                        gc_collect_cycles();
                    }
                }
            }


            // Commit all changes
            DB::statement('COMMIT');

            // Reset MySQL settings
            DB::statement('SET SESSION unique_checks = 1');
            DB::statement('SET SESSION foreign_key_checks = 1');
            DB::statement('SET SESSION autocommit = 1');
            DB::enableQueryLog();

            // Clean up memory
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $worksheet, $batch, $values);
            gc_collect_cycles();

            $message = "Import completed! Processed: {$totalProcessed} records, Errors: {$errorCount}";
            \Log::info($message);

            return [
                'success' => true,
                'message' => $message,
                'data' => [
                    'total_processed' => $totalProcessed,
                    'total_errors' => $errorCount
                ]
            ];

        } catch (Exception $e) {
            // Reset MySQL settings on error
            DB::statement('ROLLBACK');
            DB::statement('SET SESSION unique_checks = 1');
            DB::statement('SET SESSION foreign_key_checks = 1');
            DB::statement('SET SESSION autocommit = 1');
            DB::enableQueryLog();

            \Log::error("Medicine import failed: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * @throws Throwable
     */
    public function insertUpazilaDistrict()
    {
        set_time_limit(0);
        ini_set('memory_limit', '2G');

        $filePath = public_path('/uploads/medicine/bss_locaton_format.xlsx');
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

            $batchSize = 1000;
            $batch = [];
            $rowsProcessed = 0;

            foreach ($allData as $index => $data) {
                $productData[] = [
                    'upazila'        => $data[1],
                    'upazila_code'   => (int)$data[2],
                    'district'       => trim($data[3]),
                    'district_code'  => (int)($data[4]),
                    'division'       => trim($data[5]),
                    'division_code'  => (int)($data[6]),
                    'created_at'     => now(),   // required if your table uses timestamps
                    'updated_at'     => now(),
                ];
            }

            // Bulk insert
            LocationModel::insert($productData);

          //  self::processProductBatch($productData);
    }



    // product batch upload
    private function processProductBatch(array $batch)
    {
        $rowCount = 0;
        foreach ($batch as $productData) {

            $rowCount++;
        }
        return $rowCount;
    }

    /**
     * dropdown the specified resource from storage.
     */
    public function insertMedicineStockProcess()
    {
        $dropdown = ProductModel::insertExcelProducts($this->domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
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

    /**
     * dropdown the specified resource from storage.
     */
    public function categoryDropdown(Request $request)
    {
        $type = $request->get('type');
        $dropdown = CategoryModel::getCategoryHospitalDropdown($this->domain,$type);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }


    /**
     * @throws Throwable
     */
    public function userImport()
    {
        set_time_limit(0);
        ini_set('memory_limit', '2G');

        $filePath = public_path('/uploads/medicine/user-info.xlsx');

        // 🧭 Detect file type
        $reader = match (pathinfo($filePath, PATHINFO_EXTENSION)) {
            'xlsx' => new Xlsx(),
            'csv'  => new Csv(),
            default => throw new Exception('Unsupported file format.')
        };

        // 📄 Load spreadsheet
        $spreadsheet = $reader->load($filePath);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        if (empty($rows)) {
            throw new Exception('File is empty or invalid.');
        }

        // 🧱 Extract headers
        $headers = array_shift($rows);
        $headers = array_map(fn($h) => trim(strtolower($h)), $headers);

        // 🧾 Optional: track skipped users
        $skippedUsers = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $rowIndex => $row) {
                // 🧩 Map columns → headers
                $mapped = [];
                foreach ($headers as $col => $headerName) {
                    $mapped[$headerName] = $row[$col] ?? null;
                }

                // Skip empty usernames
                if (empty($mapped['username'])) {
                    $skippedUsers[] = ['row' => $rowIndex + 2, 'reason' => 'Missing username'];
                    continue;
                }

                $username = trim($mapped['username'] ?? '');
                $email = trim($mapped['emailaddress'] ?? '');

                // 📧 If email empty → use username
                if (empty($email)) {
                    $email = $username;
                }

                // Add @gmail.com if missing
                if (!str_contains($email, '@')) {
                    $email .= '@gmail.com';
                }

                // Skip duplicates
                if (UserModel::where('email', $email)->exists()) {
                    $skippedUsers[] = ['row' => $rowIndex + 2, 'email' => $email, 'reason' => 'Duplicate email'];
                    continue;
                }

                // 👤 Create User
                $userData = [
                    'domain_id'         => 13,
                    'username'          => $username,
                    'email'             => $email,
                    'name'              => trim($mapped['userfullname'] ?? ''),
                    'mobile'            => trim($mapped['contactnumbermob'] ?? ''),
                    'email_verified_at' => now(),
                    'password'          => Hash::make('@123456'),
                    'is_delete'         => 0,
                    'user_group'        => 'user',
                    'employee_group_id' => 104,
                    'enabled'           => ($mapped['useractive'] ?? 0) == 1 ? 1 : 0,
                ];

                $user = UserModel::create($userData);

                // 🏢 Department logic
                $deptName = trim($mapped['deptname'] ?? '');
                $deptId = null;

                if (!empty($deptName)) {
                    $dept = \Modules\Core\App\Models\SettingModel::firstOrCreate(
                        [
                            'setting_type_id' => 4,
                            'domain_id' => 13,
                            'name' => $deptName,
                        ],
                        [
                            'slug' => Str::slug($deptName),
                            'status' => 1,
                            'is_private' => 0,
                        ]
                    );
                    $deptId = $dept->id;
                }

                $rawDob = trim($mapped['dateofbirth'] ?? '');
                $dob = null;

                if (!empty($rawDob)) {
                    try {
                        $dob = \Carbon\Carbon::parse($rawDob)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $dob = null;
                    }
                }

                // 🧾 User Profile
                $profileData = [
                    'user_id'       => $user->id,
                    'department_id' => $deptId,
                    'name'          => $user->name,
                    'email'         => $user->email,
                    'mobile'        => trim($mapped['contactnumbermob'] ?? ''),
                    'address'       => trim($mapped['fulladdress'] ?? ''),
                    'gender'        => trim($mapped['gender'] ?? ''),
                    'dob'           => $dob,
                ];

                UserProfileModel::create($profileData);
            }

            DB::commit();

            // ✅ Optional: Log skipped users to file
            if (!empty($skippedUsers)) {
                $logPath = storage_path('logs/user_import_skipped_' . now()->format('Ymd_His') . '.log');
                file_put_contents($logPath, json_encode($skippedUsers, JSON_PRETTY_PRINT));
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return "✅ User import completed successfully.";
    }


}
