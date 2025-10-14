<?php

namespace Modules\Production\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductionItemAmendmentModel extends Model
{
    protected $table = 'pro_item_amendment';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['comment', 'production_item_id', 'created_by_id', 'content'];

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

    public static function generateAmendment($domain, $pro_item_id, $data, $comment)
    {
        self::create([
            'comment' => $comment,
            'production_item_id' => $pro_item_id,
            'created_by_id' => $domain['user_id'] ?? null,
            'content' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public static function getRecords($request)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 0;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        $query = self::query();
        $query->where('production_item_id', $request['pro_item_id']);
        $query->leftJoin('users','users.id','=','pro_item_amendment.created_by_id');
        $query->select([
                'pro_item_amendment.id',
                'pro_item_amendment.production_item_id',
                'pro_item_amendment.created_by_id',
                'users.name as created_by_name',
                'pro_item_amendment.content',
                'pro_item_amendment.comment',
                DB::raw('DATE_FORMAT(CONVERT_TZ(pro_item_amendment.created_at, "+00:00", "+06:00"), "%d-%M-%Y %r") as created_date')
            ]);

        $total = $query->count();
        $items = $query
            ->skip($skip)
            ->take($perPage)
            ->orderBy('id', 'DESC')
            ->get()
            ->map(function ($item) {
                $item->content = json_decode($item->content); // Decode JSON
                return $item;
            });


        $data = array('count' => $total, 'items' => $items);
        return $data;
    }

}
