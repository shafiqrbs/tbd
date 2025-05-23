<?php

namespace Modules\Inventory\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\Domain\App\Models\DomainModel;
use Modules\Utility\App\Entities\Setting;
use Modules\Utility\App\Models\CurrencyModel;
use Modules\Utility\App\Models\SettingModel;


class ConfigProductModel extends Model
{
    use HasFactory;

    protected $table = 'inv_config_product';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'is_sku',
        'is_multi_price',
        'is_measurement',
        'is_product_gallery',
        'sku_brand',
        'sku_model',
        'sku_color',
        'sku_size',
        'sku_grade',
        'barcode_print',
        'barcode_price_hide',
        'barcode_color',
        'barcode_size',
        'barcode_brand',
        'barcode_model',
        'barcode_grade',

    ];


    public function config()
    {
        return $this->belongsTo(ConfigModel::class, 'config_id', 'id');
    }

    public static function resetConfig($id){

        $table = 'inv_config_product';

        $columnsToExclude = [
            'id',
            'config_id',
            'created_at',
            'updated_at'
        ];


        $booleanFields = [
            'is_sku' => false,
            'is_multi_price' => false,
            'is_measurement' => false,
            'is_product_gallery' => false,
            'sku_brand' => false,
            'sku_model' => false,
            'sku_color' => false,
            'sku_size' => false,
            'sku_grade' => false,
            'barcode_print' => false,
            'barcode_price_hide' => false,
            'barcode_color' => false,
            'barcode_size' => false,
            'barcode_brand' => false,
            'barcode_model' => false,
            'barcode_grade' => false,
        ];

        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        $columnsToReset = array_diff($columns, $columnsToExclude);


        // Create SQL SET expressions
        $setStatements = [];
        foreach ($columnsToReset as $column) {
            if (array_key_exists($column, $booleanFields)) {
                // Handle boolean fields
                $value = $booleanFields[$column] ? 1 : 0;
                $setStatements[] = "`$column` = $value";
            } else {
                // Set other fields to NULL
                $setStatements[] = "`$column` = NULL";
            }
        }

        // Execute raw query with properly formatted SET statements
        DB::statement("UPDATE `$table` SET " . implode(', ', $setStatements) . " WHERE id = ?", [$id]);

    }



}
