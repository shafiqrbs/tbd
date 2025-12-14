<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Modules\Inventory\App\Models\DailyStockModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Ramsey\Collection\Collection;

class HospitalSalesModel extends Model
{
    use HasFactory;

    protected $table = 'inv_sales';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $model->invoice = self::salesEventListener($model)['generateId'];
            $model->code = self::salesEventListener($model)['code'];
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function quickRandom($length = 12)
    {
        $pool = '0123456789';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    public static function salesEventListener($model)
    {
        $patternCodeService = app(GeneratePatternCodeService::class);
        $params = [
            'config' => $model->config_id,
            'table' => 'inv_sales',
            'prefix' => 'INV-',
        ];
        return $patternCodeService->invoiceNo($params);
    }

    public function salesItems()
    {
        return $this->hasMany(SalesItemModel::class, 'sale_id');
    }

    public static function insertMedicineDelivery($domain,$id)
    {
        $prescription = PrescriptionModel::with(['invoice_details:id,customer_id as customer_id'])->find($id);
        $date =  new \DateTime("now");
        $config = $domain['inv_config'];
        $jsonData = json_decode($prescription['json_content']);
        $medicines = ($jsonData->medicines ?? []);
        if (!empty($medicines) && is_array($medicines)) {
            if (empty($prescription->sale_id)) {
                $insertData['config_id'] = $config;
                $insertData['customer_id'] = $prescription->invoice_details->customer_id ?? null;
                $sales = self::create($insertData);
                $insertData = collect($jsonData->medicines)
                    ->map(function ($medicine) use ($sales, $date) {
                        $medicineDetails = MedicineDetailsModel::find($medicine->medicine_id);
                        if ($medicineDetails) {
                            $medicine = $medicineDetails->medicineStock;
                            return [
                                'sale_id' => $sales->id,
                                'name' => $medicine->product->name ?? null, // notice key: medicine_name not medicineName
                                'stock_item_id' => $medicine->stock_item_id ?? null,
                                'quantity' => $medicine->opd_quantity ?? 0,
                                'price' => $medicine->price ?? 0,
                                'created_at' => $date,
                                'updated_at' => $date,
                            ];
                        }
                        return null; // explicit
                    })
                    ->filter() // ✅ remove nulls
                    ->values() // ✅ reset array keys (important for upsert)
                    ->toArray();
                SalesItemModel::upsert(
                    $insertData,
                    ['sale_id', 'name'], // unique keys
                    ['stock_item_id', 'quantity', 'price', 'updated_at'] // update columns
                );

            } else {
                SalesItemModel::where('sale_id', $prescription->sale_id)->forceDelete();
                $sales = self::find($prescription->sale_id);
                collect($medicines)->map(function ($medicine) use ($sales, $date) {
                    $medicineDetails = MedicineDetailsModel::find($medicine->medicine_id);
                    if ($medicineDetails) {
                        $medicine = $medicineDetails->medicineStock;
                        SalesItemModel::updateOrCreate(
                            [
                                'sale_id' => $sales->id,
                                'stock_item_id' =>  $medicine->stock_item_id ?? null,
                                 // unique keys
                            ],
                            [
                                'name' => $medicine->product->name ?? null,
                                'quantity' => $medicine->opd_quantity ?? 0,
                                'price' => $medicine->price ?? 0,
                                'updated_at' => $date,
                                'created_at' => $date,
                            ]
                        );
                    }
                })->toArray();
            }
            InvoiceModel::where('id', $prescription->invoice_details->id)
                ->update(['sales_id' => $sales->id]);
            PrescriptionModel::where('id', $prescription->id)
                ->update(['sale_id' => $sales->id]);
        }
    }

    public static function insertMedicineIssue($domain,$id)
    {
        $prescription = PrescriptionModel::with(['invoice_details:id,customer_id as customer_id'])->find($id);
        $date =  new \DateTime("now");
        $config = $domain['inv_config'];
        $hospitalConfig = HospitalConfigModel::where(['id'=> $domain['hms_config']])->first();
        $medicines = PatientPrescriptionMedicineModel::where(['prescription_id'=> $id])->get();
        if (!empty($medicines)) {
            if (empty($prescription->sale_id)) {

                $insertData['config_id'] = $config;
                $insertData['warehouse_id'] = $hospitalConfig->opd_store_id;
                $insertData['customer_id'] = $prescription->invoice_details->customer_id ?? null;
                $sales = self::create($insertData);
                $insertData = collect($medicines)
                    ->map(function ($medicine) use ($sales, $date) {
                        if($medicine->stock_item_id && $medicine->opd_quantity > 0 && $medicine->opd_status == 1){
                            return [
                                'sale_id' => $sales->id,
                                'name' => $medicine->generic ?? null, // notice key: medicine_name not medicineName
                                'stock_item_id' => $medicine->stock_item_id ?? null,
                                'quantity' => $medicine->opd_quantity ?? 0,
                                'created_at' => $date,
                                'updated_at' => $date,
                            ];
                        }
                        return null; // explicit
                    })
                    ->filter() // ✅ remove nulls
                    ->values() // ✅ reset array keys (important for upsert)
                    ->toArray();
                SalesItemModel::upsert(
                    $insertData,
                    ['sale_id', 'name'], // unique keys
                    ['stock_item_id', 'quantity', 'price', 'updated_at'] // update columns
                );

            } else {
                SalesItemModel::where('sale_id', $prescription->sale_id)->forceDelete();
                $sales = self::find($prescription->sale_id);
                collect($medicines)->map(function ($medicine) use ($sales, $date) {
                    if($medicine->stock_item_id && $medicine->opd_quantity > 0 && $medicine->opd_status == 1){
                        SalesItemModel::updateOrCreate(
                            [
                                'sale_id' => $sales->id,
                                'stock_item_id' =>  $medicine->stock_item_id ?? null,
                                // unique keys
                            ],
                            [
                                'name' => $medicine->generic ?? null,
                                'quantity' => $medicine->quantity ?? 0,
                                'updated_at' => $date,
                                'created_at' => $date,
                            ]
                        );
                    }
                })->toArray();
            }
            InvoiceModel::where('id', $prescription->invoice_details->id)
                ->update(['sales_id' => $sales->id]);
            PrescriptionModel::where('id', $prescription->id)
                ->update(['sale_id' => $sales->id]);
        }
    }

    public static function getMedicineIssueDetails($params, $domain)
    {
        // default pagination setup (unchanged)
        $page = isset($params['page']) && $params['page'] > 0 ? ($params['page'] - 1) : 0;
        $perPage = isset($params['offset']) && $params['offset'] != '' ? (int)($params['offset']) : 0;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        // prepare date range
        $warehouseId = $params['warehouse_id'] ?? null;
        $rawStart = $params['start_date'] ?? null;
        $rawEnd   = $params['end_date'] ?? $rawStart;

        $startDate = $rawStart ? \Illuminate\Support\Carbon::parse($rawStart)->startOfDay() : null;

        $endDate = $rawEnd
            ? Carbon::parse($rawEnd)->endOfDay()
            : ($rawStart ? Carbon::parse($rawStart)->endOfDay() : null);

        // main query builder
        $stockItems = SalesItemModel::join('inv_sales','inv_sales.id','=','inv_sales_item.sale_id')
            ->leftjoin('cor_customers','cor_customers.id','=','inv_sales.customer_id')
            ->leftjoin('cor_warehouses','inv_sales_item.warehouse_id','=','cor_warehouses.id')
            ->where('inv_sales.config_id', $domain['config_id'])
            ->when(!empty($startDate) && !empty($endDate), function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('inv_sales.created_at', [$startDate, $endDate]);
            })
            ->when(!empty($warehouseId), function ($query) use ($warehouseId) {
                return $query->where('inv_sales_item.warehouse_id', $warehouseId);
            })
            ->select([
                'inv_sales_item.id',
                'inv_sales_item.id as sales_item_id',
                'inv_sales_item.sale_id',
                'cor_customers.name as customer_name',
                'cor_customers.mobile as customer_mobile',
                'inv_sales.process',
                'inv_sales.invoice',
                'inv_sales_item.name',
                'inv_sales_item.quantity',
                'cor_warehouses.name as warehouse_name',
                DB::raw('DATE_FORMAT(CONVERT_TZ(inv_sales.created_at, "+00:00", "+06:00"), "%d %b %Y, %h:%i %p") as created_date'),
            ]);

        // pagination block (same logic, just safer with cloned count)
        if ($params['page'] && $params['offset']) {
            $total = (clone $stockItems)->count();

            $items = $stockItems->skip($skip)
                ->take($perPage)
                ->orderBy('inv_sales_item.id', 'DESC')
                ->get();

            return [
                'count' => $total,
                'items' => $items,
            ];
        }

        // non-paginated case
        return $stockItems
            ->orderBy('inv_sales_item.id', 'DESC')
            ->get();
    }

