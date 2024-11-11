<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\FileUploadRequest;
use Modules\Core\App\Http\Requests\VendorRequest;
use Modules\Core\App\Models\FileUploadModel;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\SettingModel as InventorySettingModel;

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
    public function fileProcessToDB(Request $request){
        /*set_time_limit(0);
        $fileID = $request->file_id;
        $getFile = FileUploadModel::find($fileID);

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        $targetLocation = public_path('/uploads/core/file-upload/');
        $spreadSheet = $reader->load($targetLocation.$getFile->file);
        $excelSheet = $spreadSheet->getActiveSheet();
        $allData = $excelSheet->toArray();

        dump($allData);*/

        set_time_limit(0);
        $fileID = $request->file_id;
        $getFile = FileUploadModel::find($fileID);

        $targetLocation = public_path('/uploads/core/file-upload/');
        $filePath = $targetLocation . $getFile->file;

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        if ($extension == 'xlsx') {
            $reader = new Xlsx();
        } elseif ($extension == 'csv') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        } else {
            throw new Exception('Unsupported file format.');
        }

        $spreadSheet = $reader->load($filePath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $allData = $excelSheet->toArray();

        dump($allData);

        /*$excelFile = ExcelImport::find($id);

        set_time_limit(0);
        $reader = new Xlsx();

        $targetLocation = public_path('upload/excel_file/');
        $spreadSheet = $reader->load($targetLocation.$excelFile->file_name);
        $excelSheet = $spreadSheet->getActiveSheet();
        $allData = $excelSheet->toArray();
        $keys = array_shift($allData); //remove Excel column heading
        $totalHeading = count($keys);
        $keys = array_map('trim', array_filter($keys)); //remove all spaces from string

        if ($excelFile->file_type == 'Ayat'){
            $totalHeading = count($keys);
            if ($totalHeading == 9) {
                foreach ($allData as $data) {
                    $values = array_slice($data, null, count($keys));
                    if ($values[0] && $values[1]) {
                        $suraInfo = Sura::where('name_en',$values[0])->first();
                        if ($suraInfo){
                            $input['sura_id']=$suraInfo->id;
                        }
                        $paraInfo = Para::where('name_en',$values[1])->first();
                        if ($paraInfo){
                            $input['para_id']=$paraInfo->id;
                        }

                        $input['aya_number'] = $values[2];
                        $input['verse_key'] = $input['sura_id'].':'.$input['aya_number'];
                        $input['name_ar'] = $values[3];
                        $input['name_en'] = $values[4];
                        $input['name_bn'] = $values[5];
                        $ayatData = Ayat::create($input);

                        if ($ayatData) {
                            $ayatTafsir = new AyatTafsir();
                            $ayatTafsir->tafsir_en = $values[7];
                            $ayatTafsir->tafsir_bn = $values[8];
                            $ayatTafsir->tafsir_by = $values[6];
                            $ayatData->ayatTafsir()->save($ayatTafsir);
                        }
                    }
                }
                ExcelImport::find($id)->update(['is_import' => 1]);
            } else {
                Session::flash('validate', 'Please follow recommend structure');
                return redirect()->route('excel_import_list', app()->getLocale());
            }
        }*/
    }
}
