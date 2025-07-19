<?php

namespace Modules\Core\App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Accounting\App\Models\AccountJournalItemModel;
use Modules\Inventory\App\Models\ConfigPurchaseModel;

class VendorModel extends Model
{
    use Sluggable;


    protected $table = 'cor_vendors';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'company_name',
        'mobile',
        'address',
        'email',
        'opening_balance',
        'binno',
        'vendor_group_id',
        'tinno',
        'code',
        'vendor_code',
        'customer_id',
        'sub_domain_id',
        'domain_id',
        'status'
    ];

    public function accountHead(): HasOne
    {
        return $this->hasOne(AccountHeadModel::class, 'vendor_id');
    }

    public static function getRecords($request, $domain)
    {

        $page = max((int)($request['page'] ?? 1) - 1, 0);
        $perPage = (int)($request['offset'] ?? 50);
        $skip = $page * $perPage;

        $vendors = self::leftJoin('cor_setting', 'cor_setting.id', '=', 'cor_vendors.vendor_group_id')
            ->join('acc_head', 'acc_head.vendor_id', '=', 'cor_vendors.id')
            ->where('cor_vendors.domain_id', $domain['global_id'])
            ->select([
                'cor_vendors.id as id',
                'acc_head.amount as outstanding',
                'acc_head.id as account_id',
                'cor_vendors.name as name',
                'cor_vendors.company_name as company_name',
                'email',
                'mobile',
                'cor_setting.name as group_name',
                'cor_setting.id as group_id',
                'cor_vendors.created_at as created_at '
            ]);

        if (isset($request['term']) && !empty($request['term'])) {
            $vendors = $vendors->whereAny(['name', 'email', 'company_name', 'mobile'], 'LIKE', '%' . $request['term'] . '%');
        }

        if (isset($request['name']) && !empty($request['name'])) {
            $vendors = $vendors->where('name', $request['name']);
        }

        if (isset($request['mobile']) && !empty($request['mobile'])) {
            $vendors = $vendors->where('mobile', $request['mobile']);
        }

        if (isset($request['company_name']) && !empty($request['company_name'])) {
            $vendors = $vendors->where('company_name', $request['company_name']);
        }

        $total = $vendors->count();
        $entities = $vendors->skip($skip)
            ->take($perPage)
            ->orderBy('id', 'DESC')
            ->get();

        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }


    public static function getRecordsForLocalStorage($request, $domain)
    {
        $vendors = self::leftJoin('cor_setting', 'cor_setting.id', '=', 'cor_vendors.vendor_group_id')
            ->leftJoin('acc_head', 'acc_head.vendor_id', '=', 'cor_vendors.id')
            ->where('cor_vendors.domain_id', $domain['global_id'])
            ->select([
                'cor_vendors.id',
                'cor_vendors.name',
                'cor_vendors.vendor_code',
                'cor_vendors.code',
                'cor_vendors.company_name',
                'cor_vendors.slug',
                'cor_vendors.address',
                'cor_vendors.email',
                'cor_vendors.mobile',
                'cor_vendors.unique_id',
                'cor_vendors.sub_domain_id',
                'cor_setting.name as group_name',
                'cor_setting.id as group_id',
                'cor_vendors.customer_id',
                DB::raw('DATE_FORMAT(cor_vendors.created_at, "%d-%m-%Y") as created_date'),
                'cor_vendors.created_at',
                'acc_head.name as ledger_name',
                'acc_head.id as ledger_id',
                'acc_head.opening_balance',
                'acc_head.amount as closing_balance',
                'acc_head.credit',
                'acc_head.debit',
                'acc_head.credit_limit',
                'acc_head.credit_period',
                'acc_head.earn_point',
                'acc_head.balance_bill_by_bill',
                'acc_head.is_credit_date_check_voucher_entry'
            ])
            ->orderByDesc('cor_vendors.id')
            ->get();

        return $vendors;
    }


    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $date = new DateTime("now");
            $model->unique_id = self::quickRandom();
            $model->created_at = $date;
            $model->updated_at = $date;
            $model->status = true;
        });

        self::updating(function ($model) {
            $date = new DateTime("now");
            $model->unique_id = self::quickRandom();
            $model->updated_at = $date;
        });

    }

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

    public static function quickRandom($length = 32)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }


    public static function insertPurchaseVendor($domain, $input)
    {
        $domainConfig = ConfigPurchaseModel::where('config_id', $domain['inv_config']);
        if ($domainConfig and $domainConfig->default_purchase_group_id) {
            $customer['vendor_group_id'] = $domainConfig->default_purchase_group_id;
        }
        $customer['domain_id'] = $domain['domain_id'];
        $customer['company_name'] = ($input['vendor_name']) ? $input['vendor_name'] : '';
        $customer['name'] = ($input['vendor_name']) ? $input['vendor_name'] : '';
        $customer['mobile'] = ($input['vendor_mobile']) ? $input['vendor_mobile'] : '';
        $customer['email'] = ($input['vendor_email']) ? $input['vendor_email'] : '';
        return self::create($customer);


    }

    public static function uniqueVendorCheck($domain, $mobile, $name)
    {
        return self::where([['company_name', $name], ['mobile', $mobile], ['domain_id', $domain]])->first();
    }

}
