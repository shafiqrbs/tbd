<?php

namespace Modules\Core\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;


class UserTransactionModel extends Model
{
    use HasFactory;

    protected $table = 'cor_user_transaction';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'max_discount',
        'sales_target',
        'discount_percent'
    ];
    protected $dates = ['created_at'];

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



    public static function getRecords($request,$domain){

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;
        $users = self::where('domain_id',$domain['global_id'])
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



}
