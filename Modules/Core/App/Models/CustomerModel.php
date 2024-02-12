<?php

namespace Modules\Core\App\Models;

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

    protected $table = 'cor_customers';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'mobile'
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
            dd($model);
            exit;
            $date =  new \DateTime("now");
            $model->unique_key = self::quickRandom();
            $model->created_at = $date;
            $model->updated_at = $date;
            $model->created = $date;
            $model->updated = $date;

            $datetime = new \DateTime("now");
            /*$lastCode = $this->getLastCode();
            $entity->setCode($lastCode+1);
            if(empty($entity->getCustomerId())){
                $model->setCustomerId(sprintf("%s%s", $datetime->format('my'), str_pad($entity->getCode(),5, '0', STR_PAD_LEFT)));
            }*/

        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->unique_key = self::quickRandom();
            $model->created_at = $date;
            $model->updated_at = $date;
            $model->created = $date;
            $model->updated = $date;
        });

    }

    public static function quickRandom($length = 32)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }


    public static function getLastCode($domain)
    {


      //  $entity = self::where('global_option_id',$domain)->count();
        $datetime = new \DateTime("now");
        $today_startdatetime = $datetime->format('Y-m-01 00:00:00');
        $today_enddatetime = $datetime->format('Y-m-t 23:59:59');
        $entity = DB::table('cor_customers as s')
            ->select(DB::raw("MAX(s.code) as code"))
            //->where('s.created','>=', $today_startdatetime)
            //->where('s.created','<=', $today_startdatetime)
            ->where('s.global_option_id', $domain)->first();
       return $entity->code;

    }

}
