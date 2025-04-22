<?php

namespace Modules\Inventory\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Domain\App\Models\DomainModel;
use Modules\Utility\App\Entities\Setting;
use Modules\Utility\App\Models\CurrencyModel;
use Modules\Utility\App\Models\SettingModel;


class ConfigSalesModel extends Model
{
    use HasFactory;

    protected $table = 'inv_config_sales';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'search_by_vendor',
        'search_by_warehouse',
        'search_by_product_nature',
        'search_by_category',
        'show_product',
        'is_zero_receive_allow',
        'due_sales_without_customer',
        'zero_stock',
        'is_sales_auto_approved',
        'is_measurement_enable',
        'is_multi_price',
        'item_sales_percent',
        'discount_with_customer',
        'default_customer_group_id'
    ];


    public function config()
    {
        return $this->belongsTo(ConfigModel::class, 'config_id', 'id');
    }



}
