<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AccountJournalItemModel extends Model
{

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
            $mainItemFormatted['is_parent'] = 1;
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

    /*public static function getLedgerWiseJournalItems($ledgerId,$configId)
    {
        $journalParentItems = self::join('acc_journal','acc_journal.id','=','acc_journal_item.account_journal_id')
            ->join('acc_head','acc_head.id','=','acc_journal_item.account_sub_head_id')
            ->select('acc_journal_item.id','acc_journal_item.account_ledger_id','acc_journal_item.account_journal_id','acc_journal_item.account_head_id','acc_journal_item.account_sub_head_id','acc_journal_item.amount','acc_journal_item.debit','acc_journal_item.credit','acc_journal_item.mode','acc_journal_item.created_at',                DB::raw('DATE_FORMAT(acc_journal_item.created_at, "%d-%m-%Y") as created_date')
                ,'acc_journal_item.opening_amount','acc_journal_item.closing_amount','acc_head.name as ledger_name')
            ->where('acc_journal.config_id', $configId)
            ->where('acc_journal_item.account_sub_head_id', $ledgerId)
            ->where('acc_journal_item.is_parent', 1)
            ->get()->toArray();


        $ledgerDetails = [];
        foreach ($journalParentItems as $journalParentItem) {
            $getChildItems = self::where('acc_journal_item.parent_id',$journalParentItem['id'])
                ->join('acc_head','acc_head.id','=','acc_journal_item.account_sub_head_id')
                ->select('acc_journal_item.id','acc_journal_item.account_ledger_id','acc_journal_item.account_journal_id','acc_journal_item.account_head_id','acc_journal_item.account_sub_head_id','acc_journal_item.amount','acc_journal_item.debit','acc_journal_item.credit','acc_journal_item.mode','acc_journal_item.created_at',DB::raw('DATE_FORMAT(acc_journal_item.created_at, "%d-%m-%Y") as created_date')
                ,'acc_journal_item.opening_amount','acc_journal_item.closing_amount','acc_head.name as ledger_name')->get()->toArray();

            $journalParentItem['childItems'] = $getChildItems;
            $ledgerDetails[] = $journalParentItem;
        }


        $getChildItems2 = self::join('acc_journal','acc_journal.id','=','acc_journal_item.account_journal_id')
            ->join('acc_head','acc_head.id','=','acc_journal_item.account_sub_head_id')
            ->select('acc_journal_item.id','acc_journal_item.account_ledger_id','acc_journal_item.account_journal_id','acc_journal_item.account_head_id','acc_journal_item.account_sub_head_id','acc_journal_item.amount','acc_journal_item.debit','acc_journal_item.credit','acc_journal_item.mode','acc_journal_item.created_at',                DB::raw('DATE_FORMAT(acc_journal_item.created_at, "%d-%m-%Y") as created_date')
                ,'acc_journal_item.opening_amount','acc_journal_item.closing_amount','acc_head.name as ledger_name')
            ->where('acc_journal.config_id', $configId)
            ->where('acc_journal_item.account_sub_head_id', $ledgerId)
            ->get()->toArray();

        return ['ledgerDetails' => $ledgerDetails, 'ledgerItems' => $getChildItems2];
    }*/
    public static function getLedgerWiseJournalItems($ledgerId, $configId)
    {
        // Get all items for the ledger
        $allItems = self::join('acc_journal', 'acc_journal.id', '=', 'acc_journal_item.account_journal_id')
            ->join('acc_head', 'acc_head.id', '=', 'acc_journal_item.account_sub_head_id')
            ->join('acc_voucher', 'acc_voucher.id', '=', 'acc_journal.voucher_id')
            ->select([
                'acc_journal_item.id',
                'acc_journal_item.account_ledger_id',
                'acc_journal_item.account_journal_id',
                'acc_journal.invoice_no',
                'acc_voucher.short_name as voucher_name',
                'acc_journal_item.account_head_id',
                'acc_journal_item.account_sub_head_id',
                'acc_journal_item.amount',
                'acc_journal_item.debit',
                'acc_journal_item.credit',
                'acc_journal_item.mode',
                'acc_journal_item.created_at',
                'acc_journal_item.opening_amount',
                'acc_journal_item.closing_amount',
                'acc_journal_item.parent_id',
                'acc_journal_item.is_parent',
                'acc_head.name as ledger_name',
                DB::raw('DATE_FORMAT(acc_journal_item.created_at, "%d-%m-%Y") as created_date')
            ])
            ->where('acc_journal.config_id', $configId)
            ->where('acc_journal_item.account_sub_head_id', $ledgerId)
            ->get()
            ->toArray();

        // Separate parent items (is_parent = 1)
        $parentItems = collect($allItems)->where('is_parent', 1)->keyBy('id');

        // Get child items by parent_id (these might be from different ledgers)
        $parentIds = $parentItems->pluck('id')->toArray();

        $childItems = self::join('acc_head', 'acc_head.id', '=', 'acc_journal_item.account_sub_head_id')
            ->select([
                'acc_journal_item.id',
                'acc_journal_item.account_ledger_id',
                'acc_journal_item.account_journal_id',
                'acc_journal_item.account_head_id',
                'acc_journal_item.account_sub_head_id',
                'acc_journal_item.amount',
                'acc_journal_item.debit',
                'acc_journal_item.credit',
                'acc_journal_item.mode',
                'acc_journal_item.created_at',
                'acc_journal_item.opening_amount',
                'acc_journal_item.closing_amount',
                'acc_journal_item.parent_id',
                'acc_head.name as ledger_name',
                DB::raw('DATE_FORMAT(acc_journal_item.created_at, "%d-%m-%Y") as created_date')
            ])
            ->whereIn('acc_journal_item.parent_id', $parentIds)
            ->get()
            ->groupBy('parent_id');

        // Attach children to parents
        $ledgerDetails = $parentItems->map(function ($parent) use ($childItems) {
            $parent['childItems'] = $childItems->get($parent['id'], collect())->toArray();
            return $parent;
        })->values()->toArray();

        $newDataSets = self::getLedgersForAccounting($allItems);

        return [
            'ledgerDetails' => $ledgerDetails,
            'ledgerItems' => $allItems,
            'ledgerItems2' => $newDataSets
        ];
    }

    public static function getLedgersForAccounting($items)
    {
        $newDataset = [];
        if (count($items) > 0) {
            foreach ($items as $item) {
                if ($item['is_parent'] == 1 && empty($item['parent_id'])){
                    $getChildLedgers = self::join('acc_head as ledger','ledger.id','=','acc_journal_item.account_sub_head_id')
                                            ->join('acc_head as motherLedger','motherLedger.id','=','acc_journal_item.account_sub_head_id')
                                            ->join('acc_journal', 'acc_journal.id', '=', 'acc_journal_item.account_journal_id')
                                            ->join('acc_voucher', 'acc_voucher.id', '=', 'acc_journal.voucher_id')
                                            ->select([
                                                'acc_journal_item.id','ledger.name as ledger_name','motherLedger.name as mother_ledger_name','acc_journal_item.amount','acc_journal_item.debit','acc_journal_item.credit','acc_journal_item.mode',DB::raw('DATE_FORMAT(acc_journal_item.created_at, "%d-%m-%Y") as created_date','acc_voucher.short_name as voucher_name')
                                            ])
                                            ->where('acc_journal_item.parent_id', $item['id'])
                                            ->get();
                    if (count($getChildLedgers) > 0) {
                        foreach ($getChildLedgers as $getChildLedger) {
                            $newDataset[] = [
                                'id' => $getChildLedger->id.' ( parent --> all data f C )',
                                'ledger_name' => $getChildLedger->ledger_name,
                                'mother_ledger_name' => $getChildLedger->mother_ledger_name,
                                'amount' => $getChildLedger->amount,
                                'debit' => $getChildLedger->debit,
                                'credit' => $getChildLedger->credit,
                                'mode' => $getChildLedger->mode == 'debit' ? 'Credit' : 'Debit',
                                'created_date' => $getChildLedger->created_date,
                                'voucher_name' => $getChildLedger->voucher_name
                            ];
                        }
                    }
                }elseif ($item['is_parent'] != 1 && !empty($item['parent_id'])){
                    $getParentLedger = self::join('acc_head as ledger','ledger.id','=','acc_journal_item.account_sub_head_id')
                        ->join('acc_head as motherLedger','motherLedger.id','=','acc_journal_item.account_sub_head_id')
                        ->select([
                            'acc_journal_item.id','ledger.name as ledger_name','motherLedger.name as mother_ledger_name','acc_journal_item.mode'
                        ])
                        ->where('acc_journal_item.id', $item['parent_id'])
                        ->first();

                    if ($getParentLedger) {
                        $newDataset[] = [
                            'id' => $item['id'].' ( child --> ladger name f p & all data f c )',
                            'ledger_name' => $getParentLedger->ledger_name,
                            'mother_ledger_name' => $getParentLedger->mother_ledger_name,
                            'amount' => $item['amount'],
                            'debit' => $item['debit'],
                            'credit' => $item['credit'],
                            'mode' => $getParentLedger->mode == 'debit' ? 'Credit' : 'Debit',
                            'created_date' => $item['created_date'],
                            'voucher_name' => $item['voucher_name']
                        ];
                    }
                }
            }
        }
        return $newDataset;
    }

    public static function handleOpeningClosing($journal,$journalItem){

        $opening = self::getLedgerWiseOpeningBalance(
            ledgerId: $journalItem->account_ledger_id,
                    configId: $journal->config_id,
                    journalItemId: $journalItem->id
                );
                $closing = $journalItem->mode === 'debit'
                    ? $opening + $journalItem->amount
                    : ($journalItem->mode === 'credit' ? $opening - $journalItem->amount : 0);

                $journalItem->update([
                    'opening_amount' => $opening,
                    'closing_amount' => $closing,
                ]);

                $findAccoundLegderHead = AccountHeadModel::find($journalItem->account_sub_head_id);
                $findAccoundLegderHead->update([
                    'amount' => $closing,
                ]);
    }



}

