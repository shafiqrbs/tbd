<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Ramsey\Collection\Collection;

class ParticularModuleModel extends Model
{
    use HasFactory;

    protected $table = 'hms_particular_module';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public function modes()
    {
        return $this->hasMany(ParticularModeModel::class, 'particular_module_id');
    }


    public static function getRecords()
    {
        $slugs = [];

        $records = ParticularModuleModel::all();
        foreach ($records as $record):
            $slugs[] = $record->slug;
        endforeach;
        $entities = ParticularModuleModel::with('modes')
            ->whereIn('slug', $slugs)
            ->get()
            ->keyBy('slug'); // <-- use slug as key
        return $entities;


    }

    public static function getParentChild()
    {
        $entities = self::select(
            'hms_particular_module.id',
            'hms_particular_module.name',
            'hms_particular_module.slug'
        )
            ->orderBy('hms_particular_module.name', 'ASC')
            ->with(['modes']);
        $entities = $entities->get();
        $records = [];
        foreach ($entities as $entity):
            $records[$entity->slug] = $entity;
        endforeach;
        return $records;
    }


}
