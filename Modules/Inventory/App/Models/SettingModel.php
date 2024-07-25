<?php

namespace Modules\Inventory\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Utility\App\Entities\Setting;
use Modules\Utility\App\Models\SettingModel as SettingUtilityModel;
use Modules\Utility\App\Models\SettingTypeModel;


class SettingModel extends Model
{
    use HasFactory;

    protected $table = 'inv_setting';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'setting_type',
        'name',
        'status'
    ];


    public function setting():BelongsTo
    {
        return $this->belongsTo(Setting::class);
    }


    public static function getSettingDropdown($domain,$dropdownType)
    {
        return DB::table('inv_setting')
            ->join('uti_settings','uti_settings.id','=','inv_setting.setting_id')
            ->join('uti_setting_types','uti_setting_types.id','=','uti_settings.setting_type_id')
            ->select([
                'uti_settings.id',
                'uti_settings.name',
                'uti_settings.slug',
                'uti_setting_types.name as type_name',
            ])
            ->where([
                ['uti_setting_types.slug',$dropdownType],
                ['uti_setting_types.status','1'],
                ['inv_setting.config_id',$domain['config_id']],
                ['uti_settings.status','1'],
            ])
            ->get();
    }



}
