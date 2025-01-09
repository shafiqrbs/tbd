<?php

namespace Modules\Core\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WarehouseModel extends Model
{
    protected $table = 'cor_warehouses';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'location',
        'mobile',
        'address',
        'email',
        'contract_person',
        'domain_id',
        'setting_id',
        'status'
    ];

    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $warehouses = self::where('domain_id',$domain['global_id'])
        ->select([
            'id',
            'name',
            'location',
            'contract_person',
            'email',
            'mobile',
            'address',
            'created_at'
        ]);

        if (isset($request['term']) && !empty($request['term'])){
            $warehouses = $warehouses->whereAny(['name','email','contract_person','mobile','location','address'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $warehouses = $warehouses->where('name',$request['name']);
        }

        if (isset($request['mobile']) && !empty($request['mobile'])){
            $warehouses = $warehouses->where('mobile',$request['mobile']);
        }

        if (isset($request['contract_person']) && !empty($request['contract_person'])){
            $warehouses = $warehouses->where('contract_person',$request['contract_person']);
        }

        if (isset($request['location']) && !empty($request['location'])){
            $warehouses = $warehouses->where('location',$request['location']);
        }

        $total  = $warehouses->count();
        $entities = $warehouses->skip($skip)
                        ->take($perPage)
                        ->orderBy('id','DESC')
                        ->get();

        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            $model->updated_at = $date;
            $model->status = true;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });

    }

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

    public static function quickRandom($length = 32)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

}
