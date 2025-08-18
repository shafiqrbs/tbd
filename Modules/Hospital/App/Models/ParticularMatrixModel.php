<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Ramsey\Collection\Collection;

class ParticularMatrixModel extends Model
{
    use HasFactory;

    protected $table = 'hms_particular_mode_matrix';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];



    public static function getRecords($domain)
    {
        $config = $domain['hms_config'];
        $entities = self::select(['*'])->where([['hms_particular_mode_matrix.config_id',$config]]);
        $total  = $entities->count();
        $entities = $entities->get();
        $data = array('count'=>$total,'entities' => $entities);
        return $data;
    }


}
