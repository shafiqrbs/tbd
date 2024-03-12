<?php

namespace Modules\Domain\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Models\SettingTypeModel;

class SettingModel extends Model
{
    use HasFactory;

    protected $table = 'dom_settings';
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


    public static function getBusinessModelDropdown($domain,$dropdownType)
    {
        return DB::table('dom_settings')
            ->join('dom_setting_types','dom_setting_types.id','=','dom_settings.setting_type_id')
            ->select([
                'dom_settings.id',
                'dom_settings.name',
                'dom_settings.slug',
                'dom_setting_types.name as type_name',
            ])
            ->where([
                ['dom_setting_types.slug',$dropdownType],
                ['dom_setting_types.status','1'],
                ['dom_settings.status','1'],
            ])
            ->get();
    }



}
