<?php

namespace Modules\Inventory\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;


class ConfigRequsitionModel extends Model
{
    use HasFactory;

    protected $table = 'inv_config_requisition';
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

    public static function resetConfig($id){

        $table = 'inv_config_requisition';
        $columnsToExclude = [
            'id',
            'config_id',
            'created_at',
            'updated_at'
        ];

        $booleanFields = [
            'search_by_vendor' => false,
            'search_by_warehouse' => false,
            'search_by_product_nature' => false,
            'search_by_category' => false,
            'show_product' => false,
            'is_measurement_enable' => false,
            'is_purchase_auto_approved' => false
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
