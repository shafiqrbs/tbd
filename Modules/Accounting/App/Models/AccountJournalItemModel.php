<?php

namespace Modules\Accounting\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AccountJournalItemModel extends Model
{
    use HasFactory;

    protected $table = 'acc_journal_item';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'account_journal_id',
        'account_head_id',
        'account_sub_head_id',
        'is_parent',
        'parent_id',
        'amount',
        'debit',
        'credit',
        'cheque_date',
        'cross_using',
        'amount',
        'forwarding_name',
        'pay_mode',
        'bank_id',
        'branch_name',
        'received_from',
        'cheque_no',
        'mode',
    ];

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

    public static function insertCustomerJournalItem($config, $entity)
    {

        self::create(
            [
                'name' => $entity['name'],
                'customer_id' => $entity['id'],
                'config_id' => $config
            ]
        );
        AccountJournalItemModel::insertCustomerJournalVoucher($entity);
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



    /**
     * Insert multiple journal items.
     *
     * @param AccountJournalModel $journal journal instance.
     * @param array $items Incoming items to insert.
     * @return bool
     */
    public static function insertJournalItems(AccountJournalModel $journal, array $items): bool
    {
        $timestamp = Carbon::now();
        $formattedItems = array_map(function ($item) use ($journal, $timestamp) {
            $bankInfo = $item['bankInfo'] ?? [];
            $parent = AccountHeadModel::find($item['id']);
            return [
                'account_journal_id'       => $journal->id,
                'account_ledger_id'        => $item['id'],
                'account_sub_head_id'      => $item['id'],
                'account_head_id'          => $parent->parent_id,
                'amount'                   => $item['mode']==='debit'?$item['debit']:$item['credit'],
                'debit'                    => $item['debit'] ?? 0,
                'credit'                   => $item['credit'] ?? 0,
                'mode'                     => $item['mode'] ?? 0,
                'created_at'               => $timestamp,
//                'cheque_date'              => $bankInfo['cheque_date'] ?? null,
//                'cross_using'              => $bankInfo['cross_using'] ?? null,
                'forwarding_name'          => $bankInfo['forwarding_name'] ?? null,
//                'pay_mode'                 => $bankInfo['pay_mode'] ?? null,
                'bank_id'                => $bankInfo['bank_id'] ?? null,
                'branch_name'              => $bankInfo['branch_name'] ?? null,
                'received_from'            => $bankInfo['received_from'] ?? null,
//                'cheque_no'                => $bankInfo['cheque_no'] ?? null,
            ];
        }, $items);

        try {
            return self::insert($formattedItems);
        } catch (\Throwable $th) {
            \Log::error('Failed to insert journal items: ' . $th->getMessage());
            return false;
        }
    }

}

