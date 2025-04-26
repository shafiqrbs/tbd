<?php

namespace Modules\Accounting\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AccountHeadModel extends Model
{
    use Sluggable;

    protected $table = 'acc_head';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'config_id',
        'mother_account_id',
        'parent_id',
        'customer_id',
        'vendor_id',
        'product_group_id',
        'category_id',
        'group',
        'head_group',
        'code',
        'level',
        'slug',
        'status',
        'amount',
        'credit',
        'debit',
        'isParent',
        'showAmount',
        'opening_balance',
        'credit_limit',
        'credit_period',
        'balance_bill_by_bill',
        'is_credit_date_check_voucher_entry',
        'provide_bank_details'
    ];

    protected $attributes = [
        'status' => true,
        'level' => 1,
        'amount' => 0,
        'credit' => 0,
        'debit' => 0,
        'isParent' => 0,
        'showAmount' => 0,
        'opening_balance' => 0,
        'credit_limit' => 0,
        'credit_period' => 0,
        'balance_bill_by_bill' => 0,
        'is_credit_date_check_voucher_entry' => 0,
        'provide_bank_details' => 0,
    ];


    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function accountHeadDetails()
    {
        return $this->hasOne(AccountHeadDetailsModel::class,'account_id','id');
    }

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

    public static function insertCategoryGroupLedger($config, $entity)
    {
        $name = "{$entity['name']}";
        $parent = AccountHeadModel::where('config_id',$config)->where('slug', 'inventory-assets')->where('head_group', 'account-head')->first();
        if($parent){
            self::create(
                [
                    'name' => $name,
                    'product_group_id' => $entity['id'],
                    'parent_id' => $parent['id'],
                    'level' => '2',
                    'source' => 'product-group',
                    'head_group' => 'sub-head',
                    'config_id' => $config
                ]
            );
        }
        //AccountJournalModel::insertCustomerJournalVoucher($entity);
    }

    public static function insertCategoryLedger($config, $entity)
    {

        $name = "{$entity['name']}";
        $parent = AccountHeadModel::where('product_group_id',$entity->parent)->where('level', '2')->first();
        if($parent) {
            self::create(
                [
                    'name' => $name,
                    'category_id' => $entity->id,
                    'parent_id' => $parent->id,
                    'level' => '3',
                    'source' => 'category',
                    'head_group' => 'ledger',
                    'config_id' => $config
                ]
            );
        }
        //AccountJournalModel::insertCustomerJournalVoucher($entity);
    }

    public static function insertCustomerLedger($config, $entity)
    {
        $name = "{$entity['mobile']}-{$entity['name']}";
        $parent = AccountHeadModel::where('config_id',$config)->where('slug','account-receivable')->where('level', 2)->where('head_group', 'sub-head')->first();
        if($parent){
            self::create(
                [
                    'name' => $name,
                    'parent_id' => $parent['id'],
                    'customer_id' => $entity['id'],
                    'level' => '3',
                    'source' => 'customer',
                    'head_group' => 'ledger',
                    'config_id' => $config
                ]
            );
        }

    }

    public static function insertVendorLedger($config, $entity)
    {
        $name = "{$entity['mobile']}-{$entity['company_name']}";
        $parent = AccountHeadModel::where('config_id',$config)->where('slug','account-payable')->where('level', 2)->where('head_group','sub-head')->first();
        if($parent) {
            self::create(
                [
                    'name' => $name,
                    'parent_id' => $parent['id'],
                    'level' => '3',
                    'vendor_id' => $entity['id'],
                    'head_group' => 'ledger',
                    'source' => 'vendor',
                    'config_id' => $config
                ]
            );
        }
    }


    public static function insertCurrentAssetsLedger($config, $entity)
    {
        $name = "{$entity['name']}";
        $array=[
            'name' => $name,
            'source' => 'vendor',
            'parent_id' => '5',
            'level' => '3',
            'vendor_id' => $entity['id'],
            'head_group' => 'ledger',
            'config_id' => $config
        ];
        $entity = self::create($array);
        //AccountJournalModel::insertCustomerJournalVoucher($entity);
    }


    public static function getLedger($request,$domain)
    {

        $query = self::select(['name', 'slug', 'id','level'])
            ->where([['level', 1], ['config_id', $domain['config_id']]]);
        return $query->get()->toArray();
    }

    public static function getAccountSubHead($domain)
    {
        $query = self::select(['name', 'slug', 'id'])
            ->where([['level', 2], ['config_id', $domain['config_id']]]);
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
        try {
            // Pagination setup
            $perPage = max(1, (int)($request['offset'] ?? 50));  // Ensure at least 1 item per page
            $currentPage = max(1, (int)($request['page'] ?? 1)); // Ensure page is at least 1
            $skip = ($currentPage - 1) * $perPage;

            // Base query
            $query = self::where('acc_head.config_id', $domain['acc_config'])
                ->leftJoin('acc_head as parent', 'parent.id', '=', 'acc_head.parent_id')
                ->leftJoin('acc_setting as mother', 'acc_head.mother_account_id', '=', 'mother.id')
                ->select([
                    'acc_head.id',
                    'acc_head.level',
                    'acc_head.name',
                    'acc_head.slug',
                    'acc_head.code',
                    'acc_head.amount',
                    'parent.name as parent_name',
                    'mother.name as mother_name'
                ]);

            // Search term filter
            if (!empty($request['term'])) {
                $searchTerm = '%' . $request['term'] . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('acc_head.name', 'LIKE', $searchTerm)
                        ->orWhere('acc_head.slug', 'LIKE', $searchTerm);
                });
            }

            // Group filter
            if (!empty($request['group'])) {
                $query->where('acc_head.head_group', $request['group']);
            }

            // Status filter
            $query->where('acc_head.status', 1);

            // Get total count before pagination
            $total = $query->count();

            // Apply pagination and ordering
            $entities = $query->skip($skip)
                ->take($perPage)
                ->orderBy('acc_head.id', 'DESC')
                ->get();

            return [
                'count' => $total,
                'entities' => $entities,
                'per_page' => $perPage,
                'current_page' => $currentPage
            ];

        } catch (\Exception $e) {
            // Log the error if needed
            // Log::error('Error fetching records: ' . $e->getMessage());

            return [
                'count' => 0,
                'entities' => [],
                'error' => 'An error occurred while fetching records'
            ];
        }
    }


    public static function getRecordsForLocalStorage($request,$domain)
    {
       // dd($request->query('mode'));
        $entities = self::leftjoin('acc_head as parent','parent.id','=','acc_head.parent_id')
            ->leftjoin('acc_setting as mother','acc_head.mother_account_id','=','mother.id')
            ->select([
                'acc_head.id',
                'acc_head.level',
                'acc_head.name',
                'acc_head.slug',
                'parent.name as parent_name',
                'mother.name as mother_name'
            ])
            ->orderBy('acc_head.name','ASC')
            ->get();

        $data = [];
        if(sizeof($entities)>0){
            foreach ($entities as $val){
                if ($val['level'] == 2){
                    $data[$val['level'].'-UserRole'][] = $val;
                }elseif ($val['level'] == 1){
                    $data[$val['level'].'-SubGroup'][] = $val;
                }else{
                    $data[$val['level'].'-Ledger'][] = $val;
                }
            }
        }


        return $data;
    }

    public static function getAccountHeadDropdown($domain,$head)
    {
        return DB::table('acc_head')
            ->select([
                'acc_head.id',
                'acc_head.parent_id',
                'acc_head.name',
                'acc_head.slug',
                'acc_head.code',
                'acc_head.head_group',
                'acc_head.mode',
            ])
            ->where([
                ['acc_head.config_id',$domain['acc_config']],
                ['acc_head.head_group',$head]
            ])
            ->get();
    }

    public static function getAccountLedgerDropdown($domain,$head='')
    {
        $data = self::where('acc_head.config_id', $domain['acc_config'])
            ->join('acc_head as l_head', 'l_head.id', '=', 'acc_head.parent_id')
            ->whereNotNull('acc_head.parent_id')
            ->select([
                'acc_head.id',
                'acc_head.parent_id',
                'l_head.name as parent_name',
                'l_head.slug as parent_slug',
                'acc_head.name',
                'acc_head.slug',
                'acc_head.code',
            ])
            ->get()
            ->groupBy('parent_id')
            ->map(function ($group) {
                return [
                    'group' => $group->first()->parent_name,
                    'items' => $group->map(fn($item) => [
                        'id' => $item->id,
                        'item_name' => $item->name,
                        'slug' => $item->slug,
                    ])->values(),
                ];
            })->values()->toArray();
        return $data;
    }
    public static function getAccountAllDropdownBySlug($domain,$head='account-head')
    {
        $data = self::where('acc_head.config_id', $domain['acc_config'])
            ->leftjoin('acc_head as l_head', 'l_head.id', '=', 'acc_head.parent_id')
            ->where('acc_head.head_group',$head)
            #->where('acc_head.status',1)
            ->select([
                'acc_head.id',
                'acc_head.parent_id',
                'l_head.name as parent_name',
                'l_head.slug as parent_slug',
                'acc_head.name',
                'acc_head.slug',
                'acc_head.code',
                'acc_head.head_group',
                'acc_head.level',
            ])
            ->get()->toArray();
        return $data;
    }



}

