<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
    public function resetAccountLedgerHead(Request $request,EntityManager $em)
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

    public function accountLedgerWiseJournal($id)
    {
        $getJournalItems = AccountJournalItemModel::getLedgerWiseJournalItems( ledgerId:$id,configId: $this->domain['acc_config'] );
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Ledger wise journal items retrieved.',
            'data' => $getJournalItems,
        ]);
    }

    public function accountLedgerWiseJournalGenerateXlsx($id)
    {

        try {
            // Use the reusable method to get processed data
            $processedData = AccountJournalItemModel::getLedgerWiseJournalItems( ledgerId:$id,configId: $this->domain['acc_config'] );

            $spreadsheet = new Spreadsheet();

            // Remove the default worksheet first
            $defaultSheetIndex = $spreadsheet->getIndex(
                $spreadsheet->getSheetByName('Worksheet')
            );
            $spreadsheet->removeSheetByIndex($defaultSheetIndex);

            // Create custom-named worksheet
            $sheet = new Worksheet($spreadsheet, 'Ledger Report');
            $spreadsheet->addSheet($sheet, 0);
            $spreadsheet->setActiveSheetIndex(0);

            // Header labels
            $headers = [
                'S/N', 'Date', 'JV No', 'Voucher Type',
                'Ledger Name', 'Opening', 'Debit', 'Credit', 'Closing',
            ];

            // Define styles
            $headerStyle = [
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E3F2FD']
                ]
            ];

            $dataStyle = [
                'font' => ['size' => 10],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ];

            // Set headers
            foreach ($headers as $col => $label) {
                $columnLetter = chr(65 + $col); // A, B, C, ...
                $cell = $columnLetter . '1';
                $sheet->setCellValue($cell, $label);
                $sheet->getStyle($cell)->applyFromArray($headerStyle);
            }

            // Set data rows
            if (!isset($processedData['ledgerItems']) || !is_array($processedData['ledgerItems'])) {
                throw new \RuntimeException('No valid ledger data found.');
            }

            $rowIndex = 2;
            foreach ($processedData['ledgerItems'] as $index => $item) {
                $mode = strtolower($item['mode'] ?? '');
                $amount = is_numeric($item['amount'] ?? null) ? (float) $item['amount'] : 0;
                $opening = is_numeric($item['opening_amount'] ?? null) ? (float) $item['opening_amount'] : 0;
                $closing = is_numeric($item['closing_amount'] ?? null) ? (float) $item['closing_amount'] : 0;

                $debit = $mode === 'debit' ? $amount : 0;
                $credit = $mode === 'credit' ? $amount : 0;

                $rowData = [
                    $index + 1,
                    $item['created_date'] ?? '',
                    $item['invoice_no'] ?? '',
                    $item['voucher_name'] ?? '',
                    $item['ledger_name'] ?? '',
                    $opening,
                    $debit,
                    $credit,
                    $closing,
                ];

                foreach ($rowData as $col => $value) {
                    $columnLetter = chr(65 + $col);
                    $sheet->setCellValue($columnLetter . $rowIndex, $value);
                    $sheet->getStyle($columnLetter . $rowIndex)->applyFromArray($dataStyle);
                }

                $rowIndex++;
            }


            // Autosize columns
            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // File path
            $diskPath = storage_path('app/public/exports');
            $filename = 'ledger-report' . '.xlsx';
            $filepath = $diskPath . '/' . $filename;

            // Ensure directory
            if (!File::exists($diskPath)) {
                File::makeDirectory($diskPath, 0755, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($filepath);

            return response()->json([
                'filename' => $filename,
                'file_url' => asset('storage/exports/' . $filename),
                'status' => 200,
                'result' => true,
            ]);

        } catch (Exception $e) {
            // Handle errors gracefully
            Log::error("XLSX Generation Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response([
                'error' => $e->getMessage(),
                'result' => false,
                'status' => 500,
            ]);
        }
    }

    public function accountLedgerWiseJournalGeneratePdf($id)
    {

        try {
            // Use the reusable method to get processed data
            $processedData = AccountJournalItemModel::getLedgerWiseJournalItems( ledgerId:$id,configId: $this->domain['acc_config'] );

            $ledgerItems = $processedData['ledgerItems'] ?? [];

            // Create TCPDF PDF (Landscape, A3)
            $pdf = new \TCPDF('L', 'mm', 'A3', true, 'UTF-8', false);
            $pdf->SetTitle('Ledger Report');
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->SetPrintHeader(false);
            $pdf->SetPrintFooter(false);
            $pdf->AddPage();

            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 10, 'Ledger Report', 0, 1, 'C');

            $headers = ['S/N', 'Date', 'JV No', 'Voucher Type', 'Ledger Name', 'Opening', 'Debit', 'Credit', 'Closing'];
            $colWidths = [12, 30, 35, 30, 60, 30, 30, 30, 30];   // Adjusted widths
            $cellHeight = 10;
            $fontSize = 9;

// 🔹 Calculate total table width
            $totalWidth = array_sum($colWidths);

// 🔹 Get usable page width
            $pageWidth = $pdf->getPageWidth();
            $leftMargin = $pdf->getMargins()['left'];
            $rightMargin = $pdf->getMargins()['right'];

            $printableWidth = $pageWidth - $leftMargin - $rightMargin;

// 🔹 Compute X offset to center table
            $xOffset = $leftMargin + ($printableWidth - $totalWidth) / 2;

// 🔸 Optional: move to start of new Y position (before table)
            $pdf->Ln(5);

// 🔹 Set font and background for header
            $pdf->SetFont('helvetica', 'B', $fontSize);
            $pdf->SetFillColor(227, 242, 253); // Light blue bg
            $pdf->SetTextColor(0, 0, 0);

// 🔹 Set cursor X to center position
            $pdf->SetX($xOffset);

// 🔹 Render Headers (center aligned)
            foreach ($headers as $i => $label) {
                $pdf->Cell($colWidths[$i], $cellHeight, $label, 1, 0, 'C', true);
            }
            $pdf->Ln();

// 🔹 Data Rows Settings
            $pdf->SetFont('helvetica', '', 8);
            $rowIndex = 1;

            foreach ($ledgerItems as $item) {
                $mode = strtolower($item['mode'] ?? '');
                $amount = is_numeric($item['amount'] ?? null) ? (float)$item['amount'] : 0;
                $debit = $mode === 'debit' ? $amount : 0;
                $credit = $mode === 'credit' ? $amount : 0;

                $row = [
                    $rowIndex++,
                    $item['created_date'] ?? '-',
                    $item['invoice_no'] ?? '-',
                    $item['voucher_name'] ?? '-',
                    $item['ledger_name'] ?? '-',
                    number_format((float)($item['opening_amount'] ?? 0), 2),
                    number_format($debit, 2),
                    number_format($credit, 2),
                    number_format((float)($item['closing_amount'] ?? 0), 2),
                ];

                // 🔹 Reset X on each line
                $pdf->SetX($xOffset);

                foreach ($row as $i => $value) {
                    $align = ($i > 4) ? 'R' : 'L';  // numbers right-aligned, left others
                    $pdf->Cell($colWidths[$i], $cellHeight, $value, 1, 0, $align, false);
                }

                $pdf->Ln();
            }

            // Save PDF
            $filename = 'ledger-report.pdf';
            $diskPath = storage_path('app/public/exports'); // ✅ correct publicly served path
            if (!File::exists($diskPath)) {
                File::makeDirectory($diskPath, 0755, true);
            }

            $pdf->Output($diskPath . '/' . $filename, 'F'); // now saves to public folder

            return response()->json([
                'filename' => $filename,
                'file_url' => asset("storage/exports/" . $filename),
                'status' => 200,
                'result' => true,
            ]);



        } catch (Exception $e) {
            // Handle errors gracefully
            Log::error("XLSX Generation Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response([
                'error' => $e->getMessage(),
                'result' => false,
                'status' => 500,
            ]);
        }
    }


    public function accountLedgerWiseJournalDownload($type)
    {
        $fileName = '';
        if ($type == 'xlsx') {
            $fileName = 'ledger-report.xlsx';
        } elseif ($type == 'pdf') {
            $fileName = 'ledger-report.pdf';
        }
        $filePath = storage_path('exports/' . $fileName);
        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

}
