<?php

namespace Modules\Domain\App\Models;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DomainModel extends Model
{
    use HasFactory,Sluggable;

    protected $table = 'dom_domain';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'unique_code',
        'slug',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public static function getRecords($request){

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;
        $entities = self::select([
            'id',
            'name',
            'email',
            'mobile',
            'unique_code',
            'created_at'
        ]);

        if (isset($request['term']) && !empty($request['term'])){
            $entities = $entities->whereAny(['name','email','username','mobile'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $entities = $entities->where('name',$request['name']);
        }

        if (isset($request['mobile']) && !empty($request['mobile'])){
            $entities = $entities->where('mobile',$request['mobile']);
        }

        if (isset($request['email']) && !empty($request['email'])){
            $entities = $entities->where('email',$request['email']);
        }

        $total  = $entities->count();
        $entities = $entities->skip($skip)
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
            $model->unique_code = self::quickRandom();
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->unique_code = self::quickRandom();
            $model->updated_at = $date;
        });

    }

    public static function quickRandom($length = 10)
    {
        $pool = '0123456789';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }


    public static function getEntityData($id)
    {
        $data = self::select(['dom_global_option.id as global_id','inv_config.id as config_id','users.id as user_id'])
            ->join('dom_global_option','dom_global_option.id','=','users.domain_id')
            ->join('inv_config','inv_config.domain_id','=','dom_global_option.id')
            ->where('users.id',$id)->first();
        return $data;
    }

}
