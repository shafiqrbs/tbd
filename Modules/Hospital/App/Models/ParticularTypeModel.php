<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Modules\Hospital\App\Entities\ParticularMasterType;
use Ramsey\Collection\Collection;

class ParticularTypeModel extends Model
{
    use HasFactory;

    protected $table = 'hms_particular_type';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public function particulars()
    {
        return $this->hasMany(ParticularModel::class, 'particular_type_id');
    }

    public function particularMatrix()
    {
        return $this->hasMany(ParticularMatrixModel::class, 'particular_type_id');
    }

    public function particularMaster()
    {
        return $this->hasOne(ParticularTypeMasterModel::class, 'id', 'particular_master_type_id');
    }


    public static function getRecords($domain)
    {
        $config = $domain['hms_config'];
        $entities = self::select(['*'])->with(['particulars' => function ($query) use($config) {
            $query->select(['*'])->where([['hms_particular.config_id',$config]]);
        }])->where([['hms_particular.config_id',$config]])->whereNotNull('particular_master_type_id')->orderBy('hms_particular_type.name',"ASC");
        $total  = $entities->count();
        $entities = $entities->get();
        $data = array('count'=>$total,'entities' => $entities);
        return $data;
    }

    public static function getParticularType($domain)
    {
        $config = $domain['hms_config'];
        $entities = self::select(['*'])
            ->with(['particularMatrix' => function ($query) use($config) {
                $query->select(['hms_particular_mode_matrix.particular_type_id as particular_type_id','hms_particular_mode_matrix.particular_mode_id as particular_mode_id'])->where([['hms_particular_mode_matrix.config_id',$config]]);
            }])
            ->where([['hms_particular_type.config_id',$config]])->whereNotNull('particular_master_type_id')->orderBy('hms_particular_type.name',"ASC");
        $entities = $entities->get();
        return $entities;
    }


}
