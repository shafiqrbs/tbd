<?php

namespace Modules\Utility\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\SettingTypeModel;

class ProductUnitModel extends Model
{
    use HasFactory;

    protected $table = 'uti_product_unit';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'slug',
        'status'
    ];



    public static function getEntityDropdown()
    {
        return DB::table('uti_product_unit')
            ->select([
                'id',
                'name',
                'slug'
            ])
            ->where([
                ['status',1],
            ])
            ->get();
    }

}
