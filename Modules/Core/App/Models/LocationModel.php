<?php

namespace Modules\Core\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;


class LocationModel extends Model
{
    use HasFactory;

    protected $table = 'cor_locations';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = ['upazila,upazila_code'];

    public static function getLocationSearch($request){

        $term = $request['term'];
        $entities = self::select([
                'cor_locations.id',
                'cor_locations.upazila as name',
                'cor_locations.upazila_code as upazila_code',
                'cor_locations.district as district',
                'cor_locations.division as division',
            ]);
        if (!empty($term)) {
            $entities->where(function ($q) use ($term) {
                $q->where('cor_locations.upazila', 'LIKE', "%{$term}%")
                    ->orWhere('cor_locations.district', 'LIKE', "%{$term}%")
                    ->orWhere('cor_locations.division', 'LIKE', "%{$term}%");
            });
        }
        $entities = $entities
            ->orderBy('cor_locations.upazila', 'ASC')
            ->orderBy('cor_locations.district', 'ASC')
            ->get();
        return $entities;

    }

}
