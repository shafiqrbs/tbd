<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Ramsey\Collection\Collection;

class ParticularDetailsModel extends Model
{
    use HasFactory;

    protected $table = 'hms_particular_details';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public static function boot()
    {
        parent::boot();
    }


    public function patientMode()
    {
        return $this->hasOne(ParticularModeModel::class, 'id', 'patient_mode_id');
    }
    public function paymentMode()
    {
        return $this->hasOne(ParticularModeModel::class, 'id', 'payment_mode_id');
    }
    public function genderMode()
    {
        return $this->hasOne(ParticularModeModel::class, 'id', 'gender_mode_id');
    }
     public function cabinMode()
    {
        return $this->hasOne(ParticularModeModel::class, 'id', 'cabin_mode_id');
    }
    public function RoomNo()
    {
        return $this->hasOne(ParticularModel::class, 'id', 'room_id');
    }

    public static function insertBed($particular,$data){

        $room_id = (isset($data['room_id']) and $data['room_id']) ? $data['room_id']:null;
        $gender_mode_id = (isset($data['gender_mode_id']) and $data['gender_mode_id']) ? $data['gender_mode_id']:null;
        $payment_mode_id = (isset($data['payment_mode_id']) and $data['payment_mode_id']) ? $data['payment_mode_id']:null;
        $patient_mode_id = (isset($data['patient_mode_id']) and $data['patient_mode_id']) ? $data['patient_mode_id']:null;
        $room = ParticularModel::find($room_id);
        $gender = ParticularModeModel::find($gender_mode_id);
        $payment = ParticularModeModel::find($payment_mode_id);
        $patient = ParticularModeModel::find($patient_mode_id);

        $parts = [];
        if ($payment) { $parts[] = $payment->name; }
        if ($patient) { $parts[] = $patient->name; }
        if ($gender) { $parts[] = $gender->name; }
        if ($room) { $parts[] = $room->name;}
        $implode = implode(' ', $parts);
        $displayName = "{$implode} - {$particular->name}";
        self::updateOrCreate(
            [
                'id' => $particular->id,
            ],
            [
                'room_id'           => $room_id,
                'display_name'      => $displayName,
                'patient_mode_id'   => $patient_mode_id,
                'gender_mode_id'    => $gender_mode_id,
                'payment_mode_id'   => $payment_mode_id,
            ]
        );


        ParticularModel::updateOrCreate(
            [
                'id' => $particular->id,
            ],
            [
                'display_name' => $displayName,
            ]
        );
    }

    public static function insertCabin($particular,$data){

        $gender_mode_id = (isset($data['gender_mode_id']) and $data['gender_mode_id']) ? $data['gender_mode_id']:null;
        $payment_mode_id = (isset($data['payment_mode_id']) and $data['payment_mode_id']) ? $data['payment_mode_id']:null;
        $patient_mode_id = (isset($data['patient_mode_id']) and $data['patient_mode_id']) ? $data['patient_mode_id']:null;
        $cabin_mode_id = (isset($data['cabin_mode_id']) and $data['cabin_mode_id']) ? $data['cabin_mode_id']:null;

        $payment = ParticularModeModel::find($payment_mode_id);
        $patient = ParticularModeModel::find($patient_mode_id);
        $cabin = ParticularModeModel::find($cabin_mode_id);

        $parts = [];
        if ($payment) { $parts[] = $payment->name; }
        if ($patient) { $parts[] = $patient->name; }
        if ($cabin) { $parts[] = $cabin->name; }
        $implode = implode(' ', $parts);
        $displayName = "{$implode} - {$particular->name}";
        self::create([
            'particular_id'     => $particular->id,
            'display_name'      => $displayName,
            'patient_mode_id'   => $patient_mode_id,
            'gender_mode_id'    => $gender_mode_id,
            'payment_mode_id'   => $payment_mode_id,
            'cabin_mode_id'   => $cabin_mode_id,
        ]);
        ParticularModel::updateOrCreate(
            [
                'id' => $particular->id,
            ],
            [
                'display_name' => $displayName,
            ]
        );
    }

    public static function insertDoctor($particular,$data){
        self::create([
            'particular_id'     => $particular->id,
        ]);
    }
}
