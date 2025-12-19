<?php

namespace Modules\Accounting\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Inventory\App\Models\CategoryModel;

class AccountHeadModel extends Model
{
    use Sluggable;

    protected $table = 'acc_head';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'display_name',
        'config_id',
        'mother_account_id',
        'account_master_head_id',
        'parent_id',
        'customer_id',
        'vendor_id',
        'user_id',
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
        'parent',
        'isParent',
        'is_private',
        'showAmount',
        'opening_balance',
        'credit_limit',
        'credit_period',
        'account_id',
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
        'is_parent' => 0,
        'show_amount' => 0,
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
        return $this->hasOne(AccountHeadDetailsModel::class, 'account_head_id', 'id');
    }

    public function parent_account_head(): BelongsTo
    {
        return $this->belongsTo(AccountHeadModel::class, 'parent_id');
    }

    public function headDetail()
    {
        return $this->hasOne(AccountHeadDetailsModel::class, 'account_head_id', 'id'); // assuming you have this model
    }

    public function parent()
    {
        return $this->belongsTo(AccountHeadModel::class, 'parent_id');
    }




    // Optional: Self-referencing relationship for children
    public function child_account_heads(): HasMany
    {
        return $this->hasMany(AccountHeadModel::class, 'parent_id');
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
        $parent = AccountingModel::find($config);
        if ($parent and $parent->account_product_group_id) {
            $group = self::create(
                [
                    'name' => $name,
                    'display_name' => $name,
                    'product_group_id' => $entity['id'],
                    'parent_id' => $parent->account_product_group_id,
                    'level' => '3',
                    'source' => 'product-group',
                    'head_group' => 'ledger',
                    'config_id' => $config,
                    'is_private' => 1
                ]
            );
        }
        return $group;
        //AccountJournalModel::insertCustomerJournalVoucher($entity);
    }

    public static function insertCategoryLedger($config, $entity)
    {

        $name = "{$entity['name']}";
        $parent = AccountHeadModel::where('product_group_id', $entity->parent)->where('level', '2')->first();
        if ($parent) {
            self::create(
                [
                    'name' => $name,
                    'display_name' => $name,
                    'category_id' => $entity->id,
                    'parent_id' => $parent->id,
                    'level' => '3',
                    'source' => 'category',
                    'head_group' => 'ledger',
                    'is_private' => 1,
                    'config_id' => $config
                ]
            );
        }
        //AccountJournalModel::insertCustomerJournalVoucher($entity);
    }

    public static function insertCustomerLedger($config, $entity)
    {
        $name = "{$entity['mobile']}-{$entity['name']}";
        $accountHead = $config['account_customer_id'];
        if ($accountHead) {
            $head = self::create(
                [
                    'name' => $name,
                    'display_name' => $entity['name'],
                    'parent_id' => $accountHead,
                    'customer_id' => $entity['id'],
                    'level' => '3',
                    'credit_limit' => $entity['credit_limit'],
                    'source' => 'customer',
                    'head_group' => 'ledger',
                    'is_private' => 1,
                    'config_id' => $config->id
                ]
            );

            $exists = AccountHeadDetailsModel::where('account_head_id', $head->id)->first();
            if (!$exists) {
                AccountHeadDetailsModel::create([
                    'config_id'       => $head->config_id,      // or $head->config->id if using relationships
                    'account_head_id' => $head->id,
                ]);
            }
        }

    }

    public static function insertVendorLedger($config, $entity)
    {
        $name = "{$entity['mobile']}-{$entity['company_name']}";
        self::create(
            [
                'name' => $name,
                'display_name' => $entity['company_name'],
                'parent_id'    => $config->account_vendor_id ?? null, // assuming this is an ID
                'level' => '3',
                'vendor_id' => $entity->id,
                'head_group' => 'ledger',
                'source' => 'vendor',
                'is_private' => 1,
                'config_id' => $config->id
            ]
        );
    }

    public static function insertCurrentAssetsLedger($config, $entity)
    {
        $name = "{$entity['name']}";
        $array = [
            'name' => $name,
            'display_name' => $name,
            'source' => 'vendor',
            'parent_id' => '5',
            'level' => '3',
            'vendor_id' => $entity['id'],
            'head_group' => 'ledger',
            'is_private' => 1,
            'config_id' => $config
        ];
        $entity = self::create($array);
        //AccountJournalModel::insertCustomerJournalVoucher($entity);
    }

    public static function getLedger($request, $domain)
    {

        $query = self::select(['name', 'slug', 'id', 'level'])
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
                    'acc_head.is_private',
                    'acc_head.amount',
                    'parent.name as parent_name',
                    'mother.name as mother_name'
                ]);

            // Search term filter
            if (!empty($request['term'])) {
                $searchTerm = '%' . $request['term'] . '%';
                $query->where(function ($q) use ($searchTerm) {
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
                ->orderBy('parent.name', 'ASC')
                ->orderBy('acc_head.name', 'ASC')
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

    public static function getRecordsForLocalStorage($request, $domain)
    {
        // dd($request->query('mode'));
        $entities = self::leftjoin('acc_head as parent', 'parent.id', '=', 'acc_head.parent_id')
            ->leftjoin('acc_setting as mother', 'acc_head.mother_account_id', '=', 'mother.id')
            ->select([
                'acc_head.id',
                'acc_head.level',
                'acc_head.name',
                'acc_head.slug',
                'acc_head.is_private',
                'parent.name as parent_name',
                'mother.name as mother_name'
            ])
            ->orderBy('acc_head.name', 'ASC')
            ->get();

        $data = [];
        if (sizeof($entities) > 0) {
            foreach ($entities as $val) {
                if ($val['level'] == 2) {
                    $data[$val['level'] . '-UserRole'][] = $val;
                } elseif ($val['level'] == 1) {
                    $data[$val['level'] . '-SubGroup'][] = $val;
                } else {
                    $data[$val['level'] . '-Ledger'][] = $val;
                }
            }
        }


        return $data;
    }

    public static function getAccountHeadDropdown($domain, $head)
    {
        return DB::table('acc_head')
            ->select([
                'acc_head.id',
                'acc_head.parent_id',
                'acc_head.name',
                'acc_head.display_name',
                'acc_head.slug',
                'acc_head.code',
                'acc_head.head_group',
                'acc_head.mode',
            ])
            ->where([
                ['acc_head.config_id', $domain['acc_config']],
                ['acc_head.head_group', $head]
            ])
            ->get();
    }

    public static function getAccountLedgerDropdown($domain, $head = '')
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
                'acc_head.display_name',
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

    public static function getAccountAllDropdownBySlug($domain, $head = 'head')
    {
        $data = self::where('acc_head.config_id', $domain['acc_config'])
            ->leftjoin('acc_head as l_head', 'l_head.id', '=', 'acc_head.parent_id')
            ->where('acc_head.head_group', $head)->where('acc_head.status', 1)
            ->select([
                'acc_head.id',
                'acc_head.parent_id',
                'l_head.name as parent_name',
                'l_head.slug as parent_slug',
                'acc_head.name',
                'acc_head.display_name',
                'acc_head.slug',
                'acc_head.code',
                'acc_head.head_group',
                'acc_head.level',
                'acc_head.is_private as is_private',
            ])
            ->get()->toArray();
        return $data;
    }

    public static function getAccountHeadWithParent($id)
    {

        $entity = self::where('id', $id)
            ->select([
                'acc_head.id',
                'acc_head.parent_id as parent_id',
                'acc_head.level',
                'acc_head.name',
                'acc_head.display_name',
                'acc_head.slug'
            ])
            ->get()->first();
        return $entity;
    }

    public static function getAccountHeadWithParentPramValue($pram, $value)
    {

        $entity = self::where($pram, $value)
            ->select([
                'acc_head.id',
                'acc_head.parent_id as parent_id',
                'acc_head.level',
                'acc_head.name',
                'acc_head.display_name',
                'acc_head.slug'
            ])
            ->get()->first();
        return $entity;
    }

    public static function generateAccountHead($domain)
    {

        DB::transaction(function () use ($domain) {

            $configId = $domain['acc_config'];
            $domainId = $domain['domain_id'];

            $config = AccountingModel::findOrFail($configId);
            $parentHeads = AccountHeadMasterModel::whereNull('parent_id')->get();
            foreach ($parentHeads as $head) {
                $entity = AccountHeadModel::updateOrCreate(
                    [
                        'config_id' => $config->id,
                        'account_master_head_id' => $head->id,
                    ],
                    [
                        'mother_account_id' => $head->mother_account_id,
                        'name' => $head->name,
                        'display_name' => $head->name,
                        'slug' => $head->slug,
                        'head_group' => 'head',
                        'level' => $head->level,
                        'is_private' => true,
                        'parent_id' => null,
                    ]
                );

                foreach ($head->children as $child) {

                    $subHead = AccountHeadModel::updateOrCreate(
                        [
                            'config_id' => $config->id,
                            'account_master_head_id' => $child->id,
                        ],
                        [
                            'mother_account_id' => $child->mother_account_id,
                            'name' => $child->name,
                            'display_name' => $child->name,
                            'slug' => $child->slug,
                            'head_group' => 'sub-head',
                            'level' => $child->level,
                            'is_private' => true,
                            'parent_id' => $entity->id,
                        ]
                    );

                    if (!$subHead->headDetail) {
                        AccountHeadDetailsModel::updateOrCreate(
                            [
                                'config_id' => $config->id,
                                'account_head_id' => $subHead->id,
                            ]
                        );
                    }

                    foreach ($child->children as $row) {
                        $ledger = AccountHeadModel::updateOrCreate(
                            [
                                'config_id' => $config->id,
                                'account_master_head_id' => $row->id,
                            ],
                            [
                                'mother_account_id' => $row->mother_account_id,
                                'name' => $row->name,
                                'display_name' => $row->name,
                                'slug' => $row->slug,
                                'head_group' => 'ledger',
                                'level' => $row->level,
                                'is_private' => true,
                                'parent_id' => $subHead->id,
                            ]
                        );
                        if (!$row->headDetail) {
                            AccountHeadDetailsModel::updateOrCreate(
                                [
                                    'config_id' => $config->id,
                                    'account_head_id' => $ledger->id,
                                ]
                            );
                        }
                    }
                }
            }

        });
    }

    public static function initialLedgerSetup($domain){

        $domainId = $domain->id;
        $configId = $domain['acc_config'];
        $config = ConfigModel::findOrFail($configId);
        $currentAssets = TransactionModeModel::where('config_id', $configId)
            ->where('status', 1)
            ->get();
        foreach ($currentAssets as $asset) {
            if($asset->method->slug == 'bank'){
                self::insertTransactionBankAccount($config,$asset);
            }else{
                self::insertTransactionAccount($config,$asset);
            }
        }

        $customers = CustomerModel::where('domain_id', $domainId)
            ->where('status', 1)
            ->get();
        foreach ($customers as $customer) {
            self::insertCustomerAccount($config,$customer);
        }

        $vendors = VendorModel::where('domain_id', $domainId)
            ->where('status', 1)
            ->get();
        foreach ($vendors as $vendor) {
            self::insertVendorAccount($config,$vendor);
        }

        $users = UserModel::leftJoin('cor_setting', 'cor_setting.id', '=', 'users.employee_group_id')
            ->where('users.domain_id', $domainId)
            ->whereIn('cor_setting.name', ['User', 'Employee']) // ✅ this is the correct syntax
            ->where('users.enabled', 1)
            ->select('users.id','users.name')
            ->get();
        if($users){
            foreach ($users as $user) {
                self::insertUserAccount($config, $user);
            }
        }

        $investors = UserModel::leftJoin('cor_setting', 'cor_setting.id', '=', 'users.employee_group_id')
            ->where('users.domain_id', $domainId)
            ->whereIn('cor_setting.name', ['Director']) // ✅ this is the correct syntax
            ->where('users.enabled', 1)
            ->select('users.id','users.name')
            ->get();
        if($investors){
            foreach ($investors as $investor) {
                self::insertCapitalInvestmentAccount($config, $investor);
            }
        }

        $groups = CategoryModel::where([
            'config_id' => $domain['inv_config'],
            'parent' => null,
            'status'    => 1,
        ])->get();
        if($groups){
            foreach ($groups as $group) {
                self::insertCategoryGroupAccount($config, $group);
            }
        }

    }

    public static function insertTransactionBankAccount($config , $entity)
    {

        $parent = null;

        $parts = [];
        if ($entity->bank->name) { $parts[] = $entity->bank->name; }
       // if ($entity) { $parts[] = $entity->name; }
        if ($entity->account_number) { $parts[] = $entity->account_number; }
       // if ($entity->branch_name) { $parts[] = $entity->branch_name; }
        $implode = implode(' ', $parts);
        $displayName = "{$implode}";


        $head = AccountHeadModel::updateOrCreate(
           [
               'account_id' => $entity->id,
               'config_id' => $config->id,
           ],
           [
               'name' => $displayName,
               'display_name' => $displayName,
               'slug' => $entity->slug,
               'parent_id' =>  $config->account_bank_id ??null, // Assuming $parent is a model
               'head_group' => 'ledger',
               'level' => 3,
               'mode' => 'debit',
               'is_private' => true,
           ]
       );

       if (!$head->headDetail) {
           AccountHeadDetailsModel::updateOrCreate(
               [
                   'config_id' => $head->config_id,
                   'account_head_id' => $head->id,
               ]
           );
       }
       return $displayName;

    }

    public static function insertTransactionAccount($config , $entity)
    {

        $parent = null;
        $methodSlug = $entity->method->slug;
        $parent = match($methodSlug){
                'cash' => $config->account_cash_id,
                'bank' => $config->account_bank_id,
                'mobile-banking' => $config->account_mobile_id,
                default => null,
        };
        $parts = [];
        if($methodSlug == 'bank'){
            if ($entity->bank->name) { $parts[] = $entity->bank->name; }
            if ($entity) { $parts[] = $entity->name; }
            if ($entity->account_number) { $parts[] = $entity->account_number; }
            if ($entity->branch_name) { $parts[] = $entity->branch_name; }
        }elseif($methodSlug == 'mobile-banking'){
            if ($entity) { $parts[] = $entity->name; }
            if ($entity->mobile) { $parts[] = $entity->mobile; }
        }else{
            if ($entity) { $parts[] = $entity->name; }
        }
        $implode = implode(' ', $parts);
        $displayName = "{$implode}";

       $head = AccountHeadModel::updateOrCreate(
           [
               'account_id' => $entity->id,
               'config_id' => $config->id,
           ],
           [
               'name' => $displayName,
               'display_name' => $displayName,
               'slug' => $entity->slug,
               'parent_id' => $parent??null, // Assuming $parent is a model
               'head_group' => 'ledger',
               'level' => 3,
               'mode' => 'debit',
               'is_private' => true,
           ]
       );

       if (!$head->headDetail) {
           AccountHeadDetailsModel::updateOrCreate(
               [
                   'config_id' => $head->config_id,
                   'account_head_id' => $head->id,
               ]
           );
       }
       return $displayName;

    }


    public static function insertCustomerAccount($config, $entity)
    {
      //  $name = "{$entity->mobile}-{$entity->name}";
        $name = "{$entity->name}";

        $head = AccountHeadModel::updateOrCreate(
            [
                'customer_id' => $entity->id,
            ],
            [
                'config_id'    => $config->id,
                'parent_id'    => $config->account_customer_id ?? null, // assuming it's an ID
                'name'         => $name,
                'display_name' => $entity->name,
                'slug'         => $entity->slug,
                'head_group'   => 'ledger',
                'level'        => 3,
                'mode'         => 'debit',
                'is_private'   => true, // optional
            ]
        );

        if (!$head->headDetail) {
            AccountHeadDetailsModel::updateOrCreate(
                [
                    'config_id'  => $head->config_id,
                    'account_head_id' => $head->id,
                ]
            );
        }
    }

    public static function insertVendorAccount($config, $entity)
    {
       // $name = "{$entity->mobile}-{$entity->company_name}";
        $name = "{$entity->company_name}";
        $head = AccountHeadModel::updateOrCreate(
            [
                'vendor_id' => $entity->id,
            ],
            [
                'config_id'    => $config->id,
                'parent_id'    => $config->account_vendor_id ?? null, // assuming this is an ID
                'name'         => $name,
                'display_name' => $entity->company_name,
                'slug'         => $entity->slug,
                'head_group'   => 'ledger',
                'level'        => 3,
                'mode'         => 'credit',
                'is_private'   => true,
            ]
        );

        if (!$head->headDetail) {
            AccountHeadDetailsModel::updateOrCreate(
                [
                    'config_id'  => $head->config_id,
                    'account_head_id' => $head->id,
                ]
            );
        }
    }

    public static function insertUserAccount($config,$user)
    {
        $head = AccountHeadModel::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'config_id'    => $config->id,
                'parent_id'    => $config->account_user_id ?? null, // Assumes this is an ID field
                'name'         => $user->name,
                'display_name' => $user->name,
                'slug'         => \Str::slug($user->name), // Optional: Convert name to slug format
                'head_group'   => 'ledger',
                'level'        => 3,
                'mode'         => 'credit',
                'is_private'   => true, // Optional if applicable
            ]
        );

        if (!$head->headDetail) {
            AccountHeadDetailsModel::updateOrCreate(
                [
                    'config_id'  => $head->config_id,
                    'account_head_id' => $head->id,
                ]
            );
        }
    }

    public static function insertCapitalInvestmentAccount($config, $user)
    {

        $head = AccountHeadModel::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'config_id'    => $config->id,
                'parent_id'    => $config->capital_investment_id ?? null, // Assuming this is the ID
                'name'         => $user->name,
                'display_name' => $user->name,
                'slug'         => \Str::slug($user->name),
                'head_group'   => 'ledger',
                'level'        => 3,
                'mode'         => 'credit',
                'is_private'   => true, // Optional, if applicable
            ]
        );

        if (!$head->headDetail) {
            AccountHeadDetailsModel::updateOrCreate(
                [
                    'config_id'  => $head->config_id,
                    'account_head_id' => $head->id,
                ]
            );
        }
    }

    public static function insertCategoryGroupAccount($config, $entity)
    {
        $head = AccountHeadModel::updateOrCreate(
            [
                'product_group_id' => $entity->id,
            ],
            [
                'config_id'        => $config->id,
                'parent_id'        => $config->account_product_group_id ?? null, // Ensure this is the correct field
                'name'             => $entity->name,
                'display_name'     => $entity->name,
                'head_group'       => 'ledger',
                'level'            => 3,
                'is_private'       => true,
                'mode'             => 'debit',
            ]
        );

        if (!$head->headDetail) {
            AccountHeadDetailsModel::updateOrCreate(
                [
                    'config_id'  => $head->config_id,
                    'account_head_id' => $head->id,
                ]
            );
        }
    }


    public static function getAccountHeadLedgerSummary($head)
    {
        $investors = AccountHeadModel::where('acc_head.parent_id', $head)
            ->select(DB::raw('SUM(acc_head.amount) as amount'))
            ->first();
        return $investors;
    }
    public static function getAccountHeadLedger($head , $limit = 10)
    {
        $investors = self::leftJoin('acc_transaction_mode', 'acc_transaction_mode.id', '=', 'acc_head.account_id')
            ->select('acc_head.id as id', 'acc_head.name as name', 'acc_head.amount as amount','parent.name as parent_name')
            ->join('acc_head as parent','parent.id','=','acc_head.parent_id')
            ->leftJoin('cor_vendors', 'cor_vendors.id', '=', 'acc_head.vendor_id')
            ->leftJoin('cor_customers', 'cor_customers.id', '=', 'acc_head.customer_id')
            ->leftJoin('acc_head_details', 'acc_head_details.account_head_id', '=', 'acc_head.id')
            ->where("acc_head.parent_id", $head)
            ->limit($limit)
            ->orderBy('acc_head.amount','DESC')
            ->get();
        return $investors;
    }

    public static function getAccountHeadOutstanding(int $configId, array $params): ?object
    {
        $query = self::where('config_id', $configId);

        // Apply conditionally if type is customer_id
        if (!empty($params['type']) && $params['type'] === 'customer') {
            $query->where('customer_id', $params['customer_id']);
        }

        // Select specific fields
        $query->select([
            'acc_head.id as id',
            'acc_head.name',
            'acc_head.display_name',
            'acc_head.amount as outstanding_amount',
        ]);

        return $query->first();
    }


}

