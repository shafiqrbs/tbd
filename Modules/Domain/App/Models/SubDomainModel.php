<?php

namespace Modules\Domain\App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Models\SalesItemModel;

class SubDomainModel extends Model
{
    protected $table = 'dom_sub_domain';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'domain_id',
        'sub_domain_id',
        'status',
        'domain_type',
        'percent_mode',
        'bonus_percent',
        'purchase_percent',
        'sales_target_amount',
        'mrp_percent',
        'vendor_id',
        'customer_id',
    ];

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

    public function subDomainCategory()
    {
        return $this->hasMany(B2BCategoryPriceMatrixModel::class, 'sub_domain_id');
    }


    public static function getB2BDomain($domain)
    {
        $entities = self::select([
            'dom_sub_domain.id as id',
            'dom_sub_domain.domain_id',
            'dom_sub_domain.sub_domain_id',
            'd.name',
            'd.email',
            'd.mobile',
            'dom_sub_domain.mrp_percent',
            'dom_sub_domain.purchase_percent',
            'dom_sub_domain.bonus_percent',
            'dom_sub_domain.sales_target_amount',
            'dom_sub_domain.percent_mode',
            'dom_sub_domain.status'
        ])->join('dom_domain as d','d.id','=','dom_sub_domain.sub_domain_id')
            ->where('dom_sub_domain.domain_id', [$domain])
            ->where('dom_sub_domain.status', 1)
            ->get()->toArray();
        return $entities;
    }

    public static function getB2BDomainSetting($domain)
    {
        $data = self::select([
            'dom_sub_domain.id',
            'dom_sub_domain.domain_id',
            'dom_sub_domain.sub_domain_id',
            'dom_sub_domain.status',
            'dom_sub_domain.domain_type',
            'dom_sub_domain.percent_mode',
            'dom_sub_domain.bonus_percent',
            'dom_sub_domain.sales_target_amount',
            'dom_sub_domain.mrp_percent',
            'dom_sub_domain.purchase_percent'
        ])
            ->with(['subDomainCategory' => function ($query) {
                $query->select([
                    'inv_b2b_category_price_matrix.id',
                    'inv_b2b_category_price_matrix.config_id',
                    'inv_b2b_category_price_matrix.sub_domain_id',
                    'inv_b2b_category_price_matrix.domain_category_id',
                    'inv_b2b_category_price_matrix.sub_domain_category_id',
                    'inv_b2b_category_price_matrix.percent_mode',
                    'inv_b2b_category_price_matrix.status',
                    'inv_b2b_category_price_matrix.mrp_percent',
                    'inv_b2b_category_price_matrix.purchase_percent',
                    'inv_b2b_category_price_matrix.bonus_percent',
                    'inv_b2b_category_price_matrix.sales_target_amount',
                    'inv_category.name as category_name',
                    'inv_b2b_category_price_matrix.not_process',
                ])->leftjoin('inv_category','inv_category.id','=','inv_b2b_category_price_matrix.domain_category_id')
                    ->where('inv_b2b_category_price_matrix.status',1);
            }])
            ->where('dom_sub_domain.id', $domain) // Exclude multiple IDs
            ->first();
        return $data; // Return the result
    }

    public static function getB2BDomainCategory($domain)
    {
        $entities = self::select([
            'd.id as id',
            'd.name',
            'd.email',
            'd.mobile',
            'dom_sub_domain.price_percent', 'dom_sub_domain.sales_price_percent', 'dom_sub_domain.bonus_percent', 'dom_sub_domain.sales_target_amount','dom_sub_domain.percent_mode','dom_sub_domain.status'
        ])->join('dom_domain as d','d.id','=','dom_sub_domain.sub_domain_id')
            ->where('dom_sub_domain.domain_id', [$domain]) // Exclude multiple IDs
            ->get()->toArray();
        return $entities; // Return the result
    }

    public static function getB2BDomainProduct($domain)
    {
        $entities = self::select([
            'd.id as id',
            'd.name',
            'd.email',
            'd.mobile',
            'dom_sub_domain.price_percent', 'dom_sub_domain.sales_price_percent', 'dom_sub_domain.bonus_percent', 'dom_sub_domain.sales_target_amount','dom_sub_domain.percent_mode','dom_sub_domain.status'
        ])->join('dom_domain as d','d.id','=','dom_sub_domain.sub_domain_id')
            ->where('dom_sub_domain.domain_id', [$domain]) // Exclude multiple IDs
            ->get()->toArray();
        return $entities; // Return the result
    }




}
