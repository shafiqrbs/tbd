<?php

namespace Modules\Utility\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Inventory\App\Models\SettingTypeModel;

class CurrencyModel extends Model
{
    use HasFactory;

    protected $table = 'uti_currencies';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'code',
        'symbol',
        'status'
    ];

}
