<?php

namespace Modules\Core\App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class SettingModel extends Model
{

    protected $table = 'cor_setting';

    public static function getSettingDropdown($type,$domain, $status = 1)
    {
        $entity = self::join('cor_setting_type as cst','cst.id','=','cor_setting.setting_type_id')
            ->select(['cor_setting.name','cor_setting.slug','cor_setting.id'])
            ->where([
                ['cst.slug', $type],
                ['cor_setting.domain_id', $domain],
                ['cor_setting.status', $status]
            ])
            ->orderBy('cor_setting.name','ASC')
            ->get();

        return $entity;
    }

}
