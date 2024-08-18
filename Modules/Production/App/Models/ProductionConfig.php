<?php

namespace Modules\Production\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ProductionConfig extends Model
{
    use HasFactory;

    protected $table = 'pro_config';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = [
        'production_procedure_id',
        'consumption_method_id',
    ];

}
