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
use function Monolog\Formatter\format;

class PatientPrescriptionMedicineDailyHistoryModel extends Model
{
    use HasFactory;

    protected $table = 'hms_patient_prescription_medicine_daily_history';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

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


    public static function insertDailyMedicine($domain,$id,$data)
    {
        $medicines = $data;
        $invoice = InvoiceModel::findByIdOrUid($id);
        $date = now();
        $today = new \DateTime();
        $dayDate = (string) $today->format('dmY');

        if (!empty($medicines) && is_array($medicines)) {

            foreach ($medicines as $medicine) {
                $exist = self::where(['prescription_medicine_id'=> $medicine['id'],'day_date' => $dayDate])->first();
                if(empty($exist)){
                    // STEP 1: Create Sale
                    $sale = HospitalSalesModel::create([
                        'config_id' => $domain['inv_config'],
                        'customer_id' => $invoice->customer_id ?? null,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    foreach ($medicines as $medicine) {

                        if (!StockItemModel::find($medicine['stock_id'])) {
                            continue;
                        }

                        // Create sales item and get ID
                        $saleItem = SalesItemModel::create([
                            'sale_id' => $sale->id,
                            'stock_item_id' => $medicine['stock_id'],
                            'quantity' => $medicine['quantity'] ?? 0,
                            'price' => $medicine['price'] ?? 0,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ]);

                        // Insert OR Update daily history
                        PatientPrescriptionMedicineDailyHistoryModel::updateOrCreate(
                            [
                                'prescription_medicine_id' => $medicine['id'],
                                'day_date' => $dayDate,     // unique row
                            ],
                            [
                                'hms_invoice_id' => $invoice->id,
                                'sale_item_id' => $saleItem->id,
                                'sale_id' => $sale->id,
                                'stock_id' => $medicine['stock_id'],
                                'quantity' => $medicine['quantity'] ?? 0,
                                'updated_at' => $date,
                                'created_at' => $date,
                            ]
                        );
                    }
                }
            }
        }
    }




}
