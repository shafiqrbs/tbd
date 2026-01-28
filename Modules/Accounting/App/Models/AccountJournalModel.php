<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Inventory\App\Models\CategoryModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;

class AccountJournalModel extends Model
{

    protected $table = 'acc_journal';
    public $timestamps = true;
    protected $guarded = ['id'];


    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->invoice_no = self::journalEventListener($model)['generateId'];
            $model->code = self::journalEventListener($model)['code'];
            $date = new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date = new \DateTime("now");
            $model->updated_at = $date;
        });
    }
    public static function journalEventListener($model)
    {

        $patternCodeService = app(GeneratePatternCodeService::class);
        $params = [
            'config' => $model->config_id,
            'table' => 'acc_journal',
            'prefix' => 'JA-',
        ];
        return $patternCodeService->invoiceNo($params);
    }

    public static function insertCustomerJournalVoucher($config, $entity)
    {

        $entity = self::create(
            [
                'name' => $entity['name'],
                'customer_id' => $entity['id'],
                'config_id' => $config
            ]
        );

    }


    public static function getLedger($domain)
    {
        $query = self::select(['name', 'slug', 'id'])
            ->where([['status', 1], ['config_id', $domain['config_id']]]);
        $query->whereNotNull('parent');
        return $query->get();
    }

    public static function getAccountSubHead($domain)
    {
        $query = self::select(['name', 'slug', 'id'])
            ->where([['status', 1], ['config_id', $domain['config_id']]]);
        return $query->get();
    }

    public static function getAccountHead($domain)
    {
        $query = self::select(['name', 'slug', 'id'])
            ->where([['status', 1], ['config_id', $domain['config_id']]]);
        return $query->get();
    }

    public function journalItems()
    {
        return $this->hasMany(AccountJournalItemModel::class, 'account_journal_id');
    }


    public static function getRecords($request, $domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 0;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;
        $isBranch = isset($request['is_branch']) && $request['is_branch'] == true ? true : false;

        $firstPendingId = self::where('acc_journal.config_id', $domain['acc_config'])
            ->where('acc_journal.process', 'Created')
            ->whereNull('acc_journal.approved_by_id')
            ->when($isBranch, function ($q) {
                $q->whereNotNull('acc_journal.branch_id');
            }, function ($q) {
                $q->whereNull('acc_journal.branch_id');
            })
            ->min('acc_journal.id');
        $firstPendingId = $firstPendingId ?? 0;


        $entity = self::where('acc_journal.config_id', $domain['acc_config'])
            ->join('acc_voucher','acc_voucher.id','=','acc_journal.voucher_id')
            ->join('users','users.id','=','acc_journal.created_by_id')
            ->leftjoin('dom_domain','dom_domain.id','=','acc_journal.branch_id')
            ->leftjoin('users as approve','approve.id','=','acc_journal.approved_by_id')
            ->select([
                'acc_journal.id',
                'dom_domain.name as branch_name',
                'acc_journal.voucher_id',
                'acc_journal.approved_by_id',
                'acc_journal.created_by_id',
                'acc_journal.process',
                'acc_journal.amount as amount',
                'acc_journal.debit',
                'acc_journal.credit',
                'acc_journal.description',
                DB::raw('DATE_FORMAT(acc_journal.created_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(acc_journal.issue_date, "%d-%m-%Y") as issue_date'),
                'acc_journal.invoice_no',
                'acc_journal.ref_no',
                'acc_voucher.name as voucher_name',
                'acc_voucher.mode as voucher_mode',
                'users.name as created_by_name',
                'approve.name as approve_by_name',
                DB::raw("CASE
                    WHEN acc_journal.id = {$firstPendingId} THEN 1
                    ELSE 0
                END as can_approve")
            ])
            ->with(['journalItems' => function ($query) {
                $query->select([
                    'acc_journal_item.id',
                    'acc_journal_item.account_journal_id',
                    'acc_journal_item.account_sub_head_id',
                    'acc_journal_item.amount',
                    'acc_journal_item.debit',
                    'acc_journal_item.credit',
                    'acc_head.name as ledger_name',
                    'parent_head.name as head_name',
//                    DB::raw("CONCAT(cor_warehouses.name, ' (', cor_warehouses.location, ')') as warehouse_name"),
                ])->join('acc_head','acc_head.id','=','acc_journal_item.account_sub_head_id')
                    ->join('acc_head as parent_head','parent_head.id','=','acc_journal_item.account_head_id');
            }]);

        if (isset($request['term']) && !empty($request['term'])) {
            $entity = $entity->whereAny(['acc_journal.name', 'acc_journal.slug'], 'LIKE', '%' . $request['term'] . '%');
        }

        if ($isBranch){
            $entity = $entity->where('acc_journal.is_branch',1);
        }else{
            $entity = $entity->where('acc_journal.is_branch',0);
        }

        $total = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('acc_journal.id', 'DESC')
            ->get();

        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }
    public static function show($id)
    {
        $entity = self::where('acc_journal.id', $id)
            ->join('acc_voucher','acc_voucher.id','=','acc_journal.voucher_id')
            ->join('users','users.id','=','acc_journal.created_by_id')
            ->leftjoin('dom_domain','dom_domain.id','=','acc_journal.branch_id')
            ->leftjoin('users as approve','approve.id','=','acc_journal.approved_by_id')
            ->select([
                'acc_journal.id',
                'dom_domain.name as branch_name',
                'acc_journal.voucher_id',
                'acc_journal.created_by_id',
                'acc_journal.process',
                'acc_journal.amount as amount',
                'acc_journal.debit',
                'acc_journal.credit',
                'acc_journal.description',
                DB::raw('DATE_FORMAT(acc_journal.created_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(acc_journal.issue_date, "%d-%m-%Y") as issue_date'),
                'acc_journal.invoice_no',
                'acc_journal.ref_no',
                'acc_voucher.name as voucher_name',
                'acc_voucher.mode as voucher_mode',
                'users.name as created_by_name',
                'approve.name as approve_by_name',
            ])
            ->with(['journalItems' => function ($query) {
                $query->select([
                    'acc_journal_item.id',
                    'acc_journal_item.account_journal_id',
                    'acc_journal_item.account_sub_head_id',
                    'acc_journal_item.amount',
                    'acc_journal_item.debit',
                    'acc_journal_item.credit',
                    'acc_head.name as ledger_name',
                    'parent_head.name as head_name',
                ])->join('acc_head','acc_head.id','=','acc_journal_item.account_sub_head_id')
                    ->join('acc_head as parent_head','parent_head.id','=','acc_journal_item.account_head_id');
            }])->first();
        return $entity;
    }

    public static function journalOpeningClosing($journal,$journalItem)
    {
        $opening = AccountJournalItemModel::getLedgerWiseOpeningBalance(
                    ledgerId: $journalItem->account_sub_head_id,
                    configId: $journal->config_id,
                );

                /*$closing = $journalItem->mode === 'debit'
                    ? $opening + $journalItem->amount
                    : ($journalItem->mode === 'credit' ? $opening - $journalItem->amount : 0);*/

                $closing = ($opening + ($journalItem->amount));
                $journalItem->update([
                    'opening_amount' => $opening,
                    'closing_amount' => $closing,
                ]);

                $findAccoundLegderHead = AccountHeadModel::find($journalItem->account_sub_head_id);
                $findAccoundLegderHead->update([
                    'amount' => $closing,
                ]);
    }
    public static function insertOpeningStockAccountJournal($domain,$openingId){

        $config = ConfigModel::find($domain['acc_config']);
        $purchaseItem = PurchaseItemModel::find($openingId);

        $input['config_id'] = $domain['acc_config'];
        $input['voucher_id'] = $config->voucher_stock_opening_id;
        $input['amount'] = $purchaseItem->sub_total;
        $input['debit'] = $purchaseItem->sub_total;
        $input['created_by_id'] = $purchaseItem->created_by_id;
        $input['approved_by_id'] = $purchaseItem->approved_by_id;
        $input['purchase_item_id'] = $purchaseItem->id;
        $input['issue_date'] = $purchaseItem->created;
        $input['module'] = 'opening-stock';
        $input['process'] = 'Approved';
        $input['waiting_process'] = 'Approved';
        $entity = self::create($input);



        $head = AccountHeadModel::getAccountHeadWithParent($config->account_stock_opening_id);
        $accountDebit['account_journal_id'] = $entity->id;
        $accountDebit['account_head_id'] = $head->parent_id;
        $accountDebit['account_sub_head_id'] = $config->account_stock_opening_id;
        $accountDebit['amount'] = $purchaseItem->sub_total;
        $accountDebit['debit'] = $purchaseItem->sub_total;
        $accountDebit['mode'] = 'debit';
        $accountDebit['is_parent'] = true;
        $debit = AccountJournalItemModel::create($accountDebit);

        self::journalOpeningClosing($entity,$debit);

        $head1 = AccountHeadModel::getAccountHeadWithParent($config->capital_investment_id);
        $accountCredit['account_journal_id'] = $entity->id;
        $accountCredit['parent_id'] = $debit->id;
        $accountCredit['account_head_id'] = $head1->parent_id;
        $accountCredit['account_sub_head_id'] = $config->capital_investment_id;
        $accountCredit['amount'] = "-".$purchaseItem->sub_total;
        $accountCredit['credit'] = $purchaseItem->sub_total;
        $accountCredit['mode'] = 'credit';
        $credit = AccountJournalItemModel::create($accountCredit);
        self::journalOpeningClosing($entity,$credit);

     //   self::openingGoodsEntry($journal,$config,$amount);

        return true;

    }
    public static function openingGoodsEntry($journal,$config,$amount){

        $head = AccountHeadModel::getAccountHeadWithParent($config->account_purchase_id);
        if($head){
            $accountDebit['account_journal_id'] = $journal->id;
            $accountDebit['account_head_id'] = $head->parent_id;
            $accountDebit['account_sub_head_id'] = $head->id;
            $accountDebit['amount'] = "-{$amount}";
            $accountDebit['credit'] = $amount;
            $accountDebit['mode'] = 'credit';
            $accountDebit['is_parent'] = true;
            $journalItem = AccountJournalItemModel::create($accountDebit);
            return $journalItem;
        }
    }

    public static function openingGoodsInStock($journal,$journalItem,$purchase){


        $records = PurchaseItemModel::getProductGroupPrice($purchase);

        foreach ($records as $record){
            $head = AccountHeadModel::getAccountHeadWithParentPramValue('product_group_id',$record['product_group_id']);
            if($head and $record['amount'] > 0){
                $amount = $record['amount'];
                $accountDebit['account_journal_id'] = $journal->id;
                $accountDebit['account_head_id'] = $head->parent_id;
                $accountDebit['account_sub_head_id'] = $head->id;
                $accountDebit['parent_id'] = $journalItem;
                $accountDebit['amount'] = "{$amount}";
                $accountDebit['debit'] = $amount;
                $accountDebit['mode'] = 'debit';
                AccountJournalItemModel::create($accountDebit);
            }
        }

    }

    public static function insertPurchaseAccountJournal($domain,$purchase){


        $config = ConfigModel::find($domain['acc_config']);
        $entity = PurchaseModel::find($purchase);

        $subTotal = ($entity->sub_total) ? floatval($entity->sub_total) : 0;
        $payment = ($entity->payment) ? floatval($entity->payment) : 0;
        $discount = ($entity->discount) ? floatval($entity->discount) : 0;


        $input['config_id'] = $config->id;
        $input['voucher_id'] = $config->voucher_purchase_id;
        $input['amount'] = $subTotal;
        $input['debit'] = $subTotal;
        $input['created_by_id'] = $entity->created_by_id;
        $input['approved_by_id'] = $entity->approved_by_id;
        $input['purchase_id'] = $entity->id;
        $input['issue_date'] = $entity->created;
        $input['module'] = 'purchase';
        $input['process'] = 'Approved';
        $input['waiting_process'] = 'Approved';
        $journal = self::create($input);

        $journalItem = self::purchaseEntry($config,$journal,$subTotal);
        if($journalItem and $discount > 0){
            self::purchaseDiscountEntry($config,$journal,$discount,$journalItem);
        }
        if($journalItem){
            self::purchasePayableEntry($journal, $entity, $journalItem);
        }

        $goodsItem = self::purchaseGoodsEntry($journal,$config,$subTotal);
        if($goodsItem){
            self::goodsInStock($journal,$journalItem,$purchase);
        }

       if($payment > 0){
           $journalItem = self::purchasePayableDebitEntry($journal, $entity);
           if($journalItem){
               self::purchasePayablePaymentEntry($config, $journal, $entity , $journalItem);
           }
       }
    }

    public static function purchaseEntry($config,$journal,$amount){

        $head = AccountHeadModel::getAccountHeadWithParent($config->account_purchase_id);
        if($head){
            $accountDebit['account_journal_id'] = $journal->id;
            $accountDebit['account_head_id'] = $head->parent_id;
            $accountDebit['account_sub_head_id'] = $head->id;
            $accountDebit['amount'] = $amount;
            $accountDebit['debit'] = $amount;
            $accountDebit['mode'] = 'debit';
            $accountDebit['is_parent'] = true;
            $journalItem = AccountJournalItemModel::create($accountDebit);
            self::journalOpeningClosing($journal,$journalItem);
            return $journalItem->id;
        }

    }

    public static function purchaseDiscountEntry($config,$journal,$amount,$journalItem){

        $head = AccountHeadModel::getAccountHeadWithParent($config->account_purchase_discount_id);
        if($head){
            $accountDebit['account_journal_id'] = $journal->id;
            $accountDebit['account_head_id'] = $head->parent_id;
            $accountDebit['account_sub_head_id'] = $config->account_purchase_discount_id;
            $accountDebit['parent_id'] = $journalItem;
            $accountDebit['amount'] = "-{$amount}";
            $accountDebit['credit'] = $amount;
            $accountDebit['mode'] = 'credit';
            $entity = AccountJournalItemModel::create($accountDebit);
            self::journalOpeningClosing($journal,$entity);
        }
    }

    public static function purchasePayableEntry($journal,$entity,$journalItem){

        $amount = $entity->total;
        $value = $entity->vendor_id;
        $head = AccountHeadModel::getAccountHeadWithParentPramValue('vendor_id',$value);
        if($head){
            $accountDebit['account_journal_id'] = $journal->id;
            $accountDebit['account_head_id'] = $head->parent_id;
            $accountDebit['account_sub_head_id'] = $head->id;
            $accountDebit['parent_id'] = $journalItem;
            $accountDebit['amount'] = "-{$amount}";
            $accountDebit['credit'] = $amount;
            $accountDebit['mode'] = 'credit';
            $journalItem = AccountJournalItemModel::create($accountDebit);
            self::journalOpeningClosing($journal,$journalItem);
        }
    }

    public static function purchaseGoodsEntry($journal,$config,$amount){

        $head = AccountHeadModel::getAccountHeadWithParent($config->account_purchase_id);
        if($head){
            $accountDebit['account_journal_id'] = $journal->id;
            $accountDebit['account_head_id'] = $head->parent_id;
            $accountDebit['account_sub_head_id'] = $head->id;
            $accountDebit['amount'] = "-{$amount}";
            $accountDebit['credit'] = $amount;
            $accountDebit['mode'] = 'credit';
            $accountDebit['is_parent'] = true;
            $journalItem = AccountJournalItemModel::create($accountDebit);
            self::journalOpeningClosing($journal,$journalItem);
            return $journalItem;
        }
    }

    public static function goodsInStock($journal,$journalItem,$purchase){

        $records = PurchaseItemModel::getProductGroupPrice($purchase);
        foreach ($records as $record){
            $head = AccountHeadModel::getAccountHeadWithParentPramValue('product_group_id',$record['product_group_id']);
            if(empty($head)){
                $group = CategoryModel::find($record['product_group_id']);
                $head = AccountHeadModel::insertCategoryGroupLedger($journal->config_id,$group);
            }
            if($head and $record['amount'] > 0){
                $amount = $record['amount'];
                $accountDebit['account_journal_id'] = $journal->id;
                $accountDebit['account_head_id'] = $head->parent_id;
                $accountDebit['account_sub_head_id'] = $head->id;
                $accountDebit['parent_id'] = $journalItem;
                $accountDebit['amount'] = "{$amount}";
                $accountDebit['debit'] = $amount;
                $accountDebit['mode'] = 'debit';
                $journalItem = AccountJournalItemModel::create($accountDebit);
                self::journalOpeningClosing($journal,$journalItem);
            }
        }

    }

    public static function purchasePayableDebitEntry($journal,$entity){

        $amount = $entity->payment;
        $value = $entity->vendor_id;
        $head = AccountHeadModel::getAccountHeadWithParentPramValue('vendor_id',$value);
        if($head){
            $accountDebit['account_journal_id'] = $journal->id;
            $accountDebit['account_head_id'] = $head->parent_id;
            $accountDebit['account_sub_head_id'] = $head->id;
            $accountDebit['amount'] = $amount;
            $accountDebit['debit'] = $amount;
            $accountDebit['mode'] = 'debit';
            $accountDebit['is_parent'] = true;
            $journalItem = AccountJournalItemModel::create($accountDebit);
            self::journalOpeningClosing($journal,$journalItem);
            return $journalItem->id;
        }
        return false;
    }

    public static function purchasePayablePaymentEntry($config,$journal,$entity,$journalItem){

        $amount = $entity->payment;
        $value = $entity->transaction_mode_id;
        $head = AccountHeadModel::getAccountHeadWithParentPramValue('account_id',$value);
        if($head){
            $accountDebit['account_journal_id'] = $journal->id;
            $accountDebit['account_head_id'] = $head->parent_id;
            $accountDebit['account_sub_head_id'] = $head->id;
            $accountDebit['parent_id'] = $journalItem;
            $accountDebit['amount'] = "-{$amount}";
            $accountDebit['credit'] = $amount;
            $accountDebit['mode'] = 'credit';
            $journalItem  = AccountJournalItemModel::create($accountDebit);
            self::journalOpeningClosing($journal,$journalItem);
        }

    }

    public static function insertSalesAccountJournal($domain,$sales){


        $config = ConfigModel::find($domain['acc_config']);
        $entity = SalesModel::find($sales);

        $subTotal = ($entity->sub_total) ? floatval($entity->sub_total) : 0;
        $payment = ($entity->payment) ? floatval($entity->payment) : 0;
        $discount = ($entity->discount) ? floatval($entity->discount) : 0;


        $input['config_id'] = $config->id;
        $input['voucher_id'] = $config->voucher_sales_id;
        $input['amount'] = $subTotal;
        $input['debit'] = $subTotal;
        $input['created_by_id'] = $entity->created_by_id;
        $input['approved_by_id'] = $entity->approved_by_id;
        $input['sales_id'] = $entity->id;
        $input['issue_date'] = $entity->created;
        $input['module'] = 'sales';
        $input['process'] = 'Approved';
        $input['waiting_process'] = 'Approved';
        $journal = self::create($input);

        $journalItem = self::salesEntry($config,$journal,$subTotal);
        if($journalItem and $discount > 0){
            self::salesDiscountEntry($config,$journal,$discount,$journalItem);
        }

        if($journalItem){
            self::salesReceivableEntry($journal, $entity, $journalItem);
        }

        if($payment > 0){
            $journalItem = self::salesReceivableCreditEntry($journal, $entity);
            if($journalItem){
                self::salesReceivableDebitEntry($journal, $entity , $journalItem);
            }
        }

        $journalItem = self::salesGoodsEntry($config,$journal,$subTotal);
        if($journalItem){
            self::goodsOutStock($journal,$journalItem,$entity);
        }

    }

    public static function salesEntry($config,$journal,$amount){

        $head = AccountHeadModel::getAccountHeadWithParent($config->account_sales_id);
        if($head){
            $accountDebit['account_journal_id'] = $journal->id;
            $accountDebit['account_head_id'] = $head->parent_id;
            $accountDebit['account_sub_head_id'] = $head->id;
            $accountDebit['amount'] = "-{$amount}";
            $accountDebit['credit'] = $amount;
            $accountDebit['mode'] = 'credit';
            $accountDebit['is_parent'] = true;
            $journalItem = AccountJournalItemModel::create($accountDebit);
            self::journalOpeningClosing($journal,$journalItem);
            return $journalItem->id;
        }

    }

    public static function salesReceivableEntry($journal,$entity,$journalItem){

        $amount = $entity->total;
        $value = $entity->customer_id;
        $head = AccountHeadModel::getAccountHeadWithParentPramValue('customer_id',$value);
        if($head){
            $accountDebit['account_journal_id'] = $journal->id;
            $accountDebit['account_head_id'] = $head->parent_id;
            $accountDebit['account_sub_head_id'] = $head->id;
            $accountDebit['parent_id'] = $journalItem;
            $accountDebit['amount'] = "{$amount}";
            $accountDebit['debit'] = $amount;
            $accountDebit['mode'] = 'debit';
            $journalItem = AccountJournalItemModel::create($accountDebit);
            self::journalOpeningClosing($journal,$journalItem);
        }
    }

    public static function salesDiscountEntry($config,$journal,$amount,$journalItem){

        $head = AccountHeadModel::getAccountHeadWithParent($config->account_sales_discount_id);
        if($head){
            $accountDebit['account_journal_id'] = $journal->id;
            $accountDebit['account_head_id'] = $head->parent_id;
            $accountDebit['account_sub_head_id'] = $config->account_sales_discount_id;
            $accountDebit['parent_id'] = $journalItem;
            $accountDebit['amount'] = "{$amount}";
            $accountDebit['debit'] = $amount;
            $accountDebit['mode'] = 'debit';
            $journalItem = AccountJournalItemModel::create($accountDebit);
            self::journalOpeningClosing($journal,$journalItem);
        }
    }

    public static function salesReceivableCreditEntry($journal,$entity){

        $amount = $entity->payment;
        $value = $entity->customer_id;
        $head = AccountHeadModel::getAccountHeadWithParentPramValue('customer_id',$value);
        if($head){
            $accountDebit['account_journal_id'] = $journal->id;
            $accountDebit['account_head_id'] = $head->parent_id;
            $accountDebit['account_sub_head_id'] = $head->id;
            $accountDebit['amount'] = "-{$amount}";
            $accountDebit['credit'] = $amount;
            $accountDebit['mode'] = 'credit';
            $accountDebit['is_parent'] = true;
            $journalItem = AccountJournalItemModel::create($accountDebit);
            self::journalOpeningClosing($journal,$journalItem);
            return $journalItem->id;
        }
        return false;
    }

    public static function salesReceivableDebitEntry($journal,$entity,$journalItem){

        $amount = $entity->payment;
        $method = $entity->transaction_mode_id;
        $head = AccountHeadModel::getAccountHeadWithParentPramValue('account_id',$method);
        if($head){
            $accountDebit['account_journal_id'] = $journal->id;
            $accountDebit['account_head_id'] = $head->parent_id;
            $accountDebit['account_sub_head_id'] = $head->id;
            $accountDebit['parent_id'] = $journalItem;
            $accountDebit['amount'] = "{$amount}";
            $accountDebit['debit'] = $amount;
            $accountDebit['mode'] = 'debit';
            $journalItem = AccountJournalItemModel::create($accountDebit);
            self::journalOpeningClosing($journal,$journalItem);
        }
    }

    public static function salesGoodsEntry($config,$journal,$amount){

        $head = AccountHeadModel::getAccountHeadWithParent($config->account_sales_id);
        if($head){
            $accountDebit['account_journal_id'] = $journal->id;
            $accountDebit['account_head_id'] = $head->parent_id;
            $accountDebit['account_sub_head_id'] = $head->id;
            $accountDebit['amount'] = "{$amount}";
            $accountDebit['debit'] = $amount;
            $accountDebit['mode'] = 'debit';
            $accountDebit['is_parent'] = true;
            $journalItem = AccountJournalItemModel::create($accountDebit);
            self::journalOpeningClosing($journal,$journalItem);
            return $journalItem;
        }
    }

    public static function goodsOutStock($journal,$journalItem,$entity){

        $records = SalesItemModel::getProductGroupPrice($entity->id);
        foreach ($records as $record){
            $head = AccountHeadModel::getAccountHeadWithParentPramValue('product_group_id',$record['product_group_id']);
            if(empty($head)){
                $group = CategoryModel::find($record['product_group_id']);
                $head = AccountHeadModel::insertCategoryGroupLedger($journal->config_id,$group);
            }
            if($head and $record['amount'] > 0){
                $amount = $record['amount'];
                $accountDebit['account_journal_id'] = $journal->id;
                $accountDebit['account_head_id'] = $head->parent_id;
                $accountDebit['account_sub_head_id'] = $head->id;
                $accountDebit['parent_id'] = $journalItem->id;
                $accountDebit['amount'] = "-{$amount}";
                $accountDebit['credit'] = $amount;
                $accountDebit['mode'] = 'credit';
                $journalItem = AccountJournalItemModel::create($accountDebit);
                self::journalOpeningClosing($journal,$journalItem);
            }
        }

    }

}

