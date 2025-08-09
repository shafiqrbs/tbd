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

    public static function getParticularDropdown($dropdownType)
    {

        return DB::table('hms_particular_mode')
            ->join('hms_particular_module','hms_particular_module.id','=','hms_particular_mode.particular_module_id')
            ->select([
                'hms_particular_mode.id',
                'hms_particular_mode.name',
                'hms_particular_mode.slug',
            ])
            ->where([
                ['hms_particular_module.slug',$dropdownType],
                ['hms_particular_mode.status',1]
            ])
            ->orderBy('hms_particular_mode.ordering')
            ->get();
    }


}
