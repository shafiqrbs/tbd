<?php

namespace Modules\Hospital\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DailyStockService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\CustomerRequest;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\UserModel;
use Modules\Hospital\App\Entities\Prescription;
use Modules\Hospital\App\Http\Requests\IpdRequest;
use Modules\Hospital\App\Http\Requests\OPDRequest;
use Modules\Hospital\App\Http\Requests\ReferredRequest;
use Modules\Hospital\App\Models\AdmissionPatientModel;
use Modules\Hospital\App\Models\AdmissionPatientPrescriptionHistory;
use Modules\Hospital\App\Models\AdmissionPatientPrescriptionHistoryModel;
use Modules\Hospital\App\Models\HospitalConfigModel;
use Modules\Hospital\App\Models\HospitalSalesModel;
use Modules\Hospital\App\Models\InvoiceContentDetailsModel;
use Modules\Hospital\App\Models\InvoiceModel;
use Modules\Hospital\App\Models\InvoiceParticularModel;
use Modules\Hospital\App\Models\InvoicePatientReferredModel;
use Modules\Hospital\App\Models\InvoiceTransactionModel;
use Modules\Hospital\App\Models\IpdModel;
use Modules\Hospital\App\Models\OPDModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularModeModel;
use Modules\Hospital\App\Models\PatientModel;
use Modules\Hospital\App\Models\PatientPrescriptionMedicineDailyHistoryModel;
use Modules\Hospital\App\Models\PatientPrescriptionMedicineModel;
use Modules\Hospital\App\Models\PrescriptionModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use function Symfony\Component\TypeInfo\null;


