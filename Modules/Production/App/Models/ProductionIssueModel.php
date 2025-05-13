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
        'issued_by_id',
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

        $entity = self::where('pro_issue.config_id',$domain['pro_config'])
            ->leftjoin('users as created','created.id','=','pro_issue.created_by_id')
            ->leftjoin('users as issued','issued.id','=','pro_issue.issued_by_id')
            ->leftjoin('users as approved','approved.id','=','pro_issue.approved_by_id')
            ->leftjoin('cor_warehouses','cor_warehouses.id','=','pro_issue.issue_warehouse_id')
            ->leftjoin('cor_vendors','cor_vendors.id','=','pro_issue.vendor_id')
            ->select([
                'pro_issue.id',
                'pro_issue.config_id',
                'pro_issue.process',
                'pro_issue.issue_type',
                'pro_issue.issue_warehouse_id',
                'pro_issue.production_batch_id',
                'pro_issue.created_by_id',
                'pro_issue.approved_by_id',
                'pro_issue.issued_by_id',
                'pro_issue.vendor_id',
                'pro_issue.type',
                'pro_issue.narration',
                'pro_issue.amount',
                DB::raw('DATE_FORMAT(pro_issue.created_at, "%d-%M-%Y") as created_date'),
                DB::raw('DATE_FORMAT(pro_issue.issue_date, "%d-%M-%Y") as issue_date'),
                'created.username as created_by_name',
                'issued.username as issued_name',
                'approved.username as approved_name',
                'cor_warehouses.name as warehouse_name',
                'cor_vendors.name as vendor_name',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $entity = $entity->whereAny(['pro_issue.process','created.username','issued.username','approved.username','cor_warehouses.name','cor_vendors.name'],'LIKE','%'.trim($request['term']).'%');
        }
        if (isset($request['created_at']) && !empty($request['created_at'])) {
            $entity = $entity->whereDate('pro_issue.created_at', trim($request['created_at']));
        }
        if (isset($request['type']) && !empty($request['type'])) {
            $entity = $entity->where('pro_issue.type', trim($request['type']));
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
