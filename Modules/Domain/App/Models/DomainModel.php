<?php

namespace Modules\Domain\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\Hospital\App\Models\HospitalConfigModel;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\NbrVatTax\App\Models\NbrVatConfigModel;
use Modules\Production\App\Models\ProductionConfig;
use Modules\Utility\App\Models\SettingModel;

class DomainModel extends Model
{
    use HasFactory, Sluggable;

    protected $table = 'dom_domain';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'short_name',
        'unique_code',
        'slug',
        'business_model_id',
        'modules',
        'address',
        'alternative_mobile',
        'license_no',
        'company_name',
    ];

    public function businessModel(): BelongsTo
    {
        return $this->belongsTo(SettingModel::class, 'business_model_id', 'id');
    }

    public function inventoryConfig()
    {
        return $this->hasOne(ConfigModel::class, 'domain_id', 'id');
    }

    public function hospitalConfig()
    {
        return $this->hasOne(HospitalConfigModel::class, 'domain_id', 'id');
    }

    public function productionConfig()
    {
        return $this->hasOne(ProductionConfig::class, 'domain_id', 'id');
    }

    public function accountConfig()
    {
        return $this->hasOne(AccountingModel::class, 'domain_id', 'id');
    }

    public function gstConfig()
    {
        return $this->hasOne(NbrVatConfigModel::class, 'domain_id', 'id');
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public static function getDomainsForBranch($exceptIds)
    {
        $entities = self::select([
            'id',
            'name',
            'email',
            'mobile',
        ])
            ->whereNotIn('id', [$exceptIds]) // Exclude multiple IDs
            ->get()->toArray();

        return $entities; // Return the result
    }

    public static function getRecords($request)
    {

        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;
        $entities = self::select([
            'dom_domain.id as id',
            'company_name',
            'short_name',
            'company_name',
            'dom_domain.name',
            'email',
            'mobile',
            'unique_code',
            'license_no',
            'uti_settings.name as business_model',
            'dom_domain.created_at'
        ])->leftjoin('uti_settings', 'uti_settings.id', '=', 'dom_domain.business_model_id');

        $entities = $entities->where('dom_domain.id', '!=', 1);
        if (isset($request['term']) && !empty($request['term'])) {
            $entities = $entities->whereAny(['name', 'email', 'mobile'], 'LIKE', '%' . $request['term'] . '%');
        }

        if (isset($request['name']) && !empty($request['name'])) {
            $entities = $entities->where('name', $request['name']);
        }

        if (isset($request['mobile']) && !empty($request['mobile'])) {
            $entities = $entities->where('mobile', $request['mobile']);
        }

        if (isset($request['email']) && !empty($request['email'])) {
            $entities = $entities->where('email', $request['email']);
        }

        $total = $entities->count('dom_domain.id');
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('dom_domain.id', 'DESC')
            ->get();

        $data = array('count' => $total, 'entities' => $entities);
        return $data;

    }

    public static function domainHospitalConfig($domain)
    {
        return self::with(['accountConfig',
            'accountConfig.capital_investment','accountConfig.account_cash','accountConfig.account_bank','accountConfig.account_mobile','accountConfig.account_user','accountConfig.account_vendor','accountConfig.account_customer','accountConfig.account_product_group','accountConfig.account_category',
            'accountConfig.voucher_stock_opening','accountConfig.voucher_purchase','accountConfig.voucher_sales','accountConfig.voucher_purchase_return','accountConfig.voucher_stock_reconciliation',
            'inventoryConfig','hospitalConfig','inventoryConfig.configPurchase','inventoryConfig.configSales','inventoryConfig.configProduct','inventoryConfig.configDiscount','inventoryConfig.configVat','inventoryConfig.businessModel',
            'inventoryConfig.currency',
            'hospitalConfig.admission_fee:id,name as admission_fee_name,price as admission_fee_price',
            'hospitalConfig.opd_ticket_fee:id,name as opd_ticket_fee_name,price as opd_ticket_fee_price',
            'hospitalConfig.emergency_fee:id,name as emergency_fee_name,price as emergency_fee_price',
            'hospitalConfig.ot_fee:id,name as ot_fee_name,price as ot_fee_price',
            'hospitalConfig.shareHealth',
        ])->find($domain);
    }


    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $date = new \DateTime("now");
            $model->unique_code = self::quickRandom();
            $model->created_at = $date;
            $model->status = true;
        });

        self::updating(function ($model) {
            $date = new \DateTime("now");
            $model->unique_code = self::quickRandom();
            $model->updated_at = $date;
        });

    }

    public static function quickRandom($length = 10)
    {
        $pool = '0123456789';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }


    public static function getEntityData($id)
    {
        $data = self::select(['dom_global_option.id as global_id', 'inv_config.id as config_id', 'users.id as user_id'])
            ->join('dom_global_option', 'dom_global_option.id', '=', 'users.domain_id')
            ->join('inv_config', 'inv_config.domain_id', '=', 'dom_global_option.id')
            ->where('users.id', $id)->first();
        return $data;
    }

    public static function getDomainConfigData($id)
    {
        $data = self::select(['dom_domain.id as global_id', 'inv_config.id as config_id', 'inv_config.id as inv_config', 'acc_config.id as acc_config', 'pro_config.id as pro_config', 'nbr_config.id as nbr_config'])
            ->leftjoin('inv_config', 'inv_config.domain_id', '=', 'dom_domain.id')
            ->leftjoin('inv_config_product', 'inv_config_product.config_id', '=', 'inv_config.id')
            ->leftjoin('inv_config_purchase', 'inv_config_purchase.config_id', '=', 'inv_config.id')
            ->leftjoin('inv_config_sales', 'inv_config_sales.config_id', '=', 'inv_config.id')
            ->leftjoin('inv_config_discount', 'inv_config_discount.config_id', '=', 'inv_config.id')
            ->leftjoin('inv_config_vat', 'inv_config_vat.config_id', '=', 'inv_config.id')
            ->leftjoin('acc_config', 'acc_config.domain_id', '=', 'dom_domain.id')
            ->leftjoin('pro_config', 'pro_config.domain_id', '=', 'dom_domain.id')
            ->leftjoin('nbr_config', 'nbr_config.domain_id', '=', 'dom_domain.id')
            ->where('dom_domain.id', $id)->first();
        return $data;
    }

    public static function getSubDomain($request)
    {

        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;
        $entities = self::select([
            'dom_domain.id as id',
            'dom_domain.company_name',
            'dom_domain.name',
            'dom_domain.short_name',
            'dom_domain.email',
            'dom_domain.mobile',
            'dom_domain.unique_code',
            'dom_domain.created_at',
            'dom_sub_domain.domain_type',
            'dom_sub_domain.percent_mode',
            'dom_sub_domain.bonus_percent',
            'dom_sub_domain.sales_target_amount',
            'dom_sub_domain.mrp_percent',
            'dom_sub_domain.purchase_percent',
            'uti_settings.name as business_model',
            'dom_sub_domain.status',
        ])->leftjoin('dom_sub_domain', 'dom_sub_domain.sub_domain_id', '=', 'dom_domain.id')
            ->leftjoin('uti_settings', 'uti_settings.id', '=', 'dom_domain.business_model_id');

        $entities = $entities->where('dom_domain.id', '!=', 1);
        if (isset($request['term']) && !empty($request['term'])) {
            $entities = $entities->whereAny(['dom_domain.name', 'dom_domain.email', 'dom_domain.mobile'], 'LIKE', '%' . $request['term'] . '%');
        }

        if (isset($request['name']) && !empty($request['name'])) {
            $entities = $entities->where('dom_domain.name', $request['name']);
        }

        if (isset($request['mobile']) && !empty($request['mobile'])) {
            $entities = $entities->where('dom_domain.mobile', $request['mobile']);
        }

        if (isset($request['email']) && !empty($request['email'])) {
            $entities = $entities->where('dom_domain.email', $request['email']);
        }

        $total = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('dom_sub_domain.id', 'DESC')
            ->get();

        $data = array('count' => $total, 'entities' => $entities);
        return $data;

    }


}
