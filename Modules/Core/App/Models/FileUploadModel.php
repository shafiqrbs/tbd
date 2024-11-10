<?php

namespace Modules\Core\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class FileUploadModel extends Model
{
    use HasFactory;

    protected $table = 'cor_file_upload';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'domain_id',
        'file_type',
        'original_name',
        'file'
    ];

    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $fileUploads = self::where('domain_id',$domain['global_id'])
        ->select([
            'id',
            'file_type',
            'original_name',
            'file',
            'domain_id',
            DB::raw('DATE_FORMAT(created_at, "%d-%m-%Y") as created'),

        ]);

        if (isset($request['term']) && !empty($request['term'])){
            $fileUploads = $fileUploads->whereAny(['file_type','file','created_at'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['file_type']) && !empty($request['file_type'])){
            $fileUploads = $fileUploads->where('file_type',$request['file_type']);
        }

        if (isset($request['file']) && !empty($request['file'])){
            $fileUploads = $fileUploads->where('file',$request['file']);
        }

        $total  = $fileUploads->count();
        $entities = $fileUploads->skip($skip)
                        ->take($perPage)
                        ->orderBy('id','DESC')
                        ->get();

        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

    }

}
