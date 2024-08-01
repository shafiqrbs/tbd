<?php

namespace Modules\Production\App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Entities\Product;
use Modules\Inventory\App\Models\ProductModel;


class ProductionValueAdded extends Model
{
    use HasFactory;

    protected $table = 'pro_value_added';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['production_item_id','production_item_amendment_id','value_added_id','amount'];


    public static function getValueAddedWithInputGenerate($pro_item_id)
    {
        $getValueAdded = self::where('pro_value_added.production_item_id', '=', $pro_item_id)
            ->join('pro_setting','pro_setting.id','=','pro_value_added.value_added_id')
            ->join('pro_setting_type','pro_setting_type.id','=','pro_setting.setting_type_id')
            ->select([
                'pro_value_added.id',
                'pro_value_added.value_added_id',
                'pro_value_added.amount',
                'pro_setting.name',
                'pro_setting.slug',
                'pro_setting_type.name as type_name',
            ])
            ->get()->toArray();

        $data = [];
        if (sizeof($getValueAdded)>0) {
            foreach ($getValueAdded as $item) {
                $data[$item['type_name']][] = $item;
            }
        }
        return $data;
    }
}
