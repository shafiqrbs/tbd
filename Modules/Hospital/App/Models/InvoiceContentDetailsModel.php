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

class InvoiceContentDetailsModel extends Model
{
    use HasFactory;

    protected $table = 'hms_invoice_content_details';
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

    public static function insertContentDetails($domain,$id)
    {
        $prescription = PrescriptionModel::with(['invoice_transaction:id'])->find($id);
        $date =  new \DateTime("now");
        $jsonData = json_decode($prescription['json_content']);
        $patientExam = $jsonData->patient_report->patient_examination ?? '';

        // Loop through all dynamic keys (chief_complaints, comorbidity, etc.)
        if($patientExam){
            foreach ($patientExam as $sectionName => $items) {

                // $items can be array or object, ensure it's iterable
                if (!is_array($items)) {
                    continue;
                }

                foreach ($items as $item) {

                    // Example DB insert like your investigation code
                    $particular = ParticularModel::find($item->id);
                    $particularParent = (isset($particular->particular_type_id) and !$particular->particular_type_id) ? $particular->particular_type_id : '';
                    if ($particular && $particularParent) {
                        self::updateOrCreate(
                            [
                                'hms_invoice_id'  => $prescription->hms_invoice_id,
                                'prescription_id' => $prescription->id,
                                'particular_id'   => $item->id,
                            ],
                            [
                                'particular_type_id'   => $particularParent,
                                'name'   => $particular->name,
                                'value'   => $item->value ?? null,
                                'duration'   => $item->duration ?? null,
                                'updated_at' => $date,
                                'created_at' => $date,
                            ]
                        );
                    }
                }
            }
        }

    }

    public static function insertIpdContentDetails($domain,$invoice,$data)
    {

        $date =  new \DateTime("now");
        $jsonData = json_decode($data['json_content']);
        $patientExam =$jsonData['patient_examination'] ?? '';

        // Loop through all dynamic keys (chief_complaints, comorbidity, etc.)

        if($patientExam){

            foreach ($patientExam as $sectionName => $items) {

                // $items can be array or object, ensure it's iterable
                if (!is_array($items)) {
                    continue;
                }

                foreach ($items as $item) {

                    // Example DB insert like your investigation code
                    $particular = ParticularModel::find($item->id);
                    $particularParent = $particular->particular_type_id;
                    if ($particular && $particularParent) {
                        self::updateOrCreate(
                            [
                                'hms_invoice_id'  => $invoice,
                                'particular_id'   => $item->id,
                            ],
                            [
                                'particular_type_id'   => $particularParent,
                                'name'   => $particular->name,
                                'value'   => $item->value ?? null,
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
