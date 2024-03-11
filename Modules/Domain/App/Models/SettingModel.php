<?php

namespace Modules\Domain\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Inventory\App\Models\SettingTypeModel;

class SettingModel extends Model
{
    use HasFactory;

    protected $table = 'dom_settings';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'setting_type',
        'name',
        'status'
    ];

    public function setting_type(): BelongsTo
    {
        return $this->belongsTo(SettingTypeModel::class);
    }



}
