<?php

namespace Modules\Inventory\App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class ParticularModel extends Model
{
    use HasFactory,Sluggable;

    protected $table = 'inv_particular';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'particular_type_id',
        'config_id',
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

    public function particular_type(): BelongsTo
    {
        return $this->belongsTo(ParticularTypeModel::class);
    }


    public static function getRecords($request,$domain){

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;

        $entity = self::where('inv_config.domain_id',$domain['global_id'])
            ->join('inv_particular_type','inv_particular_type.id','=','inv_particular.particular_type_id')
            ->join('inv_config','inv_config.id','=','inv_particular.config_id')
            ->select([
                'inv_particular.id',
                'inv_particular.name',
                'inv_particular.slug',
                'inv_particular.status',
                DB::raw('DATE_FORMAT(inv_particular.created_at, "%d-%M-%Y") as created'),
                'inv_particular_type.name as setting_type_name',
                'inv_particular_type.slug as setting_type_slug',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $entity = $entity->whereAny(['inv_particular.name','inv_particular.slug','inv_particular_type.slug'],'LIKE','%'.trim($request['term']).'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $entity = $entity->where('inv_particular.name','LIKE','%'.trim($request['name']));
        }

        if (isset($request['particular_type_id']) && !empty($request['particular_type_id'])){
            $entity = $entity->where('inv_particular.particular_type_id',$request['particular_type_id']);
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
        return DB::table('inv_particular')
            ->join('inv_particular_type','inv_particular_type.id','=','inv_particular.particular_type_id')
            ->select([
                'inv_particular.id',
                'inv_particular.name',
                'inv_particular.slug',
                'inv_particular_type.name as type_name',
            ])
            ->where([
                ['inv_particular_type.slug',$dropdownType],
                ['inv_particular_type.status','1'],
                ['inv_particular.status','1'],
            ])
            ->get();
    }

    public static function getEntityDropdown($dropdownType)
    {
        return DB::table('inv_particular')
            ->join('inv_particular_type','inv_particular_type.id','=','inv_particular.particular_type_id')
            ->select([
                'inv_particular.id',
                'inv_particular.name',
                'inv_particular.slug',
                'inv_particular_type.name as type_name',
                'inv_particular_type.slug as type_slug',
            ])
            ->where([
                ['inv_particular_type.slug',$dropdownType],
                ['inv_particular_type.status','1'],
                ['inv_particular.status','1'],
            ])
            ->get();
    }

    public static function getProductUnitDropdown($domain,$dropdownType)
    {

        return DB::table('inv_particular')
            ->join('inv_particular_type','inv_particular_type.id','=','inv_particular.particular_type_id')
            ->select([
                'inv_particular.id',
                'inv_particular.name',
                'inv_particular.slug',
                'inv_particular_type.name as type_name',
            ])
            ->where([
                ['inv_particular_type.slug',$dropdownType],
                ['inv_particular.config_id',$domain['config_id']],
                ['inv_particular.status','1'],
            ])
            ->get();
    }



}
