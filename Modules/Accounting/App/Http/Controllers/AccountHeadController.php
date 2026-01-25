<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ReportExportService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Modules\Accounting\App\Entities\AccountHead;
use Modules\Accounting\App\Entities\AccountVoucher;
use Modules\Accounting\App\Http\Requests\AccountHeadRequest;
use Modules\Accounting\App\Models\AccountHeadDetailsModel;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\Accounting\App\Models\AccountJournalItemModel;
use Modules\Accounting\App\Models\AccountVoucherModel;
use Modules\Accounting\App\Models\LedgerDetailsModel;
use Modules\Accounting\App\Models\TransactionModeModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Domain\App\Http\Requests\DomainRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Production\App\Models\ProductionBatchModel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class AccountHeadController extends Controller
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

    public function index(Request $request){

        $data = AccountHeadModel::getRecords($request,$this->domain);
        return response()->json([
            'status' => 200,
            'message' => 'success',
            'total' => $data['count'],
            'data' => $data['entities']
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountHeadRequest $request)
    {
        $data = $request->validated();
        $data['config_id'] = $this->domain['acc_config'];
        $data['display_name'] = $data['name'];
        $entity = AccountHeadModel::create($data);
        AccountHeadDetailsModel::updateOrCreate([
            'account_head_id' => $entity->id,
            'config_id' => $this->domain['acc_config'],
        ]);
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Account head created successfully.',
            'data' => $entity,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountHeadRequest $request, $id)
    {
        try {
            // Validate and get the validated data
            $validatedData = $request->validated();

            // Find the entity or fail
            $entity = AccountHeadModel::findOrFail($id);

            // Update the entity
            $updated = $entity->update($validatedData);

            if (!$updated) {
                throw new \RuntimeException('Failed to update account head');
            }
            AccountHeadDetailsModel::updateOrCreate([
                'account_head_id' => $entity->id,
                'config_id' => $this->domain['acc_config'],
            ]);

            // Reload the model to get any database-default values
            $entity->refresh();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Account head updated successfully.',
                'data' => $entity,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'Account head not found.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to update account head.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function generateAccountHead(EntityManager $em)
    {
        $config_id = $this->domain['acc_config'];
        $entity = $em->getRepository(AccountHead::class)->generateAccountHead($config_id);
        AccountingModel::initiateConfig($this->domain);
        $em->getRepository(AccountHead::class)->resetAccountLedgerHead($config_id);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }

     /**
     * Store a newly created resource in storage.
     */
    public function resetAccountLedgerHead(Request $request)
    {
        $config_id = $this->domain['acc_config'];
        AccountingModel::initiateConfig($this->domain);
        AccountHeadModel::initialLedgerSetup($this->domain);
        $service = new JsonRequestResponse();
        $data = AccountHeadModel::getRecords($request,$this->domain);
        return $service->returnJosnResponse($data);

    }

     /**
     * Store a newly created resource in storage.
     */
    public function resetAccountHead(EntityManager $em)
    {
        $config_id = $this->domain['acc_config'];
        $entity = $em->getRepository(AccountHead::class)->generateAccountHead($config_id);
        AccountingModel::initiateConfig($this->domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function resetAccountVoucher(EntityManager $em)
    {

        AccountVoucherModel::resetVoucher($this->domain);
        AccountingModel::initiateConfig($this->domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse('success');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = AccountHeadModel::with('accountHeadDetails')->find($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $service = new JsonRequestResponse();
        $entity = AccountHeadModel::find($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        AccountHeadModel::find($id)->delete();
        $entity = ['message'=>'delete'];
        $data = $service->returnJosnResponse($entity);
        return $data;
    }


    public function LocalStorage(Request $request){
        $data = AccountHeadModel::getRecordsForLocalStorage($request,$this->domain);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $data
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    public function accountLedgerWiseJournal(Request $request,$id)
    {
        $params = $request->only('start_date','end_date');
        $getJournalItems = AccountJournalItemModel::getLedgerWiseJournalItems( ledgerId:$id, configId: $this->domain['acc_config'], params: $params );
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Ledger wise journal items retrieved.',
            'data' => $getJournalItems,
        ]);
    }

    public function ledgerPdfXlsxFileGenerate(Request $request, ReportExportService $generate, int $ledgerId, string $format)
    {
        $params = $request->only('start_date','end_date');

        // Fetch processed journal data
        $processed = AccountJournalItemModel::getLedgerWiseJournalItems(
            ledgerId: $ledgerId,
            configId: $this->domain['acc_config'],
            params: $params
        );

        // Get domain info
        $domainInfo = DomainModel::query()
            ->select('mobile','address','company_name')
            ->find($this->domain['domain_id']);

        // Get ledger name
        $ledger = AccountHeadModel::query()
            ->select('name')
            ->find($ledgerId);

        $data = $processed['ledgerItems'] ?? [];
        $openingData = $processed['opening_info'] ?? [];

        // Report headers
        $headers = [
            '#', 'Date','Issue Date', 'JV No', 'Ref. Number',
            'Voucher Type', 'Ledger Name', 'Particulars', 'Debit', 'Credit', 'Closing'
        ];

        $cumulativeRows = [];
        $previousClosing = null;

        // ✅ Insert Opening Balance row BEFORE any entries
        if (!empty($data)) {
            $firstOpening = floatval($openingData['opening_balance'] ?? 0);

            $cumulativeRows[] = [
                // Empty values for data cells since we’ll custom render it
                '#'            => '',
                'Date'         => '',
                'Issue Date'   => '',
                'JV No'        => '',
                'Ref. Number'  => '',
                'Voucher Type' => '',
                'Ledger Name'  => '',
                'Particulars'  => '',

                // Text for left-side cell
                'Debit'   => 'Previous Opening Balance '.$openingData['opening_date'] ?? null,

                // Display value on right in "Closing"
                'Credit'  => '',
                'Closing' => number_format(abs($firstOpening), 2),

                // Meta fields
                '__colspan' => 10,                           // left cell colspan
                '__style'   => [
                    'bold' => true,
                    'background' => [255, 249, 196],         // light yellow
                    'align' => 'L'
                ]
            ];



            $previousClosing = $firstOpening;
        }

        // ➕ Cumulative items
        foreach ($data as $index => $item) {
            $amount = floatval($item['amount'] ?? 0);
            $mode = $item['mode'] ?? null;

            $opening = $index === 0 && $previousClosing !== null
                ? $previousClosing
                : ($previousClosing ?? floatval($item['opening_amount'] ?? 0));

            $closing = $mode === 'Debit'
                ? $opening + $amount
                : ($mode === 'Credit' ? $opening - $amount : $opening);

            $previousClosing = $closing;

            $cumulativeRows[] = [
                '#'             => $index + 1,
                'Date'          => $item['created_date'] ?? '',
                'Issue Date'    => $item['issue_date'] ?? '',
                'JV No'         => $item['invoice_no'] ?? '',
                'Ref. Number'   => $item['ref_no'] ?? '',
                'Voucher Type'  => $item['voucher_name'] ?? '',
                'Ledger Name'   => $item['ledger_name'] ?? '',
                'Particulars'   => $item['description'] ?? '',
                'Debit'         => $mode === 'Debit' ? number_format(abs($amount), 2) : '',
                'Credit'        => $mode === 'Credit' ? number_format(abs($amount), 2) : '',
                'Closing'       => $closing < 0
                    ? '(' . number_format(abs($closing), 2) . ')'
                    : number_format($closing, 2),
            ];
        }

        // Final report rows
        $rows = $cumulativeRows;

        // Title/header customization
        $startDate = !empty($params['start_date']) ? \Carbon\Carbon::parse($params['start_date'])->format('d-m-Y') : null;
        $endDate = !empty($params['end_date']) ? \Carbon\Carbon::parse($params['end_date'])->format('d-m-Y') : null;

        $titles = [
            ['text' => $domainInfo->company_name ?? null, 'align' => 'C', 'font_size' => 12, 'bold' => true],
            ['text' => $domainInfo->address ?? null, 'align' => 'C'],
            ['text' => $domainInfo->mobile ?? null, 'align' => 'C'],
            ['text' => 'Ledger Report', 'align' => 'C', 'font_size' => 12, 'bold' => true],
            ['text' => $ledger->name ?? null, 'align' => 'L', 'fill' => [227, 242, 253]],
        ];

        if ($startDate && $endDate) {
            $titles[] = [
                'text' => 'Date : ' . $startDate . ' To ' . $endDate,
                'align' => 'R',
                'fill' => [227, 242, 253],
            ];
        }

        // ✅ Generate PDF or Excel
        $output = $generate->generateReport('ledger', $format, $rows, [
            'filename' => "ledger-report.{$format}",
            'headers' => $headers,
            'titles' => $titles,
        ]);

        return response()->json($output);
    }


    public function generateFileDownload(ReportExportService $download, string $filename)
    {
        return $download->download($filename);
    }

    public function accountHeadOutstanding(Request $request)
    {
        $params = $request->only('type','customer_id');
        $getOutstanding = AccountHeadModel::getAccountHeadOutstanding(configId: $this->domain['acc_config'], params: $params);

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Outstanding data retrieved.',
            'data' => $getOutstanding,
        ]);
    }


}
