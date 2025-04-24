<?php

namespace Modules\Accounting\App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class AccountingModel extends Model
{
    use HasFactory;

    protected $table = 'acc_config';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
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

        $table = 'acc_config';
        $columnsToExclude = [
            'id',
            'domain_id',
            'financial_start_date',
            'financial_end_date',
            'created_at',
            'updated_at'
        ];

        $booleanFields = [];

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
