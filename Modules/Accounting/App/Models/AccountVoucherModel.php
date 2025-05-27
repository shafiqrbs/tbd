<?php

namespace Modules\Accounting\App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;


class AccountVoucherModel extends Model
{
    use HasFactory,Sluggable;

    protected $table = 'acc_voucher';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'voucher_type_id',
        'master_voucher_id',
        'name',
        'short_name',
        'short_code',
        'mode',
        'is_default',
        'is_private',
        'status'
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


    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public function voucher_type(): BelongsTo
    {
        return $this->belongsTo(SettingTypeModel::class);
    }


    public function ledger_account_head_primary(): BelongsToMany
    {
        return $this->belongsToMany(
            AccountHeadModel::class,
            'acc_voucher_account_primary',
            'account_voucher_id',
            'primary_account_head_id'
        );
    }

    public function ledger_account_head_secondary(): BelongsToMany
    {
        return $this->belongsToMany(
            AccountHeadModel::class,
            'acc_voucher_account_secondary',
            'account_voucher_id',
            'secondary_account_head_id'
        );
    }

    public function parent_account_head(): BelongsTo
    {
        return $this->belongsTo(AccountHeadModel::class, 'parent_id');
    }


    public static function getEntityDropdown($request, $domain)
    {

        return DB::table('acc_voucher')
            ->leftJoin('acc_setting','acc_setting.id','=','acc_voucher.voucher_type_id')
            ->with['ledger_account_head']
            ->select([
                'acc_voucher.id',
                'acc_setting.id as voucher_type_id',
                'acc_setting.name as voucher_type_name',
                'acc_voucher.name as name',
                'acc_voucher.short_name as short_name',
                'acc_voucher.short_code as short_code',
                'acc_voucher.mode as mode',
                'acc_voucher.is_private as is_private',
                'acc_voucher.status as status',
            ])
            ->where([
                ['acc_voucher.config_id',$domain['acc_config']],
                ['acc_voucher.status','1']
            ])
            ->get();
    }

    public static function getRecords($request, $domain)
    {

        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 1;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;
        $entity = self:: where('acc_voucher.config_id', $domain['acc_config'])
            ->leftJoin('acc_setting','acc_setting.id','=','acc_voucher.voucher_type_id')
            ->select([
                'acc_voucher.id',
                'acc_setting.id as voucher_type_id',
                'acc_setting.name as voucher_type_name',
                'acc_voucher.name as name',
                'acc_voucher.short_name as short_name',
                'acc_voucher.short_code as short_code',
                'acc_voucher.mode as mode',
                'acc_voucher.is_private as is_private',
                'acc_voucher.status as status'

            ])
            ->orderBy('acc_voucher.name','ASC');

        if (isset($request['term']) && !empty($request['term'])) {
            $entity = $entity->whereAny(
                ['acc_voucher.name', 'acc_voucher.slug'], 'LIKE', '%' . $request['term'] . '%');
        }
        if (isset($request['type']) && !empty($request['type'])) {
            $entity = $entity->where(
                ['acc_setting.voucher_type_id'=>$request['type']]);
        }
        $total = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('acc_setting.id', 'DESC')
            ->get();

        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }

    public static function getVoucherWiseLedgerDetails($request, $domain)
    {

        return self::where('acc_voucher.status', '1')
            ->where('acc_voucher.config_id', $domain['acc_config'])
            ->leftJoin('acc_setting', 'acc_setting.id', '=', 'acc_voucher.voucher_type_id')
            ->with([
                'ledger_account_head_primary' => function ($query) {
                    $query->where('status', 1)
                        ->select([
                            'id', 'parent_id', 'account_id', 'account_master_head_id',
                            'vendor_id', 'customer_id', 'code', 'name', 'amount',
                            'credit', 'debit', 'level', 'head_group', 'slug',
                            'display_name', 'opening_balance'
                        ])
                        ->with(['child_account_heads' => function ($subQuery) {
                            $subQuery->where('status', 1)
                                ->select([
                                    'id', 'parent_id', 'account_id', 'account_master_head_id',
                                    'vendor_id', 'customer_id', 'code', 'name', 'amount',
                                    'credit', 'debit', 'level', 'head_group', 'slug',
                                    'display_name', 'opening_balance'
                                ]);
                        }]);
                },

                'ledger_account_head_secondary' => function ($query) {
                    $query->where('status', 1)
                        ->select([
                            'id', 'parent_id', 'account_id', 'account_master_head_id',
                            'vendor_id', 'customer_id', 'code', 'name', 'amount',
                            'credit', 'debit', 'level', 'head_group', 'slug',
                            'display_name', 'opening_balance'
                        ])
                        ->with(['child_account_heads' => function ($subQuery) {
                            $subQuery->where('status', 1)
                                ->select([
                                    'id', 'parent_id', 'account_id', 'account_master_head_id',
                                    'vendor_id', 'customer_id', 'code', 'name', 'amount',
                                    'credit', 'debit', 'level', 'head_group', 'slug',
                                    'display_name', 'opening_balance'
                                ]);
                        }]);
                }
            ])
            ->select([
                'acc_voucher.id',
                'acc_setting.id as voucher_type_id',
                'acc_setting.name as voucher_type_name',
                'acc_voucher.name',
                'acc_voucher.short_name',
                'acc_voucher.short_code',
                'acc_voucher.mode',
                'acc_voucher.is_private',
                'acc_voucher.status',
                'acc_voucher.is_default',
            ])
            ->orderBy('acc_voucher.name', 'ASC')
            ->get();
    }

    public static function resetVoucher($domain)
    {

        // Get active master vouchers

        $configId = $domain['acc_config'];

        $masterVouchers = AccountMasterVoucherModel::where('status', 1)->get();

        foreach ($masterVouchers as $head) {

            self::updateOrCreate(
                [
                    'config_id'         => $configId,
                    'master_voucher_id' => $head->id,
                ],
                [
                    'voucher_type'      => $head->voucher_type_id,
                    'name'              => $head->name,
                    'short_name'        => $head->short_name,
                    'short_code'        => $head->short_code,
                    'slug'              => $head->slug,
                    'mode'              => $head->mode,
                    'status'            => 1,
                    'is_default'        => 0,
                    'is_private'        => 1,
                ]
            );
        }

    }




}
