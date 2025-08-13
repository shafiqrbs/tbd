<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Ramsey\Collection\Collection;

class ParticularTypeMasterModel extends Model
{
    use HasFactory;

    protected $table = 'hms_particular_master_type';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];


}
