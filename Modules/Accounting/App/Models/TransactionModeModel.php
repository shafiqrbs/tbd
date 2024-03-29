<?php

namespace Modules\Accounting\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionModeModel extends Model
{
    use HasFactory,Sluggable;

    protected $table = 'acc_transaction_mode';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'name',
        'slug',
        'status',
    ];

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


    public static function getCategoryDropdown($domain)
    {
        $query = self::select(['name', 'slug', 'id'])
            ->where([['status', 1],['config_id', $domain['config_id']]]);
        $query->whereNotNull('parent');
        return $query->get();
    }


    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $categories = self::where('acc_transaction_mode.config_id',$domain['acc_config_id'])
            ->leftjoin('uti_transaction_method','uti_transaction_method.id','=','acc_transaction_mode.parent')
            ->select([
                'acc_transaction_mode.id',
                'acc_transaction_mode.name',
                'acc_transaction_mode.slug',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $categories = $categories->whereAny(['acc_transaction_mode.name','acc_transaction_mode.slug'],'LIKE','%'.$request['term'].'%');
        }
        $total  = $categories->count();
        $entities = $categories->skip($skip)
            ->take($perPage)
            ->orderBy('acc_transaction_mode.id','DESC')
            ->get();

        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }



}
