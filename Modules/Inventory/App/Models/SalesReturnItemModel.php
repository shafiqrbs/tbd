<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnItemModel extends Model
{

    protected $table = 'inv_sales_return_item';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $fillable = ['sales_item_id','sales_return_id','stock_item_id','warehouse_id','purchase_return_item_id','item_name','uom','request_quantity','price','sub_total','status','stock_entry_quantity','damage_entry_quantity','quantity'];

}
