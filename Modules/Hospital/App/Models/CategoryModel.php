<?php

namespace Modules\Hospital\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;

class CategoryModel extends Model
{
    use HasFactory,Sluggable;

    protected $table = 'inv_category';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'name',
        'slug',
        'status',
        'is_private',
        'category_nature_id',
        'parent'
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
            $model->generate_id = self::generatedEventListener($model)['generateId'];
            $model->code = self::generatedEventListener($model)['code'];
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function generatedEventListener($model)
    {
        $patternCodeService = app(GeneratePatternCodeService::class);
        $params = [
            'config' => $model->config_id,
            'table' => 'inv_category'
        ];
        return $patternCodeService->categoryCode($params);
    }

    public static function getCategoryGroupDropdown($domain)
    {
        $query = self::select(['name', 'slug', 'id'])
            ->where([['status', 1],['config_id', $domain['config_id']]]);
        $query->whereNull('parent');
        return $query->get();
    }

    public static function getCategoryDropdown($domain,$type='all')
    {
        $query = self::select(['name', 'slug', 'id'])
            ->where([['status', 1],['config_id', $domain['config_id']]])->whereNull('inv_category.is_private')->whereNotNull('parent');
        return $query->get();
    }

    public static function getCategoryHospitalDropdown($domain,$type = 'service')
    {
        $query = self::select(['inv_category.name', 'inv_category.slug', 'inv_category.id'])
            ->leftjoin('inv_setting as p','p.id','=','inv_category.category_nature_id')
            ->where([['inv_category.status', 1],['inv_category.config_id', $domain['config_id']],['p.slug', $type]])->whereNull('inv_category.is_private')->whereNotNull('parent');
        return $query->get();
    }


    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $categories = self::where('inv_category.config_id',$domain['config_id'])->whereNull('inv_category.is_private')
            ->leftjoin('inv_category as p','p.id','=','inv_category.parent')
            ->leftjoin('inv_setting as inv_setting','inv_setting.id','=','inv_category.category_nature_id')
            ->select([
                'inv_category.id',
                'inv_category.name',
                'inv_setting.name as category_nature',
                'inv_category.slug',
                'inv_category.status',
                'inv_category.parent as parent_id',
            ]);

        if (isset($request['type']) && $request['type'] === 'category'){
            $categories = $categories->addSelect([
                'p.name as parent_name'
            ]);
            if (isset($request['parent']) && !empty($request['parent'])){
                $categories = $categories->where('p.name','LIKE','%'.$request['parent'].'%');
            }
        }

        if (isset($request['term']) && !empty($request['term'])){
            $categories = $categories->whereAny(['inv_category.name','inv_category.slug'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $categories = $categories->where('inv_category.name','LIKE','%'.$request['name'].'%');
        }

        if (isset($request['type']) && $request['type'] === 'category'){
            $categories = $categories->whereNotNull('inv_category.parent');
        }
        if (isset($request['type']) && $request['type'] === 'parent' ){
            $categories = $categories->whereNull('inv_category.parent');
        }


        $total  = $categories->count();
        $entities = $categories->skip($skip)
            ->take($perPage)
            ->orderBy('inv_category.name','DESC')
            ->get();

        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }

    public static function getCategoryIsParent($id)
    {
        $data = self::find($id);
        if (!$data->parent){
            return true;
        }else{
            return false;
        }
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
