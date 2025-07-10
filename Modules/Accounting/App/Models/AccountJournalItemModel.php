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
        'opening_amount',
        'closing_amount',
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

        try {
            // First, find and insert the main-ledger item
            $mainLedgerItem = collect($items)->firstWhere('type', 'main-ledger');

            if (!$mainLedgerItem) {
                \Log::error('No main-ledger item found in the data');
                return false;
            }

            // Format and insert the main-ledger item
            $mainItemFormatted = self::formatJournalItem($journal, $mainLedgerItem, $timestamp);
            $mainItemId = self::insertGetId($mainItemFormatted);

            if (!$mainItemId) {
                \Log::error('Failed to insert main-ledger item');
                return false;
            }

            // Get remaining items (non main-ledger)
            $remainingItems = collect($items)->where('type', '!=', 'main-ledger')->toArray();

            if (empty($remainingItems)) {
                return true; // Only main-ledger item was present
            }

            // Format remaining items with parent_id set to main item ID
            $formattedItems = array_map(function ($item) use ($journal, $timestamp, $mainItemId) {
                $formatted = self::formatJournalItem($journal, $item, $timestamp);
                $formatted['parent_id'] = $mainItemId;
                return $formatted;
            }, $remainingItems);

            // Insert remaining items
            return self::insert($formattedItems);

        } catch (\Throwable $th) {
            \Log::error('Failed to insert journal items: ' . $th->getMessage());
            return false;
        }
    }

    private static function formatJournalItem(AccountJournalModel $journal, array $item, $timestamp): array
    {
        $bankInfo = $item['bankInfo'] ?? [];
        $parent = AccountHeadModel::find($item['id']);

        return [
            'account_journal_id' => $journal->id,
            'account_ledger_id' => $item['id'],
            'account_sub_head_id' => $item['id'],
            'account_head_id' => $parent->parent_id ?? null,
            'amount' => $item['mode'] === 'debit' ? $item['debit'] : $item['credit'],
            'debit' => $item['debit'] ?? 0,
            'credit' => $item['credit'] ?? 0,
            'mode' => $item['mode'] ?? 0,
            'created_at' => $timestamp,
            'forwarding_name' => $bankInfo['forwarding_name'] ?? null,
            'bank_id' => $bankInfo['bank_id'] ?? null,
            'branch_name' => $bankInfo['branch_name'] ?? null,
            'received_from' => $bankInfo['received_from'] ?? null,
        ];
    }

    public static function getLedgerWiseOpeningBalance( $ledgerId,$configId,$journalItemId )
    {
        $openingBalance = self::join('acc_journal', 'acc_journal.id', '=', 'acc_journal_item.account_journal_id')
                            ->where('acc_journal.config_id', $configId)
                            ->where('acc_journal_item.account_ledger_id', $ledgerId)
                            ->where('acc_journal_item.id', '<>', $journalItemId)
                            ->orderByDesc('acc_journal_item.created_at')
                            ->value('acc_journal_item.closing_amount') ?? 0;
        return $openingBalance;
    }

}

