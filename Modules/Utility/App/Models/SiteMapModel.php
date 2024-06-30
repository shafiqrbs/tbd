<?php

namespace Modules\Utility\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;


class SiteMapModel extends Model
{
    use HasFactory;

    protected $table = 'uti_sitemaps';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'module_id',
        'name',
        'description',
        'route_name',
        'slug_name',
        'slug',
        'status'
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(SettingModel::class);
    }


    public static function listWithSearch()
    {
        return self::with('module')
        ->select([
            'uti_sitemaps.*'
        ])
        ->where('uti_sitemaps.status', '1')
        ->get();
    }

    public static function getEntityDropdown()
    {
        return self::with('module')
            ->select([
                'uti_sitemaps.*'
            ])
            ->where('uti_sitemaps.status', '1')
            ->get();
    }


}
