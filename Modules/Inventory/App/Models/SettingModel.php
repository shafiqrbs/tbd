<?php

namespace Modules\Inventory\App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class SettingModel extends Model
{
    use Sluggable;

    protected $table = 'inv_setting';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'setting_type_id',
        'config_id',
        'name',
        'slug',
        'is_production',
        'parent_slug',
        'setting_id',
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
            $model->status = false;
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

    public static function getConfigID($globalID)
    {
        return DB::table('inv_config')->where('domain_id', $globalID)->select('id')->first()->id;
    }



    public static function getRecords($request,$domain){

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;

        $entity = self::where('inv_config.domain_id',$domain['global_id'])
            ->join('inv_setting_type','inv_setting_type.id','=','inv_setting.setting_type_id')
            ->join('inv_config','inv_config.id','=','inv_setting.config_id')
            ->select([
                'inv_setting.id',
                'inv_setting.name',
                'inv_setting.slug',
                'inv_setting.status',
                DB::raw('DATE_FORMAT(inv_setting.created_at, "%d-%M-%Y") as created'),
                'inv_setting_type.name as setting_type_name',
                'inv_setting_type.slug as setting_type_slug',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $entity = $entity->whereAny(['inv_setting.name','inv_setting.slug','inv_setting_type.slug'],'LIKE','%'.trim($request['term']).'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $entity = $entity->where('inv_setting.name','LIKE','%'.trim($request['name']));
        }

        if (isset($request['setting_type_id']) && !empty($request['setting_type_id'])){
            $entity = $entity->where('inv_setting.setting_type_id',$request['setting_type_id']);
        }

        $total  = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')
            ->get();

        $data = array('count'=>$total,'entities' => $entities);
        return $data;


    }

    public static function getMeasurementInputGenerate($domain)
    {
        $entity = self::where('inv_config.domain_id', $domain)
            ->whereIN('inv_setting_type.slug',['value-added','utility'])
            ->join('inv_setting_type', 'inv_setting_type.id', '=', 'inv_setting.setting_type_id')
            ->join('inv_config', 'inv_config.id', '=', 'inv_setting.config_id')
            ->select([
                'inv_setting.id',
                'inv_setting.name',
                'inv_setting.slug',
                'inv_setting_type.name as setting_type_name',
                'inv_setting_type.slug as setting_type_slug',
            ])
            ->get()
            ->toArray();

        $data = [];
        if (sizeof($entity)>0) {
            foreach ($entity as $item) {
                $data[$item['setting_type_name']][] = $item;
            }
        }

        $slugArray = array_map(function ($entity) {
            return $entity['slug'];
        }, $entity);
        return ['datakey'=>$slugArray,'field'=>$data];
    }

    public static function getSettingDropdown($domain,$dropdownType)
    {

        return DB::table('inv_setting')
            ->join('uti_settings','uti_settings.id','=','inv_setting.setting_id')
            ->join('uti_setting_types','uti_setting_types.id','=','uti_settings.setting_type_id')
            ->select([
                'inv_setting.id',
                'inv_setting.name',
                'inv_setting.slug',
            ])
            ->where([
                ['inv_setting.config_id',$domain],
                ['uti_setting_types.slug',$dropdownType],
                ['uti_settings.status','1'],
                ['inv_setting.status','1'],
            ])
            ->get();
    }

    public static function getEntityDropdown($dropdownType)
    {
        return DB::table('inv_setting')
            ->join('inv_setting_type','inv_setting_type.id','=','inv_setting.setting_type_id')
            ->select([
                'inv_setting.id',
                'inv_setting.name',
                'inv_setting.slug',
                'inv_setting_type.name as type_name',
                'inv_setting_type.slug as type_slug',
            ])
            ->where([
                ['inv_setting_type.slug',$dropdownType],
                ['inv_setting_type.status','1'],
                ['inv_setting.status','1'],
            ])
            ->get();
    }

    public static function getProductTypeOrCreate($parentProductType, $configId)
    {
        $findParentProductType = self::find($parentProductType);
        if (!$findParentProductType) {
            return null; // or throw an exception
        }

        // Find child product type under same setting + config
        $childProductType = self::where('setting_id', $findParentProductType->setting_id)
            ->where('config_id', $configId)
            ->first();

        // If not found, create both possible product types for this config
        if (!$childProductType) {
            $stockableSettingId = \Modules\Utility\App\Models\SettingModel::where('slug', 'stockable')->value('id');
            $rawMaterialSettingId = \Modules\Utility\App\Models\SettingModel::where('slug', 'raw-materials')->value('id');

            // Create if not exists (per config)
            self::updateOrCreate(
                [
                    'setting_id' => $stockableSettingId,
                    'config_id'  => $configId,
                    'slug'       => 'stockable',
                    'parent_slug'=> 'product-type'
                ],
                [
                    'status' => 1,
                    'name'   => 'Stockable'
                ]
            );

            self::updateOrCreate(
                [
                    'setting_id' => $rawMaterialSettingId,
                    'config_id'  => $configId,
                    'slug'       => 'raw-materials',
                    'parent_slug'=> 'product-type'
                ],
                [
                    'status' => 1,
                    'name'   => 'Raw Materials'
                ]
            );

            // Default return stockable
            return self::where('slug', 'stockable')
                ->where('config_id', $configId)
                ->value('id');
        }

        // Return the found child product type’s id
        return $childProductType->id;
    }




}
