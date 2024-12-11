<?php

namespace Modules\Inventory\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockItemHistoryModel extends Model
{
    use HasFactory;

    protected $table = 'inv_stock_item_history';
    public $timestamps = true;
    protected $guarded = ['id'];

}
