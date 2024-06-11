<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Utility\App\Models\ProductUnitModel;
use Ramsey\Collection\Collection;

class PurchaseItemModel extends Model
{
    use HasFactory;

    protected $table = 'inv_purchase_item';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
    ];

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


    public function purchase()
    {
        return $this->belongsTo(PurchaseModel::class);
    }


    public static function getRecords($request,$domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)$request['offset'] : 0;
        $skip = $page * $perPage;

        $isApprovedCondition = $request['is_approved'] == 0 ? 'whereNull' : 'whereNotNull';
        $purchaseItems = self::where([
            ['inv_purchase_item.config_id',$domain['config_id']],
            ['inv_purchase_item.mode', 'opening']
        ])->$isApprovedCondition('approved_by_id');

        $purchaseItems = $purchaseItems->leftjoin('inv_product','inv_product.id','=','inv_purchase_item.product_id')
            ->leftjoin('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('uti_product_unit','uti_product_unit.id','=','inv_product.unit_id')
            ->leftjoin('inv_brand','inv_brand.id','=','inv_product.brand_id')
            ->leftjoin('uti_settings','uti_settings.id','=','inv_product.product_type_id')
            ->select([
                'inv_purchase_item.id',
                'inv_purchase_item.product_id',
                'inv_purchase_item.opening_quantity',
                'inv_purchase_item.sales_price',
                'inv_purchase_item.purchase_price',
                'inv_purchase_item.sub_total',
                'inv_product.name as product_name',
                'inv_product.slug',
                'inv_category.name as category_name',
                'uti_product_unit.name as unit_name',
                'inv_brand.name as brand_name',
                'inv_product.barcode',
                'inv_product.alternative_name',
                'uti_settings.name as product_type',
                DB::raw('DATE_FORMAT(inv_purchase_item.updated_at, "%d-%M-%Y") as created'),
            ]);

        if (!empty($request['term'])) {
            $purchaseItems = $purchaseItems->whereAny(
                ['inv_product.name', 'inv_product.slug', 'inv_category.name', 'uti_product_unit.name', 'inv_brand.name', 'inv_product.sales_price', 'uti_settings.name'],
                'LIKE',
                '%' . $request['term'] . '%'
            );
        }

        if (!empty($request['start_date'])) {
            $start_date = $request['start_date'].' 00:00:00';
            $end_date = (!empty($request['end_date']))
                ? $request['end_date'].' 23:59:59'
                : $request['start_date'].' 23:59:59';

            $purchaseItems = $purchaseItems->whereBetween('inv_purchase_item.updated_at',[$start_date, $end_date]);
        }

        $total = $purchaseItems->count();
        $entities = $purchaseItems->skip($skip)
            ->take($perPage)
            ->orderBy('inv_purchase_item.updated_at','DESC')
            ->get();

        $data = ['count' => $total, 'entities' => $entities];
        return $data;

    }


}
