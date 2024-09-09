<?php

namespace Modules\Production\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ProductionBatchModel extends Model
{
    use HasFactory;

    protected $table = 'pro_batch';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'issue_date',
        'receive_date',
        'process',
        'mode',
        'invoice',
        'created_by_id',
        'code'
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            $model->issue_date = $date;
            $model->receive_date = $date;
        });
        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public function batchItems()
    {
        return $this->hasMany(ProductionBatchItemnModel::class, 'batch_id');
    }
    public static function getRecords($request,$domain){

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;

        $entity = self::where('pro_config.domain_id',$domain['global_id'])
            ->join('pro_setting_type','pro_setting_type.id','=','pro_setting.setting_type_id')
            ->join('pro_config','pro_config.id','=','pro_setting.config_id')
            ->select([
                'pro_setting.id',
                'pro_setting.name',
                'pro_setting.slug',
                'pro_setting.status',
                DB::raw('DATE_FORMAT(pro_setting.created_at, "%d-%M-%Y") as created'),
                'pro_setting_type.name as setting_type_name',
                'pro_setting_type.slug as setting_type_slug',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $entity = $entity->whereAny(['pro_setting.name','pro_setting.slug','pro_setting_type.slug'],'LIKE','%'.trim($request['term']).'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $entity = $entity->where('pro_setting.name','LIKE','%'.trim($request['name']));
        }

        if (isset($request['setting_type_id']) && !empty($request['setting_type_id'])){
            $entity = $entity->where('pro_setting.setting_type_id',$request['setting_type_id']);
        }

        $total  = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')
            ->get();

        $data = array('count'=>$total,'entities' => $entities);
        return $data;
    }

    public static function getShow($id,$domain)
    {
        $entity = self::where([
            ['pro_batch.config_id', '=', $domain['pro_config']],
            ['pro_batch.id', '=', $id]
        ])
            ->select([
                'pro_batch.id',
                DB::raw('DATE_FORMAT(pro_batch.created_at, "%d-%m-%Y") as created_date'),
                'pro_batch.invoice as invoice',
                'pro_batch.code',
                'pro_batch.mode',
                'pro_batch.process',
                'pro_batch.status',
                'pro_batch.issue_date',
                'pro_batch.receive_date',
                'pro_batch.remark',
            ])
            ->with(['batchItems' => function ($query) {
                $query->select([
                    'pro_batch_item.id',
                    'pro_batch_item.batch_id',
                    'pro_batch_item.production_item_id',
                    'pro_batch_item.issue_quantity',
                    'pro_batch_item.receive_quantity',
                    'pro_batch_item.damage_quantity',
                    DB::raw('DATE_FORMAT(pro_batch_item.created_at, "%d-%m-%Y") as created_date')
                ])->with(['productionItems' => function ($query) {
                        $query->select([
                            'pro_element.id',
                            'pro_element.production_item_id',
                            'pro_element.quantity',
                            'pro_element.material_id',
                            'inv_stock.quantity as stock_quantity',
                            'inv_stock.name as name',
                        ])
                            ->join('inv_stock','inv_stock.id','=','pro_element.material_id')
                        ;
                    }]);
            }])
            ->first();

        foreach($entity->batchItems as $batchItem) {
            foreach($batchItem->productionItems as $productionItem) {
                $productionItem->needed_quantity = $batchItem->issue_quantity * $productionItem->quantity;
                if ($productionItem->needed_quantity>$productionItem->stock_quantity){
                    $productionItem->less_quantity = $productionItem->needed_quantity - $productionItem->stock_quantity;
                    $productionItem->more_quantity = null;
                }else{
                    $productionItem->more_quantity = $productionItem->stock_quantity-$productionItem->needed_quantity;
                    $productionItem->less_quantity = null;
                }
            }
        }

        return $entity;
    }



}
