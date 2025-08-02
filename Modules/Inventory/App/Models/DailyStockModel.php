<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class DailyStockModel extends Model
{
    protected $table = 'inv_daily_stock';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'config_id',
        'inv_date',
        'warehouse_id',
        'stock_item_id',
        'item_name',
        'uom',
        'price',
        'purchase_price',
        'sales_price',
        'actual_price',
        'discount_price',
        'opening_quantity',
        'opening_balance',
        'production_quantity',
        'purchase_quantity',
        'sales_return_quantity',
        'asset_in_quantity',
        'total_in_quantity',
        'sales_quantity',
        'damage_quantity',
        'purchase_return_quantity',
        'production_expense_quantity',
        'asset_out_quantity',
        'total_out_quantity',
        'closing_quantity',
        'closing_balance',
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

    /**
     * @throws \Throwable
     */
    public static function maintainDailyStock($date, $field, $configId = null, $warehouseId = null, $stockItemId = null , $quantity = 0)
    {
//        dump($date,$field,$configId,$warehouseId,$stockItemId,$quantity);
//        dump(DB::connection()->getDatabaseName());

//        DB::beginTransaction();
//        DB::enableQueryLog();
        $findDailyStock = DailyStockModel::where('config_id',$configId)
            ->where('stock_item_id',$stockItemId)
            /*->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('warehouse_id',$warehouseId);
            })*/
//            ->where('inv_date',$date)
            ->first();

        dump($configId,$stockItemId,$warehouseId,$date);
//        dump($findDailyStock);

        /*dump([
            'id' => $findDailyStock->id,
            'query' => DailyStockModel::find($findDailyStock->id),
            'sql' => DB::select('select * from inv_daily_stock where id = ?', [$findDailyStock->id])
        ]);*/


        /*if (empty($findDailyStock)) {
            $stockItem = StockItemModel::find($stockItemId);
            $findDailyStock = DailyStockModel::create([
                'config_id' => $configId,
                'inv_date' => $date,
                'warehouse_id' => $warehouseId,
                'stock_item_id' => $stockItemId,
                'item_name' => $stockItem->name,
                'sales_price' => $stockItem->sales_price,
                'purchase_price' => $stockItem->purchase_price,
            ]);
        }*/
//        DB::commit(); // REQUIRED

//        dump(DB::getQueryLog()); // dump the SQL queries


//        return $findDailyStock;
        /*// Define operators for processes
        $operatorData = [
            'production_quantity' => '+',
            'purchase_quantity' => '+',
            'sales_return_quantity' => '+',
            'asset_in_quantity' => '+',
            'sales_quantity' => '-',
            'damage_quantity' => '-',
            'purchase_return_quantity' => '-',
            'production_expense_quantity' => '-',
            'asset_out_quantity' => '-',
        ];

        // Determine the operator and apply calculations
        $operator = $operatorData[$field] ?? '+';
        dump($operator);*/
    }

    public function category()
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function measurement()
    {
        return $this->hasMany(ProductMeasurementModel::class, 'product_id');
    }

    public function unit()
    {
        return $this->belongsTo(ParticularModel::class, 'unit_id');
    }

    public function setting()
    {
        return $this->belongsTo(SettingModel::class, 'product_type_id');
    }
    public function images()
    {
        return $this->hasOne(ProductGalleryModel::class, 'product_id', 'product_id')->where('status', 1);
    }

    public function measurments():HasMany
    {
        return $this->hasMany(ProductMeasurementModel::class, 'product_id');
    }
}
