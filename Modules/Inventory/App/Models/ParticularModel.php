<?php

namespace Modules\Inventory\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class ParticularModel extends Model
{
    use HasFactory;

    protected $table = 'inv_particular';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'setting_type',
        'name',
        'status'
    ];

    public function setting_type(): BelongsTo
    {
        return $this->belongsTo(ParticularTypeModel::class);
    }


    public static function getSettingDropdown($dropdownType)
    {
        return DB::table('inv_particular')
            ->join('inv_particular_type','inv_particular_type.id','=','inv_particular.particular_type_id')
            ->select([
                'inv_particular.id',
                'inv_particular.name',
                'inv_particular.slug',
                'inv_particular_type.name as type_name',
            ])
            ->where([
                ['inv_particular_type.slug',$dropdownType],
                ['inv_particular_type.status','1'],
                ['inv_particular.status','1'],
            ])
            ->get();
    }

    public static function getEntityDropdown($dropdownType)
    {
        return DB::table('inv_particular')
            ->join('inv_particular_type','inv_particular_type.id','=','inv_particular.particular_type_id')
            ->select([
                'inv_particular.id',
                'inv_particular.name',
                'inv_particular.slug',
                'inv_particular_type.name as type_name',
                'inv_particular_type.slug as type_slug',
            ])
            ->where([
                ['inv_particular_type.slug',$dropdownType],
                ['inv_particular_type.status','1'],
                ['inv_particular.status','1'],
            ])
            ->get();
    }

    public static function getProductUnitDropdown($domain,$dropdownType)
    {

        return DB::table('inv_particular')
            ->join('inv_particular_type','inv_particular_type.id','=','inv_particular.particular_type_id')
            ->select([
                'inv_particular.id',
                'inv_particular.name',
                'inv_particular.slug',
                'inv_particular_type.name as type_name',
            ])
            ->where([
                ['inv_particular_type.slug',$dropdownType],
                ['inv_particular.config_id',$domain['config_id']],
                ['inv_particular.status','1'],
            ])
            ->get();
    }


}
