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
        if ($getFile->file_type === 'Product' && count($keys) === 13) {
            $isInsert = $this->insertProductsInBatches($allData, $em);
        } else {
            return response()->json([
                'message' => 'Invalid file type or structure',
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

    private function insertProductsInBatches($allData, EntityManagerInterface $em)
    {
        $batchSize = 1000;
        $batch = [];
        $rowsProcessed = 0;

        foreach ($allData as $index => $data) {
            $values = array_map('trim', $data);

            // Fetch related IDs
            $productType = InventorySettingModel::where('slug', 'like', '%' . Str::slug(trim($values[2])) . '%')->first('id');

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
                        'config_id' => $this->domain['config_id'], // Assuming $this->domain['config_id'] exists
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

            $productUnit = ParticularModel::where('name', 'like', '%' . Str::slug(trim($values[8])) . '%')->first('id');

            // Ensure valid data
            if ($productType && $productUnit && $values[5]) {
                $productData = [
                    'code' => !empty($values[0]) ? str_replace("#", "", trim($values[0])) : null,
                    'barcode' => trim($values[1] ?? null),
                    'product_type_id' => $productType->id ?? null,
                    'category_id' => $productCategory->id ?? null,
                    'unit_id' => $productUnit->id ?? null,
                    'name' => trim($values[5] ?? ''),
                    'alternative_name' => !empty(trim($values[6] ?? '')) ? trim($values[6]) : null,
                    'purchase_price' => is_numeric(trim($values[10] ?? '')) ? (float) trim($values[10]) : null,
                    'sales_price' => is_numeric(trim($values[11] ?? '')) ? (float) trim($values[11]) : null,
                    'config_id' => $this->domain['config_id'] ?? null,
                    'status' => 1,
                ];


                $batch[] = $productData;

                // Batch insert when batch size reached
                if (count($batch) === $batchSize) {
                    $rowsProcessed += $this->processBatch($batch, $em);
                    $batch = [];  // Reset batch after processing
                }
            }
        }

        // Process any remaining items
        if (count($batch) > 0) {
            $rowsProcessed += $this->processBatch($batch, $em);
        }

        return ['is_insert' => true, 'row_count' => $rowsProcessed];
    }

    private function processBatch(array $batch, EntityManagerInterface $em)
    {
        $rowCount = 0;

        foreach ($batch as $productData) {
            $product = ProductModel::create($productData);
            $em->getRepository(StockItem::class)->insertStockItem($product->id, $productData);
            $rowCount++;
        }

        return $rowCount;
    }



}
