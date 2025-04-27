<?php

namespace Modules\Production\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class ProductionConfig extends Model
{
    use HasFactory;

    protected $table = 'pro_config';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $fillable = [
        'domain_id',
        'production_procedure_id',
        'consumption_method_id',
        'is_warehouse',
        'issue_with_warehouse',
        'issue_by_production_batch',
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function resetConfig($id){

        $table = 'pro_config';
        $columnsToExclude = [
            'id',
            'domain_id',
            'created_at',
            'updated_at'
        ];

        $booleanFields = [
            'is_measurement' => false,
            'is_warehouse' => false,
            'issue_with_warehouse' => false,
            'issue_by_production_batch' => false,
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