    public static function getMedicineSummeryDetails($params, $domain)
    {
        $page    = isset($params['page']) && $params['page'] > 0 ? ($params['page'] - 1) : 0;
        $perPage = isset($params['offset']) && $params['offset'] != '' ? (int)$params['offset'] : 0;
        $skip    = $page * $perPage;

        $warehouseId = $params['warehouse_id'] ?? null;
        $rawStart    = $params['start_date'] ?? null;
        $rawEnd      = $params['end_date'] ?? $rawStart;

        $startDate = $rawStart ? Carbon::parse($rawStart)->startOfDay() : null;
        $endDate   = $rawEnd   ? Carbon::parse($rawEnd)->endOfDay() : null;

        $query = DB::table('inv_stock_item_history')
            ->leftJoin('cor_warehouses', 'inv_stock_item_history.warehouse_id', '=', 'cor_warehouses.id')
            ->where('inv_stock_item_history.config_id', $domain['config_id'])
            ->when($warehouseId, fn ($q) =>
                $q->where('inv_stock_item_history.warehouse_id', $warehouseId)
            )
            ->when($startDate && $endDate, fn ($q) =>
                $q->whereBetween('inv_stock_item_history.created_at', [$startDate, $endDate])
            )
            ->selectRaw("
            warehouse_id,
            stock_item_id,
            item_name as name,
            cor_warehouses.name AS warehouse_name,

            /* Opening Quantity: previous closing before start date */
            (
                SELECT ih_open.closing_quantity
                FROM inv_stock_item_history AS ih_open
                WHERE ih_open.stock_item_id = inv_stock_item_history.stock_item_id
                  AND ih_open.warehouse_id = inv_stock_item_history.warehouse_id
                  AND ih_open.created_at < ?
                ORDER BY ih_open.id DESC
                LIMIT 1
            ) AS opening_quantity,

            /* Total IN */
            SUM(
                CASE
                    WHEN quantity > 0 THEN quantity
                    ELSE 0
                END
            ) AS total_in_quantity,

            /* Total OUT */
            ABS(SUM(
                CASE
                    WHEN quantity < 0 THEN quantity
                    ELSE 0
                END
            )) AS total_out_quantity,

            /* Closing Quantity: last row in date range */
            (
                SELECT ih_close.closing_quantity
                FROM inv_stock_item_history AS ih_close
                WHERE ih_close.stock_item_id = inv_stock_item_history.stock_item_id
                  AND ih_close.warehouse_id = inv_stock_item_history.warehouse_id
                  AND ih_close.created_at BETWEEN ? AND ?
                ORDER BY ih_close.id DESC
                LIMIT 1
            ) AS closing_quantity
        ", [
                $startDate,           // opening_quantity
                $startDate, $endDate  // closing_quantity
            ])
            ->groupBy(
                'warehouse_id',
                'stock_item_id',
                'item_name'
            )
            ->orderBy('warehouse_id')
            ->orderBy('stock_item_id');

        /** Pagination */
        if (!empty($params['page']) && !empty($params['offset'])) {
            $total = (clone $query)->count();
            $items = $query->skip($skip)->take($perPage)->get();

            return [
                'count' => $total,
                'items' => $items,
            ];
        }

        return $query->get();
    }

}
