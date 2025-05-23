<?php

namespace Modules\Accounting\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AccountHeadMasterModel extends Model
{
    use Sluggable;

    protected $table = 'acc_head_master';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'mother_account_id',
        'parent_id',
        'head_group',
        'code',
        'level',
        'slug',
        'mode',
        'status',
    ];

    protected $attributes = [
        'status' => true,
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

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $date = new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date = new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function getRecords($request)
    {
        try {
            // Pagination setup
            $perPage = max(1, (int)($request['offset'] ?? 50));  // Ensure at least 1 item per page
            $currentPage = max(1, (int)($request['page'] ?? 1)); // Ensure page is at least 1
            $skip = ($currentPage - 1) * $perPage;

            // Base query
            $query = self::leftJoin('acc_head_master as parent', 'parent.id', '=', 'acc_head_master.parent_id')
                ->leftJoin('acc_setting as mother', 'acc_head_master.mother_account_id', '=', 'mother.id')
                ->select([
                    'acc_head_master.id',
                    'acc_head_master.level',
                    'acc_head_master.name',
                    'acc_head_master.head_group',
                    'acc_head_master.slug',
                    'acc_head_master.code',
                    'parent.name as parent_name',
                    'mother.name as mother_name'
                ]);

            // Search term filter
            if (!empty($request['term'])) {
                $searchTerm = '%' . $request['term'] . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('acc_head_master.name', 'LIKE', $searchTerm)
                        ->orWhere('acc_head_master.slug', 'LIKE', $searchTerm);
                });
            }

            // Group filter
            if (!empty($request['group'])) {
               // $query->where('acc_head_master.head_group', $request['group']);
            }

            // Status filter
          //  $query->where('acc_head_master.status', 1);

            // Get total count before pagination
            $total = $query->count();

            // Apply pagination and ordering
            $entities = $query->skip($skip)
                ->take($perPage)
                ->orderBy('acc_head_master.id', 'DESC')
                ->get();

            return [
                'count' => $total,
                'entities' => $entities,
                'per_page' => $perPage,
                'current_page' => $currentPage
            ];

        } catch (\Exception $e) {
            // Log the error if needed
            // Log::error('Error fetching records: ' . $e->getMessage());

            return [
                'count' => 0,
                'entities' => [],
                'error' => 'An error occurred while fetching records'
            ];
        }
    }

    public static function getAccountHeadMasterDropdown()
    {
        return DB::table('acc_head_master')
            ->select([
                'acc_head_master.id',
                'acc_head_master.parent_id',
                DB::raw("CONCAT(acc_head_master.name, ' - ', acc_head_master.head_group) AS name"),
                'acc_head_master.slug',
                'acc_head_master.code',
                'acc_head_master.head_group',
                'acc_head_master.mode',
                'acc_head_master.level',
            ])
            ->whereIn('acc_head_master.head_group', ['head', 'sub-head'])
            ->get();
    }



}

