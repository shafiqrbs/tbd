<?php

namespace Modules\Core\App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class VendorModel extends Model
{
    use HasFactory;
    use Sluggable;


    protected $table = 'cor_vendors';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'company_name',
        'mobile',
        'address',
        'email',
        'opening_balance',
        'binno',
        'tinno',
        'code',
        'vendor_code',
        'customer_id',
        'sub_domain_id',
        'domain_id',
        'status'
    ];

    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;
        $vendors = self::where('domain_id',$domain['global_id'])
        ->select([
            'id',
            'name',
            'company_name',
            'email',
            'mobile',
            'created_at'
        ]);

        if (isset($request['term']) && !empty($request['term'])){
            $vendors = $vendors->whereAny(['name','email','company_name','mobile'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $vendors = $vendors->where('name',$request['name']);
        }

        if (isset($request['mobile']) && !empty($request['mobile'])){
            $vendors = $vendors->where('mobile',$request['mobile']);
        }

        if (isset($request['company_name']) && !empty($request['company_name'])){
            $vendors = $vendors->where('company_name',$request['company_name']);
        }

        $total  = $vendors->count();
        $entities = $vendors->skip($skip)
                        ->take($perPage)
                        ->orderBy('id','DESC')
                        ->get();

        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }


    public static function getRecordsForLocalStorage($request,$domain): array
    {
        $vendors = self::where('cor_vendors.domain_id',$domain['global_id'])
            ->select([
                'cor_vendors.id',
                'cor_vendors.name',
                'cor_vendors.vendor_code',
                'cor_vendors.code',
                'cor_vendors.company_name',
                'cor_vendors.slug',
                'cor_vendors.address',
                'cor_vendors.email',
                'cor_vendors.mobile',
                'cor_vendors.unique_id',
                DB::raw('DATE_FORMAT(cor_vendors.created_at, "%d-%m-%Y") as created_date'),
                'cor_vendors.created_at'
            ])
            ->orderBy('cor_vendors.id','DESC')
            ->get();

        $data = array('entities'=>$vendors);
        return $data;
    }


    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->unique_id = self::quickRandom();
            $model->created_at = $date;
            $model->updated_at = $date;
            $model->status = true;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->unique_id = self::quickRandom();
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
