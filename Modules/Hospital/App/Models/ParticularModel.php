<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Ramsey\Collection\Collection;

class ParticularModel extends Model
{
    use HasFactory;

    protected $table = 'hms_particular';
    public $timestamps = true;
    protected $guarded = ['id'];


    public static function getRecords($request,$domain){

        $config =  $domain['hms_config'];
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;
        $entity = self::where('hms_particular.config_id',$config)
            ->join('hms_particular_type','hms_particular_type.id','=','hms_particular.particular_type_id')
            ->leftJoin('inv_category','inv_category.id','=','hms_particular.category_id')
            ->select([
                'hms_particular.id',
                'hms_particular.name',
                'hms_particular.slug',
                'hms_particular.price',
                'hms_particular.status',
                'inv_category.name as category',
                DB::raw('DATE_FORMAT(hms_particular.created_at, "%d-%M-%Y") as created'),
                'hms_particular_type.name as particular_type_name',
                'hms_particular_type.slug as particular_type_slug',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $entity = $entity->whereAny(['hms_particular.name','hms_particular.slug','hms_particular_type.slug'],'LIKE','%'.trim($request['term']).'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $entity = $entity->where('hms_particular.name','LIKE','%'.trim($request['name']));
        }

        if (isset($request['particular_type_id']) && !empty($request['particular_type_id'])){
            $entity = $entity->where('hms_particular.particular_type_id',$request['particular_type_id']);
        }

        $total  = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')
            ->get();

        $data = array('count'=>$total,'entities' => $entities);
        return $data;


    }

    public static function getParticularDropdown($domain,$dropdownType)
    {
        $config = $domain['hms_config'];
        return DB::table('hms_particular')
            ->join('hms_particular_type','hms_particular_type.id','=','hms_particular.particular_type_id')
            ->join('hms_particular_master_type','hms_particular_master_type.id','=','hms_particular_type.particular_master_type_id')
            ->select([
                'hms_particular.id',
                'hms_particular.name'
            ])
            ->where([
                ['hms_particular.config_id',$config],
                ['hms_particular_master_type.slug',$dropdownType],
                ['hms_particular.status',1]
            ])
            ->orderBy('hms_particular.name')
            ->get();
    }


}
