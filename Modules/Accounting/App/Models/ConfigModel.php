<?php

namespace Modules\Accounting\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;


class ConfigModel extends Model
{
    use HasFactory;

    protected $table = 'acc_config';
    public $timestamps = true;
    protected $guarded = ['id'];

}
