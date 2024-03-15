<?php

namespace Modules\Inventory\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductBrandModel extends Model
{
    use HasFactory,Sluggable;

    protected $table = 'inv_brand';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'name',
        'slug',
        'status',
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }


    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function getEntityDropdown($domain)
    {
        $query = self::select(['name', 'slug', 'id'])
            ->where([['status', 1],['config_id', $domain['config_id']]]);
        return $query->get();
    }

    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $categories = self::where('config_id',$domain['config_id'])
            ->select([
                'id',
                'name',
                'slug',
                'parent as parent_id',
            ]);

        if (isset($request['type']) && $request['type'] === 'category'){
            $categories = $categories->addSelect([
                'p.name as parent_name'
            ]);
        }

        if (isset($request['term']) && !empty($request['term'])){
            $categories = $categories->whereAny(['name','slug'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['type']) && $request['type'] === 'category'){
            $categories = $categories->whereNotNull('parent');
        }
        if (isset($request['type']) && $request['type'] === 'parent' ){
            $categories = $categories->whereNull('parent');
        }


        $total  = $categories->count();
        $entities = $categories->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')
            ->get();

        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }


    public static function getCategoryIsDeletable($id)
    {
        $data = self::where('parent',$id)->get();
        if (sizeof($data)>0){
            return false;
        }else{
            return true;
        }
    }
}
