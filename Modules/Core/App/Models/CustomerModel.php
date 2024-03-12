<?php

namespace Modules\Core\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Entities\Customer;


class CustomerModel extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = 'cor_customers';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'mobile',
        'location_id',
        'address',
        'customer_group',
        'email',
        'alternative_mobile',
        'credit_limit',
        'reference_id',
        'opening_balance',
        'marketing_id',
        'code',
        'customerId',
        'bloodGroup',
        'dob',
        'age',
        'ageType',
        'ageGroup',
        'gender',
        'permanentAddress',
        'fatherName',
        'motherName',
        'maritalStatus',
        'alternativeContactPerson',
        'alternativeContactMobile',
        'alternativeRelation',
        'company',
        'weight',
        'bloodPressure',
        'diabetes',
        'firstName',
        'lastName',
        'customerId',
        'customer_unique_name',
        'unique_id',
        'global_option_id'
    ];
    public static function getAllCustomers(){
        $data = self::where(['status'=>1])->whereNotNull('mobile')->orderBy('name','ASC')
            ->select([
                'customers.id as id',
                'customers.name as name',
                'customers.mobile as mobile'
            ])
            ->get()->toArray();
        return $data;
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(LocationModel::class);
    }

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->unique_id = self::quickRandom();
            $model->created_at = $date;
            $model->updated_at = $date;
            $model->created = $date;
            $model->updated = $date;
            $datetime = new \DateTime("now");
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->unique_id = self::quickRandom();
            $model->updated_at = $date;
            $model->updated = $date;
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


    public static function getRecords($domain,$request){

        $global = $domain['global_id'];

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;

        $customers = self::where('global_option_id',$global)
            ->select([
                'id',
                'name',
                'mobile',
                'created_at'
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $customers = $customers->whereAny(['name','mobile'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $customers = $customers->where('name',$request['name']);
        }

        if (isset($request['mobile']) && !empty($request['mobile'])){
            $customers = $customers->where('mobile',$request['mobile']);
        }


        $totalUsers  = $customers->count();
        $customers = $customers->skip($skip)
                            ->take($perPage)
                            ->orderBy('id','DESC')
                            ->get();

        $data = array('count'=>$totalUsers,'entities' => $customers);
        return $data;
    }


}
