<?php

namespace Modules\Inventory\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;



class ConfigVatModel extends Model
{
    protected $table = 'inv_config_vat';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'config_id',
        'vat_integration',
        'vat_reg_no',
        'vat_enable',
        'ait_enable',
        'zakat_enable',
        'zakat_percent',
        'vat_percent',
        'ait_percent',
        'sd_percent',
        'sd_enable',
        'hs_code_enable',
        'sd_enable',
        'vat_mode',
    ];

    public function config()
    {
        return $this->belongsTo(ConfigModel::class, 'config_id', 'id');
    }

    public static function resetConfig($id){

        $table = 'inv_config_vat';
        $columnsToExclude = [
            'id',
            'config_id',
            'created_at',
            'updated_at'
        ];

        $booleanFields = [
            'status' => false,
            'vat_enable'=> false,
            'ait_enable'=> false,
            'zakat_enable'=> false,
            'vat_percent'=> false,
            'ait_percent'=> false,
            'sd_percent'=> false,
            'sd_enable'=> false,
            'hs_code_enable'=> false,
            'vat_integration'=> false,
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
                $setStatements[] = "'$column' = {0}";
            }
        }

        // Execute raw query with properly formatted SET statements
      //  $row = DB::statement("UPDATE '$table' SET " . implode(', ', $setStatements) . " WHERE id = ?", [$id]);

    }



}
