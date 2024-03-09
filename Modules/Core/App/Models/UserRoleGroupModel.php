<?php

namespace Modules\Core\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;


class UserRoleGroupModel extends Model
{
    use HasFactory;

    protected $table = 'cor_user_role_group';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'permissions',
    ];

    public static function getRecords($request){
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;
        $entity = self::
        select([
            'id',
            'name',
            'permissions',
            'created_at'
        ]);
        if (isset($request['term']) && !empty($request['term'])){
            $entity = $entity->where('name','LIKE','%'.$request['term'].'%');
        }
        $count  = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')->get();

        return $data = array('entities'=>$entities,'count'=>$count);
    }




}
