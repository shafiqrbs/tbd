<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Modules\Hospital\App\Entities\InvoiceParticular;
use Ramsey\Collection\Collection;

class InvoiceTransactionModel extends Model
{
    use HasFactory;

    protected $table = 'hms_invoice_transaction';
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

    public static function insertInvestigations($domain,$id)
    {
        $prescription = PrescriptionModel::with(['invoice_transaction:id'])->find($id);
        $date =  new \DateTime("now");
        $jsonData = json_decode($prescription['json_content']);
        $investigations = ($jsonData->patient_report->patient_examination->investigation);

        $invoiceTransaction = self::updateOrCreate(
            [
                'invoice_id'                    => $prescription->hms_invoice_id,
                'prescription_id'               => $prescription->id,
            ],
            [
                'created_by_id'    => $prescription->created_by_id,
                'updated_at'    => $date,
                'created_at'    => $date,
            ]
        );

        collect($investigations)->map(function ($investigation) use ($prescription,$invoiceTransaction,$date) {

            $particular = ParticularModel::find($investigation->investigation_id);
            if($particular){
                InvoiceParticularModel::updateOrCreate(
                    [
                        'hms_invoice_id'            => $prescription->hms_invoice_id,
                        'prescription_id'            => $prescription->id,
                        'invoice_transaction_id'       => $invoiceTransaction->id,
                        'particular_id'              => $investigation->investigation_id,
                    ],
                    [
                        'quantity'      => 1,
                        'price'         => $particular->price ?? 0,
                        'updated_at'    => $date,
                        'created_at'    => $date,
                    ]
                );
            }
        })->toArray();
    }

}
