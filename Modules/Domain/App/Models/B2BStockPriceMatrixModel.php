<?php

namespace Modules\Domain\App\Models;
use Illuminate\Database\Eloquent\Model;

class B2BStockPriceMatrixModel extends Model
{
    protected $table = 'inv_b2b_stock_price_matrix';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'sub_domain_id',
        'domain_stock_item_id',
        'sub_domain_stock_item_id',
        'mrp',
        'purchase_price',
        'sales_price',
        'status',
        'category_price_matrix_id',
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


    public static function getRecords($request,$domain)
    {

        $categories = self::where('inv_b2b_category_price_matrix.config_id',$domain['config_id'])
            ->join('inv_category as c','c.id','=','inv_b2b_category_price_matrix.sub_domain_category_item_id')
            ->join('inv_category as p','p.id','=','inv_category.parent')
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
        $entities = $categories->orderBy('inv_category.id','DESC')->get();
        return $entities;
    }

}
