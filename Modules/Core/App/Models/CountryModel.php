<?php

namespace Modules\Core\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;


class CountryModel extends Model
{
    use HasFactory;

    protected $table = 'cor_countries';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
    ];

    public static function getCountryDropdown(){
        return self::
            select([
                'id',
                'name',
                'code',
                'phonecode',
                'numcode',
                'iso',
                'nicename'
            ])
            ->orderBy('name', 'ASC')
            ->get();
    }

}
