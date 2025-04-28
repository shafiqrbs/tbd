<?php

namespace Modules\Core\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;


class UserWarehouseModel extends Model
{
    use HasFactory;

    protected $table = 'cor_user_warehouse';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'warehouse_id',
        'max_discount'
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

    public static function getUserAllWarehouse($domain)
    {

        $data = self::where('users.domain_id',$domain['global_id'])
            ->join('users','users.id','=','cor_user_warehouse.user_id')
            ->join('cor_warehouses','cor_warehouses.id','=','cor_user_warehouse.warehouse_id')
            ->select([
                'cor_user_warehouse.id',
                'cor_user_warehouse.is_status as status',
                'cor_user_warehouse.user_id',
                'cor_warehouses.name as warehouse_name',
                'cor_warehouses.location as warehouse_location',
                'cor_warehouses.contract_person',
                'cor_warehouses.mobile as warehouse_mobile',
                'users.name',
                'users.username',
            ])
            ->get()->toArray();
        return $data;
    }
}
