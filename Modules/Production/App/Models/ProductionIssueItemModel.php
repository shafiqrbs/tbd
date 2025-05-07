<?php

namespace Modules\Production\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;


class ProductionIssueItemModel extends Model
{
    protected $table = 'pro_issue_item';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'config_id',
        'stock_item_id',
        'process',
        'quantity',
        'name',
        'uom',
        'product_warehouse_id',
        'purchase_price',
        'sales_price',
        'issue_date',
        'production_issue_id'
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

    public function production_issue()
    {
        return $this->belongsTo(ProductionIssueModel::class);
    }


    /**
     * Insert multiple sales items.
     *
     * @param ProductionIssueModel $issue Issue instance.
     * @param array $items Incoming items to insert.
     * @return bool
     */
    public static function insertIssueItems($issue, array $items): bool
    {
        $timestamp = Carbon::now();

        $formattedItems = array_map(function ($item) use ($issue, $timestamp) {
            $findStockHistory = StockItemHistoryModel::find($item['product_id']);
            return [
                'production_issue_id'       => $issue->id,
                'config_id'      => $issue->config_id,
                'stock_item_id'  => $findStockHistory->stock_item_id ?? null,
                'product_warehouse_id'  => $item['product_warehouse_id'] ?? null,
                'issue_date'  => $issue->issue_date ?? null,
                'name'  => $item['display_name'] ?? null,
                'quantity'  => $item['quantity'] ?? null,
                'uom'  => $item['unit_name'] ?? null,
                'sales_price'      => $item['sales_price'] ?? 0,
                'purchase_price'      => $item['purchase_price'] ?? 0,
                'created_at'     => $timestamp,
                'updated_at'     => $timestamp
            ];
        }, $items);

        return self::insert($formattedItems);
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
