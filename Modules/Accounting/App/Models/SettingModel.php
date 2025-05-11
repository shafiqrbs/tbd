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
        'is_private',
        'status'
    ];

    public function setting_type(): BelongsTo
    {
        return $this->belongsTo(SettingTypeModel::class);
    }


    public static function getSettingDropdown($dropdownType)
    {
        return DB::table('acc_setting')
            ->join('acc_setting_type','acc_setting_type.id','=','acc_setting.setting_type_id')
            ->select([
                'acc_setting.id',
                'acc_setting.name',
                'acc_setting.slug',
                'acc_setting_type.name as type_name',
            ])
            ->where([
                ['acc_setting_type.slug',$dropdownType],
                ['acc_setting_type.status','1'],
                ['acc_setting.status','1'],
            ])
            ->get();
    }

    public static function getEntityDropdown($dropdownType)
    {
        return DB::table('acc_setting')
            ->join('acc_setting_type','acc_setting_type.id','=','acc_setting.setting_type_id')
            ->select([
                'acc_setting.id',
                'acc_setting.name',
                'acc_setting.slug',
                'acc_setting_type.name as type_name',
                'acc_setting_type.slug as type_slug',
            ])
            ->where([
                ['acc_setting_type.slug',$dropdownType],
                ['acc_setting_type.status','1'],
                ['acc_setting.status','1'],
            ])
            ->get();
    }

    public static function getRecords($request, $domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 1;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;
        $entity = self:: where('acc_setting.config_id', $domain['acc_config_id'])
            ->join('acc_setting_type','acc_setting_type.id','=','acc_setting.setting_type_id')
            ->select([
                'acc_setting.id',
                'acc_setting.name',
                'acc_setting.slug',
                'acc_setting.is_private',
                'acc_setting_type.name as type_name',
                'acc_setting_type.slug as type_slug',
            ])
            ->orderBy('acc_setting.name','ASC');

        if (isset($request['term']) && !empty($request['term'])) {
            $entity = $entity->whereAny(
                ['acc_setting.name', 'acc_setting.slug'], 'LIKE', '%' . $request['term'] . '%');
        }
        if (isset($request['type']) && !empty($request['type'])) {
            $entity = $entity->where(
                ['acc_setting.setting_type_id'=>$request['type']]);
        }
        $total = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('acc_setting.id', 'DESC')
            ->get();

        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }




}
