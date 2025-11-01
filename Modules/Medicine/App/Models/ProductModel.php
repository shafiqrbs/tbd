<?php

namespace Modules\Medicine\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Inventory\App\Models\CategoryModel;
use Modules\Inventory\App\Models\ParticularModel;
use Modules\Inventory\App\Models\ProductGalleryModel;
use Modules\Inventory\App\Models\ProductMeasurementModel;
use Modules\Inventory\App\Models\SettingModel;
use Modules\Inventory\App\Models\StockItemModel;

class ProductModel extends Model
{
    use HasFactory,Sluggable;

    protected $table = 'inv_product';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'product_type_id',
        'category_id',
        'unit_id',
        'name',
        'code',
        'barcode',
        'status',
        'config_id',
        'parent_id',
        'description',
        'quantity',
        'is_private',
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
            $model->barcode = self::generatedEventListener($model)['generateId'];
            $model->code = self::generatedEventListener($model)['code'];
            $model->status = true;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function generatedEventListener($model)
    {
        $patternCodeService = app(GeneratePatternCodeService::class);
        $params = [
            'config' => $model->config_id,
            'table' => 'inv_product',
        ];
        return $patternCodeService->productMedicineCode($params);
    }

    public function category()
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function unit()
    {
        return $this->belongsTo(ParticularModel::class, 'unit_id');
    }

    public function stockItems()
    {
        return $this->hasMany(StockItemModel::class, 'product_id');
    }

    public function medicineStocks()
    {
        return $this->hasMany(MedicineStockModel::class, 'product_id');
    }

}
