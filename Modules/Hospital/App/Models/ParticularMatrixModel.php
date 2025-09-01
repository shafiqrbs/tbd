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
        $entities = self::select(
            'hms_particular_mode_matrix.*',
            'hms_particular_mode.name as mode_name',
            'hms_particular_mode.slug as mode_slug'
        )
            ->join(
                'hms_particular_mode',
                'hms_particular_mode.id',
                '=',
                'hms_particular_mode_matrix.particular_mode_id'
            )
            ->where('hms_particular_mode_matrix.config_id', $config)
            ->with([
                'particularType.particulars' => function ($query) use ($config) {
                    $query->select('hms_particular.*') // ⚠️ include foreign key for relation
                    ->where('hms_particular.config_id', $config)
                    ->groupBy('hms_particular.name');
                }
            ]);
        $total  = $entities->count();
        $entities = $entities->get();
        $data = array('count' => $total,'entities' => $entities);
        return $data;
    }

    public function particularType()
    {
        return $this->belongsTo(ParticularTypeModel::class, 'particular_type_id');
    }

    public static function getOperationParticularType($domain,$id){

        $config = $domain['hms_config'];
        $entities = self::select(['*'])->where([
            ['hms_particular_mode_matrix.config_id',$config],
            ['hms_particular_mode_matrix.particular_module_id',$id]
        ]);
        $entities = $entities->get();
        return $entities;

    }


}
