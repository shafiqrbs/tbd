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

        $entity = self::where([
                    ['pro_item.status', '=', 1],
                    ['pro_item.is_delete', '=', 0]
                ])
            ->whereIN('inv_setting.slug',['raw-materials','post-production','mid-production','pre-production'])
            ->join('inv_stock','inv_stock.id','=','pro_item.item_id')
            ->join('inv_product','inv_product.id','=','inv_stock.product_id')
            ->join('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('uti_product_unit','uti_product_unit.id','=','inv_product.unit_id')
            ->join('inv_setting','inv_setting.id','=','inv_product.product_type_id')
            ->select([
                'pro_item.id',
                'inv_product.name as product_name',
                'uti_product_unit.name as unit_name',
                'pro_item.waste_amount',
                DB::raw('DATE_FORMAT(pro_item.license_date, "%d-%M-%Y") as license_date'),
                DB::raw('DATE_FORMAT(pro_item.initiate_date, "%d-%M-%Y") as initiate_date'),
                'pro_item.quantity',
                'pro_item.waste_percent',
                'pro_item.waste_amount',
                'pro_item.material_amount',
                'pro_item.material_quantity',
                'pro_item.value_added_amount',
                'pro_item.reminig_quantity',
                'inv_setting.slug as product_type_slug',
                'inv_category.name as category_name',
                'pro_item.status',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $entity = $entity->whereAny(['inv_product.name','uti_product_unit.name','inv_setting.slug'],'LIKE','%'.trim($request['term']).'%');
        }

        if (isset($request['product_name']) && !empty($request['product_name'])){
            $entity = $entity->where('inv_product.name','LIKE','%'.trim($request['product_name']));
        }

        /*if (isset($request['setting_type_id']) && !empty($request['setting_type_id'])){
            $entity = $entity->where('pro_setting.setting_type_id',$request['setting_type_id']);
        }*/

        $total  = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('pro_item.id','DESC')
            ->get();

        $data = array('count'=>$total,'entities' => $entities);
        return $data;
    }


}
