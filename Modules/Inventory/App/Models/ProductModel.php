<?php

namespace Modules\Inventory\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductModel extends Model
{
    use HasFactory,Sluggable;

    protected $table = 'inv_product';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'category_id',
        'unit_id'
    /*name
    quantity
    content
    price
    code
    status
    path
    product_type
    sorting
    production_type
    commission
    slug
    tlo_price
    opening_approve
    brand_id
    config_id
    wear_house_id
    business_particular_type_id
    rack_no_id
    opening_quantity
    adjustment_quantity
    min_quantity
    reorder_quantity
    transfer_quantity
    stock_in
    purchase_quantity
    sales_quantity
    remaining_quantity
    purchase_return_quantity
    sales_return_quantity
    damage_quantity
    bonus_quantity
    bonus_adjustment
    return_bonus_quantity
    bonus_purchase_quantity
    bonus_sales_quantity
    purchase_price
    avg_purchase_price
    production_sales_price
    sales_price
    discount_price
    minimum_price
    particular_code
    sku
    barcode
    model_no
    created_at
    updated_at
    size_id
    color_id
    alternative_name*/
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

    public static function getCategoryGroupDropdown($domain)
    {
        $query = self::select(['name', 'slug', 'id'])
            ->where([['status', 1],['config_id', $domain['config_id']]]);
        $query->whereNull('parent');
        return $query->get();
    }

    public static function getCategoryDropdown($domain)
    {
        $query = self::select(['name', 'slug', 'id'])
            ->where([['status', 1],['config_id', $domain['config_id']]]);
        $query->whereNotNull('parent');
        return $query->get();
    }


    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $categories = self::where('inv_category.config_id',$domain['config_id'])
            ->leftjoin('inv_category as p','p.id','=','inv_category.parent')
            ->select([
                'inv_category.id',
                'inv_category.name',
                'inv_category.slug',
                'inv_category.parent as parent_id',
            ]);

        if (isset($request['type']) && $request['type'] === 'category'){
            $categories = $categories->addSelect([
                'p.name as parent_name'
            ]);
        }

        if (isset($request['term']) && !empty($request['term'])){
            $categories = $categories->whereAny(['inv_category.name','inv_category.slug'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['type']) && $request['type'] === 'category'){
            $categories = $categories->whereNotNull('inv_category.parent');
        }
        if (isset($request['type']) && $request['type'] === 'parent' ){
            $categories = $categories->whereNull('inv_category.parent');
        }


        $total  = $categories->count();
        $entities = $categories->skip($skip)
            ->take($perPage)
            ->orderBy('inv_category.id','DESC')
            ->get();

        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }

    public static function getCategoryIsParent($id)
    {
        $data = self::find($id);
        if (!$data->parent){
            return true;
        }else{
            return false;
        }
    }

    public static function getCategoryIsDeletable($id)
    {
        $data = self::where('parent',$id)->get();
        if (sizeof($data)>0){
            return false;
        }else{
            return true;
        }
    }
}