class IpdController extends Controller
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
    public function index(Request $request){
        $domain = $this->domain;
        InvoiceParticularModel::getPatientCountBedRoom($domain);
        $data = IpdModel::getRecords($request,$domain);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message'   => 'success',
            'status'    => Response::HTTP_OK,
            'total'     => $data['count'],
            'data'      => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Display a listing of the resource.
     */
    public function ipdConfirm(Request $request){
        $domain = $this->domain;
        $data = IpdModel::getIpdConfirmRecords($request,$domain);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message'   => 'success',
            'status'    => Response::HTTP_OK,
            'total'     => $data['count'],
            'data'      => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(IpdRequest $request)
    {
        $domain = $this->domain;
        $input = $request->validated();
        $parentInvoice = InvoiceModel::where('uid',$input['hms_invoice_id'])->first();
        DB::beginTransaction();
        try {

            $input['config_id'] = $domain['hms_config'];
            $input['parent_id'] = $parentInvoice->id;
            $input['customer_id'] = $parentInvoice->customer_id;
            $input['created_by_id'] = $domain['user_id'];
            $input['process'] = 'done';
            $patient_mode_id = ParticularModeModel::firstWhere([
                ['slug', 'ipd'],
                ['particular_module_id', 3],
            ])->id;
            $input['patient_mode_id'] = $patient_mode_id;
            $entity = IpdModel::create($input);
            IpdModel::insertHmsInvoice($domain,$parentInvoice,$entity,$input);
            DB::commit();
            $invoice = InvoiceModel::getShow($entity->id);
            $service = new JsonRequestResponse();
            return $service->returnJosnResponse($invoice);
        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();

            // Optionally log the exception for debugging purposes
            \Log::error('Error storing domain and related data: ' . $e->getMessage());

            // Return an error response
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'An error occurred while saving the domain and related data.',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }

    }

    /**
     * Show the specified resource.
     */

    public function show($id)
    {

        $entity = InvoiceModel::getIpdShow($id);
        if (!$entity){
            $entity = 'not_found';
        }
        $amount = InvoiceTransactionModel::where('hms_invoice_id', $entity->id)->where('process','Done')->sum('amount');
        $total = InvoiceParticularModel::where('hms_invoice_id', $entity->id)->where('status',true)->sum('sub_total');
        InvoiceParticularModel::getCountBedRoom($entity->id);
        $entity->update(['sub_total'=> $total ,'total' => $total,'amount' => $amount]);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the specified resource.
     */

    public function ipdAdmissionShow($id)
    {

        $invoice = IpdModel::findByIdOrUid($id);
        if (!$invoice){
            $entity = 'not_found';
        }
        $amount = InvoiceTransactionModel::where('hms_invoice_id', $invoice->id)->where('process','Done')->sum('amount');
        $total = InvoiceParticularModel::where('hms_invoice_id', $invoice->id)->where('status',1)->sum('sub_total');
        InvoiceParticularModel::getCountBedRoom($invoice->id);
        $invoice->update(['sub_total'=> $total ,'total' => $total,'amount' => $amount]);
        $entity = IpdModel::getIpdAdmissionShow($invoice->id);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($entity);
        return $data;
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function admissionRevised(Request $request,$id)
    {
        $domain = $this->domain;
        $input = $request->all();
        $entity = InvoiceModel::findByIdOrUid($id);
        $changeMode = in_array($input['change_mode'] ?? '', ['change','change_day','cancel'])
            ? $input['change_mode']
            : 'change';
        AdmissionPatientModel::updateOrCreate(
            ['hms_invoice_id' => $entity->id],
            [
                'approved_by_id' => $domain['user_id'],
                'change_mode'   => $changeMode ?? 'change',
                'comment'       => $input['comment'] ?? null,
            ]
        );
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($entity);
        return $data;


    }

    /**
     * Show the form for editing the specified resource.
     */
    public function admissionChange(Request $request,$id)
    {
        $domain = $this->domain;
        $input = $request->all();
        $entity = InvoiceModel::findByIdOrUid($id);
        $changeMode = in_array($input['change_mode'] ?? '', ['change','day_change','cancel'])
            ? $input['change_mode']
            : 'change';
        AdmissionPatientModel::updateOrCreate(
            ['hms_invoice_id' => $entity->id],
            [
                'created_by_id' => $domain['user_id'],
                'change_mode'   => $changeMode ?? 'change',
                'comment'       => $input['comment'] ?? null,
            ]
        );
        $entity->update(['process'=> 'revised']);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($entity);
        return $data;


    }

    /**
     * Show the form for editing the specified resource.
     */
    public function release($id,$mode)
    {
        $entity = InvoiceModel::findByIdOrUid($id);
        $entity->update(['release_mode'=>$mode]);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
       // $data = $request->validated();
        $user = $this->domain['user_id'];
        $data = $request->all();
        $data['admitted_by_id'] = $user;
        IpdModel::updateIpdInvoice($id,$data);
        $entity = InvoiceModel::getIpdShow($id);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }


    /**
    * Update the specified resource in storage.
    */
    public function ipdDataProcess(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $domain = $this->domain;
            $data = $request->all();
            $content = $data['json_content'];
            $module = $data['ipd_module'];
            if ($module == 'medicine') {
                InvoiceTransactionModel::insertIpdMedicines($domain, $id, $content);
            }
            if ($module == 'investigation') {
                InvoiceTransactionModel::insertIpdInvestigations($domain, $id, $content);
            }
            if ($module == 'issue-medicine' && $request['warehouse_id']) {
                $salesId = PatientPrescriptionMedicineDailyHistoryModel::insertDailyMedicine($domain, $id, $content , $request['warehouse_id']);
                if ($salesId){
                    $sales = SalesModel::with('salesItems')->find($salesId);
                    if (!$sales) {
                        return response()->json([
                            'message' => 'Sales record not found',
                            'status' => ResponseAlias::HTTP_NOT_FOUND
                        ], ResponseAlias::HTTP_NOT_FOUND);
                    }
                    foreach ($sales->salesItems as $item) {
                        // Validate update
                        if (!$item->warehouse_id) {
                            throw new Exception("Warehouse update failed for item ID: {$item->id}");
                        }

                        //--- STOCK HISTORY
                        StockItemHistoryModel::openingStockQuantity(
                            $item,
                            'sales',
                            $domain
                        );

                        //----DAILY STOCK MAINTAIN
                        DailyStockService::maintainDailyStock(
                            date: now()->format('Y-m-d'),
                            field: 'sales_quantity',
                            configId: $domain['config_id'],
                            warehouseId: $item->warehouse_id,
                            stockItemId: $item->stock_item_id,
                            quantity: $item->quantity
                        );
                    }

                    $sales->update([
                        'approved_by_id' => $this->domain['user_id'],
                        'process' => 'Closed'
                    ]);
                }
            }
            if ($module == 'room') {
                InvoiceTransactionModel::insertIpdRoom($domain, $id, $content);
            }
            if ($module == 'advice') {
                InvoiceTransactionModel::adviceIpdRoom($domain, $id, $content);
            }
            $entity = InvoiceModel::getIpdShow($id);
            DB::commit();
            $service = new JsonRequestResponse();
            return $service->returnJosnResponse($entity);
        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();

            // Optionally log the exception for debugging purposes
            \Log::error('Error storing domain and related data: ' . $e->getMessage());

            // Return an error response
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'An error occurred while saving the domain and related data.',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }
    }



    /**
     * Update the specified resource in storage.
     */
    public function transaction(Request $request, $id)
    {
        $data = $request->all();
        if($data['mode'] == "medicine"){
            $entity = PatientPrescriptionMedicineModel::getPatientIpdMedicine($id);
        }elseif($data['mode'] == "investigation"){
            $entity = InvoiceParticularModel::getGroupParticulars($id);
        }
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }



    /**
     * Update the specified resource in storage.
     */
    public function patientChart(Request $request, $id)
    {
        $data = $request->all();
        $entity = InvoiceModel::where('uid',$id)->first();
        IpdModel::patientChart($entity->id,$data);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse('success');
    }

    /**
     * Show the specified resource.
     */
    public function efreshOrder($id)
    {

        $entity = InvoiceModel::where('uid',$id)->first();
        if (!$entity){
            $entity = 'not_found';
        }
        $prescription = PrescriptionModel::where('hms_invoice_id',$entity->id)->first();
        $data = $prescription->json_content;
        AdmissionPatientPrescriptionHistoryModel::insert($this->domain,$entity->id,$prescription->id,$data);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($entity);
        return $data;
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        OPDModel::find($id)->delete();
        $entity = ['message'=>'delete'];
        return $service->returnJosnResponse($entity);
    }


}
