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


class ConfigDiscountModel extends Model
{
    use HasFactory;

    protected $table = 'inv_config_discount';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'max_discount',
        'discount_with_customer',
        'online_customer',
    ];


    public function config()
    {
        return $this->belongsTo(ConfigModel::class, 'config_id', 'id');
    }

    public static function resetConfig($id){

        $table = 'inv_config_discount';
        $columnsToExclude = [
            'id',
            'config_id',
            'created_at',
            'updated_at'
        ];

        $booleanFields = [
            'status' => false,
        ];

        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        $columnsToReset = array_diff($columns, $columnsToExclude);


        // Create SQL SET expressions
        $setStatements = [];
        foreach ($columnsToReset as $column) {
            if (array_key_exists($column, $booleanFields)) {
                // Handle boolean fields
                $value = $booleanFields[$column] ? 1 : 0;
                $setStatements[] = "'$column' = $value";
            } else {
                // Set other fields to NULL
                $setStatements[] = "'$column' = NULL";
            }
        }

        // Execute raw query with properly formatted SET statements
        DB::statement("UPDATE '$table' SET " . implode(', ', $setStatements) . " WHERE id = ?", [$id]);

    }



}
