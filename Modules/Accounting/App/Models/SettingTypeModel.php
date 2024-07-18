<?php

namespace Modules\Accounting\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SettingTypeModel extends Model
{
    use HasFactory;

    protected $table = 'acc_setting_type';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'slug',
        'status'
    ];

}
