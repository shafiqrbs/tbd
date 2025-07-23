<?php

namespace Modules\Production\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\WarehouseModel;


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
        'remark',
        'mode',
        'invoice',
        'created_by_id',
        'approved_by_id',
        'status',
        'code'
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->status = 1;
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
        return $this->hasMany(ProductionBatchItemModel::class, 'batch_id');
    }

    public function productionConfig(): BelongsTo
    {
        return $this->belongsTo(ProductionConfig::class, 'config_id', 'id');
    }

    public function getDomainWarehouseAttribute(): Collection
    {
        // Safely access the domain ID using null-safe operator
        $domainId = $this->productionConfig?->domain_id;

        return $domainId
            ? WarehouseModel::where('domain_id', $domainId)->get()
            : collect(); // Safe fallback when productionConfig is null
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
                    'inv_stock.name',
                    DB::raw('DATE_FORMAT(pro_batch_item.created_at, "%d-%m-%Y") as created_date')
                ])
                    ->join('pro_item','pro_item.id','=','pro_batch_item.production_item_id')
                    ->join('inv_stock','inv_stock.id','=','pro_item.item_id')
                    ->with(['productionExpenses' => function ($query) {
                        $query->select([
                            'pro_expense.id',
                            'pro_expense.production_batch_item_id',
                            'pro_expense.production_item_id',
                            'pro_expense.quantity as needed_quantity',
                            'pro_expense.issue_quantity as raw_issue_quantity',
                            'pro_element.material_id',
                            'pro_element.quantity',
                            'inv_stock.quantity as stock_quantity',
                            'inv_stock.opening_quantity as opening_quantity',
                            'inv_stock.name as name',
                            'inv_stock.uom',
                        ])
                            ->join('pro_element','pro_element.id','=','pro_expense.production_element_id')
                            ->join('inv_stock','inv_stock.id','=','pro_element.material_id')
                        ;
                    }]);
            }])
            ->first();

        foreach($entity->batchItems as $batchItem) {
            foreach($batchItem->productionExpenses as $expense) {
                $expense->needed_quantity = $expense->needed_quantity;
                if ($expense->needed_quantity>$expense->stock_quantity){
                    $expense->less_quantity = $expense->needed_quantity - $expense->stock_quantity;
                    $expense->more_quantity = null;
                }else{
                    $expense->more_quantity = $expense->stock_quantity-$expense->needed_quantity;
                    $expense->less_quantity = null;
                }
            }
        }
//        $entity['ware'] = $entity->domain_warehouse;
        return $entity;
    }

    public static function getBatchDropdown($domain)
    {
        return self::where([['pro_batch.config_id',$domain['pro_config']],['pro_batch.status',1]])->select(['pro_batch.id','pro_batch.invoice','mode','issue_date',DB::raw('DATE_FORMAT(pro_batch.created_at, "%d-%M-%Y") as created_date')])->get();
    }

    public static function issueReportData(array $params, int $domain_id, int $production_config_id) {
        $data = self::query()
            ->where('pro_batch.config_id', '=', $production_config_id)
//            ->whereBetween('pro_batch.created_at', [$params['start_date'], $params['end_date']])
            ->select([
                'pro_batch.id',
                DB::raw('DATE_FORMAT(pro_batch.created_at, "%d-%m-%Y") as created_date'),
                'pro_batch.invoice',
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
                    'inv_stock.name',
                    DB::raw('DATE_FORMAT(pro_batch_item.created_at, "%d-%m-%Y") as created_date'),
                ])
                    ->join('pro_item', 'pro_item.id', '=', 'pro_batch_item.production_item_id')
                    ->join('inv_stock', 'inv_stock.id', '=', 'pro_item.item_id')
                    ->with(['productionExpenses' => function ($expenseQuery) {
                        $expenseQuery->select([
                            'pro_expense.id',
                            'pro_expense.production_batch_item_id',
                            'pro_expense.production_item_id',
                            'pro_expense.quantity as needed_quantity',
                            'pro_expense.issue_quantity as raw_issue_quantity',
                            'pro_element.material_id',
                            'pro_element.quantity',
                            'inv_stock.quantity as stock_quantity',
                            'inv_stock.opening_quantity',
                            'inv_stock.name',
                            'inv_stock.uom',
                        ])
                            ->join('pro_element', 'pro_element.id', '=', 'pro_expense.production_element_id')
                            ->join('inv_stock', 'inv_stock.id', '=', 'pro_element.material_id');
                    }]);
            }])
            ->get()
            ->each(function ($batch) {
                foreach ($batch->batchItems as $batchItem) {
                    foreach ($batchItem->productionExpenses as $expense) {
                        $needed = (float) $expense->needed_quantity;
                        $available = (float) $expense->stock_quantity;

                        $expense->less_quantity = $needed > $available ? $needed - $available : null;
                        $expense->more_quantity = $needed < $available ? $available - $needed : null;
                    }
                }
            });
//        $entity['ware'] = $entity->domain_warehouse;
        return $data;
//        dump($data);
    }

}
