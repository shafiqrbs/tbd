<?php

namespace Modules\Production\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ProductionIssueModel extends Model
{
    protected $table = 'pro_issue';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'config_id',
        'factory_id',
        'process',
        'issue_date',
        'issue_type',
        'issue_warehouse_id',
        'production_batch_id',
        'invoice',
        'created_by_id',
        'approved_by_id',
        'vendor_id',
        'type',
        'narration',
        'amount'
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->process = 'In-progress';
            $model->created_at = $date;
        });
        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public function issueItems()
    {
        return $this->hasMany(ProductionIssueItemModel::class, 'production_issue_id');
    }
    public static function getRecords($request,$domain){
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;
//        dump($domain['pro_config']);

        $entity = self::where('pro_batch.config_id',$domain['pro_config'])
            ->join('users','users.id','=','pro_batch.created_by_id')
            ->select([
                'pro_batch.id',
                'pro_batch.invoice',
                'pro_batch.status',
                'pro_batch.process',
                'pro_batch.created_by_id',
                'pro_batch.approved_by_id',
                'users.name as created_by_name',
                DB::raw('DATE_FORMAT(pro_batch.created_at, "%d-%M-%Y") as created_date'),
                DB::raw('DATE_FORMAT(pro_batch.issue_date, "%d-%M-%Y") as issue_date'),
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $entity = $entity->whereAny(['pro_batch.invoice','pro_batch.process','users.name'],'LIKE','%'.trim($request['term']).'%');
        }
        if (isset($request['created_at']) && !empty($request['created_at'])) {
            $entity = $entity->whereDate('pro_batch.created_at', trim($request['created_at']));
        }

        $total  = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')
            ->get();

        $data = array('count'=>$total,'entities' => $entities);
        return $data;
    }




}
