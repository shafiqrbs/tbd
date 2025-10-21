<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Ramsey\Collection\Collection;

class MedicineDosageModel extends Model
{
    use HasFactory;

    protected $table = 'hms_medicine_dosage';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public static function getRecords($request,$domain){

        $config =  $domain['hms_config'];
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;
        $entity = self::where('hms_medicine_dosage.config_id',$config)
            ->select(['*']);
        if (isset($request['name']) && !empty($request['name'])){
            $entity = $entity->where('hms_medicine_dosage.name','LIKE','%'.trim($request['name']));
        }
        $total  = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('hms_medicine_dosage.ordering','ASC')
            ->get();

        $data = array('count'=>$total,'entities' => $entities);
        return $data;


    }


    public static function getMedicineDosageDropdown($domain,$mode){

        $config =  $domain['hms_config'];
        $entity = self::where('hms_medicine_dosage.config_id',$config)
            ->select('id','name','name_bn');
        $entity = $entity->where('hms_medicine_dosage.mode',$mode);
        $entities = $entity->orderBy('hms_medicine_dosage.name','ASC')->get();
        return $entities;

    }


}
