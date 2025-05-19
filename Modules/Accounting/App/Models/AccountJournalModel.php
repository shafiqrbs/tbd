<?php

namespace Modules\Accounting\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Entities\AccountHead;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseModel;

class AccountJournalModel extends Model
{
    use HasFactory;

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


    public static function getRecords($request, $domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 0;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;
        $entity = self::where('acc_head.config_id', $domain['acc_config_id'])
            ->select([
                'acc_head.id',
                'acc_head.name'
            ]);

        if (isset($request['term']) && !empty($request['term'])) {
            $entity = $entity->whereAny(
                ['acc_head.name', 'acc_head.slug'], 'LIKE', '%' . $request['term'] . '%');
        }
        $total = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('acc_head.id', 'DESC')
            ->get();

        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }

    public static function insertOpeningStockAccountJournal($domain,$openingId){

        $config = ConfigModel::find($domain['acc_config']);
        $purchaseItem = PurchaseItemModel::find($openingId);

        $input['config_id'] = $domain['acc_config'];
        $input['voucher_id'] = $config->voucher_stock_opening_id;
        $input['amount'] = $purchaseItem->sub_total;
        $input['created_by_id'] = $purchaseItem->created_by_id;
        $input['approved_by_id'] = $purchaseItem->approved_by_id;
        $input['purchase_item_id'] = $purchaseItem->id;
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
        $accountDebit['mode'] = 'Debit';
        $accountDebit['is_parent'] = true;
        $debit = AccountJournalItemModel::create($accountDebit);


        $head1 = AccountHeadModel::getAccountHeadWithParent($config->capital_investment_id);
        $accountCredit['account_journal_id'] = $entity->id;
        $accountCredit['parent_id'] = $debit->id;
        $accountCredit['account_head_id'] = $head1->parent_id;
        $accountCredit['account_sub_head_id'] = $config->capital_investment_id;
        $accountCredit['amount'] = "-".$purchaseItem->sub_total;
        $accountCredit['credit'] = $purchaseItem->sub_total;
        $accountCredit['mode'] = 'Credit';
        AccountJournalItemModel::create($accountCredit);
        return true;

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
        $input['created_by_id'] = $entity->created_by_id;
        $input['approved_by_id'] = $entity->approved_by_id;
        $input['purchase_id'] = $entity->id;
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
            AccountJournalItemModel::create($accountDebit);
        }
    }

    public static function purchasePayableEntry($journal,$entity,$journalItem){

        $amount = $entity->total;
        $vendor = $entity->vendor_id;
        $head = AccountHeadModel::getAccountHeadWithParentPramValue('vendor_id',$vendor);
        if($head){
            $accountDebit['account_journal_id'] = $journal->id;
            $accountDebit['account_head_id'] = $head->parent_id;
            $accountDebit['account_sub_head_id'] = $head->id;
            $accountDebit['parent_id'] = $journalItem;
            $accountDebit['amount'] = "-{$amount}";
            $accountDebit['credit'] = $amount;
            $accountDebit['mode'] = 'credit';
            AccountJournalItemModel::create($accountDebit);
        }
    }

    public static function purchasePayableDebitEntry($journal,$entity){

        $amount = $entity->payment;
        $vendor = $entity->vendor_id;
        $head = AccountHeadModel::getAccountHeadWithParentPramValue('vendor_id',$vendor);
        if($head){
            $accountDebit['account_journal_id'] = $journal->id;
            $accountDebit['account_head_id'] = $head->parent_id;
            $accountDebit['account_sub_head_id'] = $head->id;
            $accountDebit['amount'] = $amount;
            $accountDebit['credit'] = $amount;
            $accountDebit['mode'] = 'debit';
            $accountDebit['is_parent'] = true;
            $journalItem = AccountJournalItemModel::create($accountDebit);
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
            AccountJournalItemModel::create($accountDebit);
        }

    }

}

