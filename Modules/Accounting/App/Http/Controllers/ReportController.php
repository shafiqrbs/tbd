<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Accounting\App\Entities\AccountHead;
use Modules\Accounting\App\Models\AccountHeadMasterModel;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\Accounting\App\Models\AccountJournalItemModel;
use Modules\Accounting\App\Models\AccountVoucherModel;
use Modules\Accounting\App\Models\SettingModel;
use Modules\Accounting\App\Models\SettingTypeModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


class ReportController extends Controller
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
    public function dashboard()
    {

        $configId = $this->domain['acc_config'];
        $config = AccountingModel::find($configId);

        $cashSummary = AccountHeadModel::getAccountHeadLedgerSummary($config['account_cash_id']);
        $bankSummary = AccountHeadModel::getAccountHeadLedgerSummary($config['account_bank_id']);
        $mobileSummary = AccountHeadModel::getAccountHeadLedgerSummary($config['account_mobile_id']);
        $customerSummary = AccountHeadModel::getAccountHeadLedgerSummary($config['account_customer_id']);
        $vendorSummary = AccountHeadModel::getAccountHeadLedgerSummary($config['account_vendor_id']);
        $summary =[
            'cashSummary' => $cashSummary,
            'bankSummary' => $bankSummary,
            'mobileSummary' => $mobileSummary,
            'customerSummary' => $customerSummary,
            'vendorSummary' => $vendorSummary
        ];
        
        $bankAccounts = AccountHeadModel::getAccountHeadLedger($config['account_bank_id']);
        $mobileAccounts = AccountHeadModel::getAccountHeadLedger($config['account_mobile_id']);
        $cashAccounts = AccountHeadModel::getAccountHeadLedger($config['account_cash_id']);
        $vendorAccounts = AccountHeadModel::getAccountHeadLedger($config['account_vendor_id'],20);
        $customerAccounts = AccountHeadModel::getAccountHeadLedger($config['account_customer_id'],20);
        $products = AccountHeadModel::getAccountHeadLedger($config['account_product_group_id'],20);
        $data =[
            'bankAccounts' => $bankAccounts,
            'cashAccounts' => $cashAccounts,
            'mobileAccounts' => $mobileAccounts,
            'vendorAccounts' => $vendorAccounts,
            'customerAccounts' => $customerAccounts,
            'products' => $products,
        ];
        $result =[
            'summaries' => $summary,
            'ledgers'=>$data,
        ];
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($data);

    }

    public function incomeExpense(Request $request)
    {
        $params = $request->only('start_date','end_date');

        $receivedData = AccountJournalItemModel::getIncomeExpense($params, $this->domain);
        return response()->json([
            'status' => true,
            'message' => 'Income Expense',
            'data' => [
                'ledgers' => $receivedData['accounts'],
                'receives' => $receivedData['outletSales'],
                'expenses' => $receivedData['outletExpense'],
                'summary' => $receivedData['summaryData'],
            ]
        ], ResponseAlias::HTTP_OK);
    }






}
