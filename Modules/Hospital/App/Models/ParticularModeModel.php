<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Ramsey\Collection\Collection;

class ParticularModeModel extends Model
{
    use HasFactory;

    protected $table = 'hms_particular_mode';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];


    public static function getRecords($request,$domain){

        $config =  $domain['hms_config'];
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;
        $entity = self::join('hms_particular_module','hms_particular_module.id','=','hms_particular_mode.particular_module_id')
            ->select([
                'hms_particular_mode.id',
                'hms_particular_mode.name',
                'hms_particular_mode.slug',
                'hms_particular_mode.status',
                DB::raw('DATE_FORMAT(hms_particular_mode.created_at, "%d-%M-%Y") as created'),
                'hms_particular_module.name as setting_module_name',
                'hms_particular_module.slug as setting_module_slug',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $entity = $entity->whereAny(['hms_particular_mode.name','hms_particular_mode.slug','hms_particular_module.slug'],'LIKE','%'.trim($request['term']).'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $entity = $entity->where('hms_particular_mode.name','LIKE','%'.trim($request['name']));
        }

        if (isset($request['particular_module_id']) && !empty($request['particular_module_id'])){
            $entity = $entity->where('hms_particular_mode.particular_module_id',$request['particular_module_id']);
        }

        $total  = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')
            ->get();

        $data = array('count' => $total,'entities' => $entities);
        return $data;


    }

    public static function getParticularModuleDropdown($dropdownType)
    {

        return DB::table('hms_particular_mode')
            ->join('hms_particular_module','hms_particular_module.id','=','hms_particular_mode.particular_module_id')
            ->select([
                'hms_particular_mode.id',
                'hms_particular_mode.name',
                'hms_particular_mode.slug',
            ])
            ->where([
                ['hms_particular_module.slug',$dropdownType],
                ['hms_particular_mode.status',1]
            ])
            ->orderBy('hms_particular_mode.ordering')
            ->get();
    }




}
