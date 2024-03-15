<?php

namespace Modules\Utility\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SettingTypeModel extends Model
{
    use HasFactory;

    protected $table = 'uti_setting_types';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'slug',
        'status'
    ];

}
