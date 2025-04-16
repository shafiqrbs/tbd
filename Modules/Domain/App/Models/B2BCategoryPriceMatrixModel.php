<?php

namespace Modules\Domain\App\Models;
use Illuminate\Database\Eloquent\Model;

class B2BCategoryPriceMatrixModel extends Model
{
    protected $table = 'inv_b2b_category_price_matrix';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'sub_domain_id',
        'domain_category_id',
        'sub_domain_category_id',
        'created_by_id',
        'percent_mode',
        'status',
        'notes',
        'process',
        'sales_target_amount',
        'bonus_percent',
        'purchase_percent',
        'mrp_percent',
        'not_process',
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


    public static function getB2BDomainCategory($domain)
    {

        $categories = self::where('inv_b2b_category_price_matrix.config_id',$domain)
            ->join('inv_category as c','c.id','=','inv_b2b_category_price_matrix.sub_domain_category_id')
            ->join('inv_category as p','p.id','=','c.parent')
            ->select([
                'inv_b2b_category_price_matrix.id',
                'inv_b2b_category_price_matrix.percent_mode',
                'inv_b2b_category_price_matrix.price_percent',
                'inv_b2b_category_price_matrix.sales_price_percent',
                'inv_b2b_category_price_matrix.not_process',
                'c.id',
                'c.name',
                'c.slug',
                'c.status',
                'p.name as parent_name'
            ]);
        $entities = $categories->orderBy('c.name','DESC')->get();
        return $entities;
    }

}
