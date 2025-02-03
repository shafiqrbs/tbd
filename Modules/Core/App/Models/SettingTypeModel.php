<?php

namespace Modules\Core\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SettingTypeModel extends Model
{
    use HasFactory;

    protected $table = 'cor_setting_type';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'slug',
        'status'
    ];

    public static function getSettingTypeDropdown()
    {
      //  return self::where('status', 1)->where('is_show_setting_dropdown',1)->select('id','name','slug')->orderBy('name')->get();
        return self::where('status', 1)->select('id','name','slug')->orderBy('name')->get();
    }

}
