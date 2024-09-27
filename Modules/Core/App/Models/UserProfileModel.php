<?php

namespace Modules\Core\App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class UserProfileModel extends Model
{
    use HasFactory;

    protected $table = 'core_user_profiles';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'location_id',
        'designation_id',
        'employee_group_id',
        'department_id',
        'bank_id',
        'name',
        'father_name',
        'mother_name',
        'user_group',
        'mobile',
        'phone_no',
        'email',
        'facebook_id',
        'profession',
        'about',
        'address',
        'permanent_address',
        'postal_code',
        'additional_phone',
        'occupation',
        'nid',
        'gender',
        'dob',
        'blood_group',
        'religion_status',
        'marital_status',
        'employee_type',
        'interest',
        'joining_date',
        'leave_date',
        'account_no',
        'branch',
        'terms_condition_accept',
        'path',
        'signature_path'
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

}
