<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\FileUploadRequest;
use Modules\Core\App\Http\Requests\VendorRequest;
use Modules\Core\App\Models\FileUploadModel;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Inventory\App\Entities\StockItem;
use Modules\Inventory\App\Models\CategoryModel;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\ParticularModel;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\SettingModel as InventorySettingModel;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Production\App\Models\ProductionBatchItemnModel;
use Modules\Production\App\Models\ProductionElements;
use Modules\Production\App\Models\ProductionItems;
use Modules\Production\App\Models\ProductionValueAdded;
use Modules\Production\App\Models\SettingModel;
use Modules\Utility\App\Models\ProductUnitModel;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class FileUploadController extends Controller
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
    public function index(Request $request){

        $data = FileUploadModel::getRecords($request,$this->domain);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
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
     * Store a newly created resource in storage.
     */
    public function store(FileUploadRequest $request)
    {
        $data = $request->validated();

        // Start the transaction.
        DB::beginTransaction();

        try {
            $data['domain_id'] = $this->domain->global_id;
            if ($request->file('file')) {
                $data['original_name'] = $request->file('file')->getClientOriginalName();
                $file = $this->processFileUpload($request->file('file'), '/uploads/core/file-upload/');
                if ($file) {
                    $data['file'] = $file;
                }
            }

            $entity = FileUploadModel::create($data);

            // If we got this far, everything is okay, commit the transaction.
            DB::commit();

            // Return a json response using your service.
            $service = new JsonRequestResponse();
            return $service->returnJosnResponse($entity);

        } catch (Exception $e) {
            // If there's an exception, rollback the transaction.
            DB::rollBack();

            // Optionally log the exception (for debugging purposes)
            \Log::error('Error updating domain and inventory settings: '.$e->getMessage());

            // Return an error response.
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'An error occurred while updating.',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }
    }

    private function processFileUpload($file, $uploadDir)
    {
        if ($file) {
            $uploadDirPath = public_path($uploadDir);

            // Ensure that the directory exists
            if (!file_exists($uploadDirPath)) {
                mkdir($uploadDirPath, 0777, true); // Recursively create the directory with full permissions
            }

            // Generate a unique file name with timestamp
            $fileName = time() . '.' . $file->extension();

            // Move the uploaded file to the target location
            $file->move($uploadDirPath, $fileName);

            return $fileName;
        }

        return null;
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        VendorModel::find($id)->delete();

        $entity = ['message'=>'delete'];
        return $service->returnJosnResponse($entity);

    }

    /**
     * process file data to DB.
     */

    public function fileProcessToDB(Request $request, EntityManagerInterface $em)
    {
        set_time_limit(0);
        $fileID = $request->file_id;
        $getFile = FileUploadModel::find($fileID);

        $filePath = public_path('/uploads/core/file-upload/') . $getFile->file;

        // Load file based on extension
        $reader = match (pathinfo($filePath, PATHINFO_EXTENSION)) {
            'xlsx' => new Xlsx(),
            'csv' => new \PhpOffice\PhpSpreadsheet\Reader\Csv(),
            default => throw new Exception('Unsupported file format.')
        };

        $allData = $reader->load($filePath)->getActiveSheet()->toArray();

        // Remove headers
        $keys = array_map('trim', array_shift($allData));
        // Only proceed if it's 'Product' and structure is correct
        if ($getFile->file_type === 'Product' && count($keys) === 12) {
            $isInsert = $this->insertProductsInBatches($allData, $em);
        }elseif ($getFile->file_type === 'Production' && count($keys) === 8 ){
            $isInsert = $this->insertProductionInBatches($allData, $em);
        } else {
            if ($getFile->file_type === 'Product'){
                $message = 'Invalid file type or structure or column expect 12 , its '.count($keys).' given.';
            }elseif ($getFile->file_type === 'Production'){
                $message = 'Invalid file type or structure or column expect 7 , its '.count($keys).' given.';
            }else{
                $message = 'Invalid file type or structure.';
            }
            return response()->json([
                'message' => $message,
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($isInsert['is_insert']) {
            $getFile->update(['is_process' => true, 'process_row' => $isInsert['row_count']]);

            return response()->json([
                'message' => 'success',
                'status' => Response::HTTP_OK,
                'row' => $isInsert['row_count']
            ], Response::HTTP_OK);
        }
    }


    // for production batch process for upload
    private function insertProductionInBatches($allData, EntityManagerInterface $em)
    {
        $batchSize = 1000;
        $batch = [];
        $rowsProcessed = 0;


        foreach ($allData as $index => $data) {
            $values = array_map('trim', $data);

            // Fetch related IDs
            $stockItemBarcode = str_replace("#", "", trim($values[0]));
            $stockItem = StockItemModel::where('barcode',$stockItemBarcode)->where('config_id',$this->domain['config_id'])->first('id');

            $materialItemBarcode = str_replace("#", "", trim($values[2]));
            $materialItem = StockItemModel::where('barcode',$materialItemBarcode)->where('config_id',$this->domain['config_id'])->first(['id','purchase_price']);

            if ($stockItem && $materialItem && $values[4]) {
                // handle material item unit
                $materialItemUnit = ParticularModel::where('slug', 'like', '%' . Str::slug(trim($values[5])) . '%')->where('config_id',$this->domain['config_id'])->first(['id','name']);

                $productionData = [
                    'item_id' => $stockItem->id,
                    'material_id' => $materialItem->id,
                    'quantity' => trim($values[4]),
                    'price' => $materialItem->purchase_price ?? 0,
                    'purchase_price' => $materialItem->purchase_price ?? 0,
                    'sub_total' => $materialItem->purchase_price*trim($values[4]),
                    'config_id' => $this->domain['pro_config'],
                    'unit_id' => $materialItemUnit->id,
                    'uom' => $materialItemUnit->name,
                    'value_added' => trim($values[7]),
                    'status' => true,
                ];

                if (trim($values[6]) && !empty(trim($values[6]))){
                    $wastagePercent = trim($values[6]) ?? null;
                    $wastageQuantity = ((trim($values[4])*trim($values[6]))/100) ?? null;
                    $productionData['wastage_percent'] = $wastagePercent;
                    $productionData['wastage_quantity'] = $wastageQuantity;
                    $productionData['wastage_amount'] = $wastageQuantity*$productionData['price'];
                }

                $batch[] = $productionData;

                // Batch insert when batch size reached
                if (count($batch) === $batchSize) {
                    $rowsProcessed += $this->processProductionBatch($batch, $em);
                    $batch = [];  // Reset batch after processing
                }
            }
        }

        // Process any remaining items
        if (count($batch) > 0) {
            $rowsProcessed += $this->processProductionBatch($batch, $em);
        }

        return ['is_insert' => true, 'row_count' => $rowsProcessed];
    }

    // product batch upload
    private function processProductionBatch(array $batch, EntityManagerInterface $em)
    {
        $rowCount = 0;

        foreach ($batch as $item) {
            // production item exists
            $productionItem = ProductionItems::where('item_id', $item['item_id'])
                ->where('config_id', $item['config_id'])
                ->first();
            // production item not exists then create production item
            if (!$productionItem) {
                $item['is_delete'] = 0;
                $productionItem = ProductionItems::create($item);
            }

            // check material item exists
            $materialItemExists = ProductionElements::where('production_item_id', $productionItem->id)
                ->where('config_id', $item['config_id'])
                ->where('material_id',$item['material_id'])
                ->first();

            if ($materialItemExists) {
                // update material item
                $materialItemExists->update($item);
            }else {
                // create material item
                $item['production_item_id'] = $productionItem->id;
                ProductionElements::create($item);
            }

            // manage value added
            $this->valueAddedInsetAndUpdate($item['value_added'],$productionItem);

            // Calculate totals for the production item and update
            $totals = ProductionElements::where('production_item_id', $productionItem->id)
                ->where('config_id', $item['config_id'])
                ->selectRaw('
                SUM(quantity) as material_quantity,
                SUM(sub_total) as material_amount,
                SUM(wastage_quantity) as total_wastage_quantity,
                SUM(wastage_amount) as total_wastage_amount,
                SUM(wastage_percent) as total_wastage_percent
            ')
                ->first();

            $productionItem->update([
                'quantity' => $totals->material_quantity+$totals->total_wastage_quantity,
                'price' => null,
                'process' => 'created',
                'sub_total' => $totals->material_amount,
                'material_quantity' => $totals->material_quantity,
                'material_amount' => $totals->material_amount,
                'waste_material_quantity' => $totals->total_wastage_quantity,
                'waste_amount' => $totals->total_wastage_amount,
                'waste_percent' => $totals->total_wastage_percent,
            ]);

            $rowCount++;
        }

        return $rowCount;
    }

    private function valueAddedInsetAndUpdate($valueAdded, $productionItem)
    {
        //  Remove the curly braces
        $valueAdded = trim($valueAdded, '{}');

        // Split the string into key-value pairs
        $pairs = explode(',', $valueAdded);

        $valueAddedArray = [];
        // Iterate over the key-value pairs and populate the array
        foreach ($pairs as $pair) {
            list($key, $value) = explode('=>', $pair);
            $valueAddedArray[trim($key)] = trim($value);
        }

        $getMeasurementInputGenerate = SettingModel::getMeasurementInput($this->domain['pro_config']);
        if (!empty($getMeasurementInputGenerate)) {
            foreach ($getMeasurementInputGenerate as $value) {
                if (isset($value['id'])) {
                    DB::transaction(function () use ($productionItem, $value) {
                        ProductionValueAdded::lockForUpdate()->firstOrCreate([
                            'production_item_id' => $productionItem->id,
                            'value_added_id' => $value['id'],
                        ]);
                    });
                }
            }
        }

        // Update value added
        if (!empty($valueAddedArray)) {
            foreach ($valueAddedArray as $key => $amount) {
                DB::transaction(function () use ($productionItem, $key, $amount) {
                    $findCoreSetting = SettingModel::where('config_id', $this->domain['pro_config'])
                        ->where('slug', $key)
                        ->first(['id']);

                    if ($findCoreSetting && $amount) {
                        ProductionValueAdded::lockForUpdate()->firstOrCreate([
                            'production_item_id' => $productionItem->id,
                            'value_added_id' => $findCoreSetting->id,
                        ])->update([
                            'amount' => $amount,
                        ]);
                    }
                });
            }
        }
    }

    // for product batch process for upload
    private function insertProductsInBatches($allData, EntityManagerInterface $em)
    {
        $batchSize = 1000;
        $batch = [];
        $rowsProcessed = 0;

        foreach ($allData as $index => $data) {
            $values = array_map('trim', $data);

            // Fetch related IDs
            $productType = InventorySettingModel::where('slug', 'like', '%' . Str::slug(trim($values[2])) . '%')->where('config_id',$this->domain['config_id'])->first('id');

            // Trim and Slug Values Once
            $parentCategoryName = trim($values[3] ?? null); // Avoid undefined index issues
            $productCategoryName = trim($values[4] ?? null);

            $parentSlug = $parentCategoryName ? Str::slug($parentCategoryName) : null;
            $productSlug = $productCategoryName ? Str::slug($productCategoryName) : null;

            // Handle Parent Category
            if ($parentSlug && !empty($parentCategoryName)) {
                $parentCategory = CategoryModel::where('slug', $parentSlug)->where('config_id',$this->domain['config_id'])->first('id');
                if (!$parentCategory) {
                    $parentCategory = CategoryModel::create([
                        'config_id' => $this->domain['config_id'],
                        'name' => $parentCategoryName,
                        'slug' => $parentSlug,
                        'status' => 1,
                        'parent' => null // Parent category has no parent
                    ]);

                    // Check for Ledger Existence and Insert if Necessary
                    $ledgerExist = AccountHeadModel::where('category_id', $parentCategory->id)
                        ->where('config_id', $this->domain['acc_config'])->first();

                    if (empty($ledgerExist)) {
                        AccountHeadModel::insertCategoryLedger($this->domain['acc_config'], $parentCategory);
                    }
                }
            }

            // Handle Product Category
            if ($productSlug && !empty($productCategoryName)) {
                $productCategory = CategoryModel::where('slug', $productSlug)->where('config_id',$this->domain['config_id'])->first('id');
                if (!$productCategory) {
                    $productCategory = CategoryModel::create([
                        'config_id' => $this->domain['config_id'],
                        'name' => $productCategoryName,
                        'slug' => $productSlug,
                        'status' => 1,
                        'parent' => ($parentSlug && isset($parentCategory->id)) ? $parentCategory->id : null
                    ]);

                    // Check for Ledger Existence and Insert if Necessary
                    $ledgerExist = AccountHeadModel::where('category_id', $productCategory->id)
                        ->where('config_id', $this->domain['acc_config'])->first();

                    if (empty($ledgerExist)) {
                        AccountHeadModel::insertCategoryLedger($this->domain['acc_config'], $productCategory);
                    }
                }
            }

            $productUnit = ParticularModel::where('name', 'like', '%' . Str::slug(trim($values[9])) . '%')->where('config_id',$this->domain['config_id'])->first('id');
            if (!$productUnit) {
                $productUnit = ParticularModel::create([
                    'config_id' => $this->domain['config_id'],
                    'particular_type_id' => 1,
                    'name' => trim($values[9]),
                    'slug' => Str::slug(trim($values[9])),
                    'status' => true
                ]);
            }
            // Ensure valid data
            if ($productType && $values[5]) {
                $productData = [
                    'code' => !empty($values[0]) ? str_replace("#", "", trim($values[0])) : null,
                    'barcode' => !empty($values[1]) ? str_replace("#", "", trim($values[1])) : null,
                    'product_type_id' => $productType->id ?? null,
                    'category_id' => $productCategory->id ?? null,
                    'unit_id' => $productUnit->id ?? null,
                    'name' => trim($values[5]),
                    'item_size' => trim($values[8] ?? null),
                    'alternative_name' => !empty(trim($values[6])) ? trim($values[6]) : null,
                    'bangla_name' => !empty(trim($values[7])) ? trim($values[7]) : null,
                    'purchase_price' => is_numeric(trim($values[10])) ? (float) trim($values[10]) : 0,
                    'sales_price' => is_numeric(trim($values[11])) ? (float) trim($values[11]) : 0,
                    'config_id' => $this->domain['config_id'] ?? null,
                    'status' => 1,
                ];


                $batch[] = $productData;

                // Batch insert when batch size reached
                if (count($batch) === $batchSize) {
                    $rowsProcessed += $this->processProductBatch($batch, $em);
                    $batch = [];  // Reset batch after processing
                }
            }
        }

        // Process any remaining items
        if (count($batch) > 0) {
            $rowsProcessed += $this->processProductBatch($batch, $em);
        }

        return ['is_insert' => true, 'row_count' => $rowsProcessed];
    }

    // product batch upload
    private function processProductBatch(array $batch, EntityManagerInterface $em)
    {
        $rowCount = 0;

        foreach ($batch as $productData) {
            $product = ProductModel::where('name', $productData['name'])
                ->where('config_id', $productData['config_id'])
                ->first();

            if (!$product) {
                // Create the product if it doesn't exist
                $product = ProductModel::create($productData);

                // Insert stock item for newly created product (new record)
                $em->getRepository(StockItem::class)->insertStockItem($product->id, $productData);
            } else {
                // Insert stock item for newly created product (new record)
                $productData['product_id'] = $product->id;
                $productData['display_name'] = $productData['alternative_name'];
                StockItemModel::create($productData);
            }

            $rowCount++;
        }

        return $rowCount;
    }



}
