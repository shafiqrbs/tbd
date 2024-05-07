<?php

namespace Modules\Accounting\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class TransactionModeModel extends Model
{
    use HasFactory,Sluggable;
    protected $table = 'acc_transaction_mode';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'method_id',
        'name',
        'slug',
        'short_name',
        'authorised',
        'authorised_mode_id',
        'account_mode_id',
        'account_type',
        'service_charge',
        'account_owner',
        'path',
        'status',
        'config_id',
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

        $tramsactionsMode = self::where('acc_transaction_mode.config_id',$domain['acc_config_id'])
            ->leftjoin('uti_transaction_method','uti_transaction_method.id','=','acc_transaction_mode.method_id')
            ->leftjoin('uti_settings as authorized','authorized.id','=','acc_transaction_mode.authorised_mode_id')
            ->leftjoin('uti_settings as account_type','account_type.id','=','acc_transaction_mode.account_mode_id')
            ->select([
                'acc_transaction_mode.id',
                'acc_transaction_mode.name',
                'acc_transaction_mode.slug',
                'acc_transaction_mode.service_charge',
                'acc_transaction_mode.account_owner',
                'acc_transaction_mode.path',
                'acc_transaction_mode.short_name',
                'uti_transaction_method.name as method_name',
                'authorized.name as authorized_name',
                'account_type.name as account_type_name',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $tramsactionsMode = $tramsactionsMode->whereAny(
                ['acc_transaction_mode.name','acc_transaction_mode.slug','acc_transaction_mode.account_owner','acc_transaction_mode.short_name'],'LIKE','%'.$request['term'].'%');
        }
        $total  = $tramsactionsMode->count();
        $entities = $tramsactionsMode->skip($skip)
            ->take($perPage)
            ->orderBy('acc_transaction_mode.id','DESC')
            ->get();

        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }


    public static function getTransactionsModeData($domain)
    {
        $entities = self::where([['acc_transaction_mode.config_id',$domain['acc_config_id']],['acc_transaction_mode.status',1]])
            ->leftjoin('uti_transaction_method','uti_transaction_method.id','=','acc_transaction_mode.method_id')
            ->select([
                'acc_transaction_mode.id',
                'acc_transaction_mode.authorised',
                'acc_transaction_mode.service_charge',
                'acc_transaction_mode.account_type',
                'acc_transaction_mode.name',
                'uti_transaction_method.name as method_name',
                'uti_transaction_method.slug as method_slug',
                DB::raw("CONCAT('".url('')."/uploads/accounting/transaction-mode/', acc_transaction_mode.path) AS path")
        ])
            ->limit(8)
            ->get();
        return $entities;
    }


    public static function getRecordsForLocalStorage($request,$domain)
    {
        $tramsactionsMode = self::where([['acc_transaction_mode.config_id',$domain['acc_config_id']],['acc_transaction_mode.status',1]])
            ->leftjoin('uti_transaction_method','uti_transaction_method.id','=','acc_transaction_mode.method_id')
            ->leftjoin('uti_settings as authorized','authorized.id','=','acc_transaction_mode.authorised_mode_id')
            ->leftjoin('uti_settings as account_type','account_type.id','=','acc_transaction_mode.account_mode_id')
            ->select([
                'acc_transaction_mode.id',
                'acc_transaction_mode.name',
                'acc_transaction_mode.slug',
                'acc_transaction_mode.service_charge',
                'acc_transaction_mode.account_owner',
                'acc_transaction_mode.path',
                'acc_transaction_mode.short_name',
                'authorized.name as authorized_name',
                'account_type.name as account_type_name',
                'uti_transaction_method.name as method_name',
                'uti_transaction_method.slug as method_slug',
                DB::raw("CONCAT('".url('')."/uploads/accounting/transaction-mode/', acc_transaction_mode.path) AS path")
            ])
            ->orderBy('acc_transaction_mode.id','DESC')
            ->get();

        $data = array('entities'=>$tramsactionsMode);
        return $data;
    }



}
