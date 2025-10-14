<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Modules\Inventory\App\Models\SalesItemModel;
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
                        if (StockItemModel::find($medicine->medicine_id)) {
                            return [
                                'sale_id' => $sales->id,
                                'name' => $medicine->medicine_name, // notice key: medicine_name not medicineName
                                'stock_item_id' => $medicine->medicine_id ?? null,
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
                    if (StockItemModel::find($medicine->medicine_id)) {
                        SalesItemModel::updateOrCreate(
                            [
                                'sale_id' => $sales->id,
                                'stock_item_id' => $medicine->medicine_id ?? null,
                                 // unique keys
                            ],
                            [
                                'name' => $medicine->medicine_name ?? null,
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


}
