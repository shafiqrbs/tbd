<?php

namespace Modules\Domain\App\Models;
use Illuminate\Database\Eloquent\Model;

class SubDomainModel extends Model
{
    protected $table = 'dom_sub_domain';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'domain_id',
        'sub_domain_id',
        'status',
        'domain_type'
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


    public static function getB2BDomain($domain)
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

    public static function getB2BDomainSetting($domain)
    {

        $entities = self::select([
            'd.id as id',
            'd.name',
            'd.email',
            'd.mobile',
            'dom_sub_domain.price_percent', 'dom_sub_domain.sales_price_percent', 'dom_sub_domain.bonus_percent', 'dom_sub_domain.sales_target_amount','dom_sub_domain.percent_mode','dom_sub_domain.status'
        ])->join('dom_domain as d','d.id','=','dom_sub_domain.sub_domain_id')
            ->where('dom_sub_domain.sub_domain_id', [$domain]) // Exclude multiple IDs
            ->get()->toArray();
        return $entities; // Return the result
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
