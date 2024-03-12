<?php

namespace Modules\Core\App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserModel extends Model
{
    use HasFactory;

    protected $table = 'users';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'username',
        'mobile',
        'email',
        'password',
        'IsDelete',
    ];

    public static function getRecords($request,$domain){

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;
        $users = self::where('global_option_id',$domain['global_id'])
        ->select([
            'id',
            'name',
            'username',
            'email',
            'mobile',
            'created_at'
        ]);

        if (isset($request['term']) && !empty($request['term'])){
            $users = $users->whereAny(['name','email','username','mobile'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $users = $users->where('name',$request['name']);
        }

        if (isset($request['mobile']) && !empty($request['mobile'])){
            $users = $users->where('mobile',$request['mobile']);
        }

        if (isset($request['email']) && !empty($request['email'])){
            $users = $users->where('email',$request['email']);
        }

        $total  = $users->count();
        $entities = $users->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')
            ->get();

        $data = array('count'=>$total,'entities' => $entities);
        return $data;


    }


    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
//            $model->unique_id = self::quickRandom();
            $model->created_at = $date;
            $model->updated_at = $date;
//            $model->created = $date;
//            $model->updated = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
//            $model->unique_id = self::quickRandom();
            $model->updated_at = $date;
//            $model->updated = $date;
        });

    }

    public static function quickRandom($length = 32)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }


    public static function getUserData($id)
    {
        $data = self::select(['dom_global_option.id as global_id','inv_config.id as config_id','users.id as user_id'])
            ->join('dom_global_option','dom_global_option.id','=','users.global_option_id')
            ->join('inv_config','inv_config.global_option_id','=','dom_global_option.id')
            ->where('users.id',$id)->first();
        return $data;
    }

}
