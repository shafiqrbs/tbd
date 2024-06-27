<?php

namespace Modules\Accounting\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class SettingModel extends Model
{
    use HasFactory;

    protected $table = 'acc_setting';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'setting_type',
        'name',
        'status'
    ];

    public function setting_type(): BelongsTo
    {
        return $this->belongsTo(SettingTypeModel::class);
    }


    public static function getSettingDropdown($dropdownType)
    {
        return DB::table('uti_settings')
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
                ['uti_settings.status','1'],
            ])
            ->get();
    }

    public static function getEntityDropdown($dropdownType)
    {
        return DB::table('uti_settings')
            ->join('uti_setting_types','uti_setting_types.id','=','uti_settings.setting_type_id')
            ->select([
                'uti_settings.id',
                'uti_settings.name',
                'uti_settings.slug',
                'uti_setting_types.name as type_name',
                'uti_setting_types.slug as type_slug',
            ])
            ->where([
                ['uti_setting_types.slug',$dropdownType],
                ['uti_setting_types.status','1'],
                ['uti_settings.status','1'],
            ])
            ->get();
    }



}
