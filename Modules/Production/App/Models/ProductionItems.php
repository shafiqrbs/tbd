<?php

namespace Modules\Production\App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Entities\Product;
use Modules\Inventory\App\Models\ProductModel;


class ProductionItems extends Model
{
    use HasFactory,Sluggable;

    protected $table = 'pro_item';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [

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

    public function setting_type(): BelongsTo
    {
        return $this->belongsTo(SettingTypeModel::class);
    }

    public static function getRecords($request,$domain){

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;

        $entity = ProductModel::where(['inv_product.status' => 1])
            ->whereIN('uti_settings.slug',['raw-materials','post-production','mid-production','mid-production'])
            ->join('inv_category','inv_category.id','=','inv_product.category_id')
            ->join('uti_product_unit','uti_product_unit.id','=','inv_product.unit_id')
            ->join('uti_settings','uti_settings.id','=','inv_product.product_type_id')
            ->select([
                'inv_product.id',
                'inv_product.category_id',
                'inv_category.name as category_name',
                'inv_product.unit_id',
                'uti_product_unit.name as unit_name',
                'inv_product.name as product_name',
                'inv_product.purchase_price as product_purchase_price',
                'inv_product.sales_price as product_sales_price',
                'uti_settings.name as product_type_name',

//                'pro_setting.slug',
//                'pro_setting.status',
//                DB::raw('DATE_FORMAT(pro_setting.created_at, "%d-%M-%Y") as created'),
//                'pro_setting_type.name as setting_type_name',
//                'pro_setting_type.slug as setting_type_slug',
            ]);

        /*if (isset($request['term']) && !empty($request['term'])){
            $entity = $entity->whereAny(['pro_setting.name','pro_setting.slug','pro_setting_type.slug'],'LIKE','%'.trim($request['term']).'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $entity = $entity->where('pro_setting.name','LIKE','%'.trim($request['name']));
        }

        if (isset($request['setting_type_id']) && !empty($request['setting_type_id'])){
            $entity = $entity->where('pro_setting.setting_type_id',$request['setting_type_id']);
        }*/

        $total  = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('inv_product.id','DESC')
            ->get();

        $data = array('count'=>$total,'entities' => $entities);
        return $data;


    }


}
