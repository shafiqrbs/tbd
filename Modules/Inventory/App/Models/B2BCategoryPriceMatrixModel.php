<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class B2BCategoryPriceMatrixModel extends Model
{
    use HasFactory;

    protected $table = 'inv_b2b_category_price_matrix';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'category_id',
        'status',
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
