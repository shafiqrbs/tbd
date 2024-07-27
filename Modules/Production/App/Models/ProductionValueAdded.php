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
}
