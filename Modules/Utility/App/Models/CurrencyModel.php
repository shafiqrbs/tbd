<?php

namespace Modules\Utility\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Models\SettingTypeModel;

class CurrencyModel extends Model
{
    use HasFactory;

    protected $table = 'uti_currencies';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'code',
        'symbol',
        'status'
    ];



    public static function getEntityDropdown()
    {
        return DB::table('uti_currencies')
            ->select([
                'id',
                'name',
                'symbol',
                'code'
            ])
            ->get();
    }

}
