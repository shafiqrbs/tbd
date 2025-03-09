<?php

namespace Modules\Core\App\Models;
use Illuminate\Database\Eloquent\Model;
class WarehouseModel extends Model
{
    protected $table = 'cor_warehouses';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'location',
        'mobile',
        'address',
        'email',
        'contract_person',
        'domain_id',
        'setting_id',
        'is_delete',
        'status'
    ];

    public static function getRecords($request, $domain)
    {
        $page = (!empty($request['page']) && is_numeric($request['page'])) ? max(0, $request['page'] - 1) : 0;
        $perPage = (int) ($request['offset'] ?? 10);
        $skip = $page * $perPage;

        // Default domain ID if missing
        $domainId = $domain['global_id'] ?? 0;

        $warehouses = self::where([['domain_id', $domainId],['is_delete',0],['status',1]])
            ->select(['id', 'name', 'location', 'contract_person', 'email', 'mobile', 'address', 'created_at'])
            ->when(!empty($request['term']), function ($query) use ($request) {
                $query->whereAny(['name', 'email', 'contract_person', 'mobile', 'location', 'address'], 'LIKE', "%{$request['term']}%");
            })
            ->when(!empty($request['name']), fn($q) => $q->where('name', $request['name']))
            ->when(!empty($request['mobile']), fn($q) => $q->where('mobile', $request['mobile']))
            ->when(!empty($request['contract_person']), fn($q) => $q->where('contract_person', $request['contract_person']))
            ->when(!empty($request['location']), fn($q) => $q->where('location', $request['location']));

        $total = $warehouses->clone()->count();

        $entities = $warehouses->orderBy('id', 'DESC')->skip($skip)->take($perPage)->get();

        return [
            'success' => true,
            'message' => 'Warehouses fetched successfully',
            'data' => [
                'count' => $total,
                'entities' => $entities
            ]
        ];
    }



    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            $model->updated_at = $date;
            $model->status = true;
            $model->is_delete = false;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });

    }

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

}
