<?php

namespace Modules\Accounting\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Models\PurchaseItemModel;

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
            $date = new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date = new \DateTime("now");
            $model->updated_at = $date;
        });
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


        $accountDebit['account_journal_id'] = $entity->id;
        $accountDebit['account_head_id'] = $config->account_stock_opening_id;
        $accountDebit['account_sub_head_id'] = $config->account_stock_opening_id;
        $accountDebit['amount'] = $purchaseItem->sub_total;
        $accountDebit['debit'] = $purchaseItem->sub_total;
        $accountDebit['mode'] = 'Debit';
        $debit = AccountJournalItemModel::create($accountDebit);


        $accountCredit['account_journal_id'] = $entity->id;
        $accountCredit['parent'] = $debit->id;
        $accountCredit['account_head_id'] = $config->capital_investment_id;
        $accountCredit['account_sub_head_id'] = $config->capital_investment_id;
        $accountCredit['amount'] = "-".$purchaseItem->sub_total;
        $accountCredit['credit'] = $purchaseItem->sub_total;
        $accountCredit['mode'] = 'Credit';
        AccountJournalItemModel::create($accountCredit);
        return true;

    }
}

