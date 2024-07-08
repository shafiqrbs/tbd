<?php

namespace Modules\Core\App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;


class SettingModel extends Model
{

    protected $table = 'cor_setting';
    use HasFactory;
    use Sluggable;

    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'setting_type_id',
        'domain_id',
        'status'
    ];

    public function setting_type(): BelongsTo
    {
        return $this->belongsTo(SettingTypeModel::class);
    }

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            $model->updated_at = $date;
            $datetime = new \DateTime("now");
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });

    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable():array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }


    public static function getRecords($domain,$request){

        $global = $domain['global_id'];
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;
        $entities = self::join('cor_setting_type as cst','cst.id','=','cor_setting.setting_type_id')
            ->select([
                'cor_setting.id as id',
                'cor_setting.name as name',
                'cst.name as setting_name',
                'cst.id as setting_id'
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $entities = $entities->whereAny(['name'],'LIKE','%'.$request['term'].'%');
        }
        $counts  = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')
            ->get();
        $data = array('count' => $counts,'entities' => $entities);
        return $data;
    }

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
