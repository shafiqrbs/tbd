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


class ConfigPurchaseModel extends Model
{
    use HasFactory;

    protected $table = 'inv_config_purchase';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'default_vendor_group_id',
        'search_by_vendor',
        'search_by_warehouse',
        'search_by_product_nature',
        'search_by_category',
        'show_product',
        'is_measurement_enable',
        'is_purchase_auto_approved'
    ];


    public function config()
    {
        return $this->belongsTo(ConfigModel::class, 'config_id', 'id');
    }



}
