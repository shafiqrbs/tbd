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

    public static function getRecords($request){

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;
        $users = self::
        select([
            'id',
            'name',
            'username',
            'email',
            'mobile',
            'created_at'
        ]);

        if (isset($request['term']) && !empty($request['term'])){
            $users = $users->where('name','LIKE','%'.$request['term'].'%')
                ->orWhere('email','LIKE','%'.$request['term'].'%')
                ->orWhere('username','LIKE','%'.$request['term'].'%')
                ->orWhere('mobile','LIKE','%'.$request['term'].'%');
        }

        $total  = $users->count();
        $entities = $users->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')->get();

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

}
