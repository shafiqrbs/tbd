<?php

namespace Modules\Production\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SettingTypeModel extends Model
{
    use HasFactory;

    protected $table = 'pro_setting_type';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'slug',
        'status'
    ];

    public static function getDropdown($domain)
    {
        return self::where('status', 1)->select('id','name','slug')->orderBy('name')->get();
    }
}
