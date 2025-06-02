<?php

namespace Modules\Utility\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class BanksModel extends Model
{
    protected $table = 'uti_banks';



    public static function getEntityDropdown()
    {
        return self::select([
                'id',
                'name',
            ])
            ->get();
    }

}
