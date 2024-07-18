<?php

namespace Modules\Production\App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class SettingModel extends Model
{
    use HasFactory,Sluggable;

    protected $table = 'pro_setting';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'setting_type_id',
        'name',
        'slug',
        'status'
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }


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

    public function setting_type(): BelongsTo
    {
        return $this->belongsTo(SettingTypeModel::class);
    }

    public static function getRecords($request,$domain){

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;

        $entity = self::join('pro_setting_type','pro_setting_type.id','=','pro_setting.setting_type_id')
            ->select([
                'pro_setting.id',
                'pro_setting.name',
                'pro_setting.slug',
                'pro_setting.status',
                DB::raw('DATE_FORMAT(pro_setting.created_at, "%d-%M-%Y") as created'),
                'pro_setting_type.name as setting_type_name',
                'pro_setting_type.slug as setting_type_slug',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $entity = $entity->whereAny(['pro_setting.name','pro_setting.slug','pro_setting_type.slug'],'LIKE','%'.trim($request['term']).'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $entity = $entity->where('pro_setting.name','LIKE','%'.trim($request['name']));
        }

        if (isset($request['setting_type_id']) && !empty($request['setting_type_id'])){
            $entity = $entity->where('pro_setting.setting_type_id',$request['setting_type_id']);
        }

        $total  = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')
            ->get();

        $data = array('count'=>$total,'entities' => $entities);
        return $data;


    }

    public static function getSettingDropdown($dropdownType)
    {
        return DB::table('pro_setting')
            ->join('pro_setting_type','pro_setting_type.id','=','pro_setting.setting_type_id')
            ->select([
                'pro_setting.id',
                'pro_setting.name',
                'pro_setting.slug',
                'pro_setting_type.name as type_name',
            ])
            ->where([
                ['pro_setting_type.slug',$dropdownType],
                ['pro_setting_type.status','1'],
                ['pro_setting.status','1'],
            ])
            ->get();
    }

    public static function getEntityDropdown($dropdownType)
    {
        return DB::table('pro_setting')
            ->join('pro_setting_type','pro_setting_type.id','=','pro_setting.setting_type_id')
            ->select([
                'pro_setting.id',
                'pro_setting.name',
                'pro_setting.slug',
                'pro_setting_type.name as type_name',
                'pro_setting_type.slug as type_slug',
            ])
            ->where([
                ['pro_setting_type.slug',$dropdownType],
                ['pro_setting_type.status','1'],
                ['pro_setting.status','1'],
            ])
            ->get();
    }



}
