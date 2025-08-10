<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Ramsey\Collection\Collection;

class ParticularModel extends Model
{
    use HasFactory;

    protected $table = 'hms_particular';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public static function getParticularDropdown($domain,$dropdownType)
    {
        $config = $domain['hms_config'];
        return DB::table('hms_particular')
            ->join('hms_particular_type','hms_particular_type.id','=','hms_particular.particular_type_id')
            ->select([
                'hms_particular.id',
                'hms_particular.name'
            ])
            ->where([
                ['hms_particular.config_id',$config],
                ['hms_particular_type.slug',$dropdownType],
                ['hms_particular.status',1]
            ])
            ->orderBy('hms_particular.name')
            ->get();
    }


}
