<?php

namespace Modules\Accounting\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class AccountJournalItemModel extends Model
{
    use HasFactory;

    protected $table = 'acc_journal_item';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'account_journal_id',
        'account_head_id',
        'account_sub_head_id',
        'amount',
        'debit',
        'credit',
        'mode',
    ];

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

    public static function insertCustomerJournalItem($config, $entity)
    {

        self::create(
            [
                'name' => $entity['name'],
                'customer_id' => $entity['id'],
                'config_id' => $config
            ]
        );
        AccountJournalItemModel::insertCustomerJournalVoucher($entity);
    }


    public static function getLedger($domain)
    {
        $query = self::select(['name', 'slug', 'id'])
            ->where([['status', 1], ['config_id', $domain['config_id']]]);
        $query->whereNotNull('parent');
        return $query->get();
    }

    public static function getAccountSubHead($domain)
    {
        $query = self::select(['name', 'slug', 'id'])
            ->where([['status', 1], ['config_id', $domain['config_id']]]);
        return $query->get();
    }

    public static function getAccountHead($domain)
    {
        $query = self::select(['name', 'slug', 'id'])
            ->where([['status', 1], ['config_id', $domain['config_id']]]);
        return $query->get();
    }


    public static function getRecords($request, $domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 0;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;
        $entity = self::where('acc_head.config_id', $domain['acc_config_id'])
            ->select([
                'acc_head.id',
                'acc_head.name'
            ]);

        if (isset($request['term']) && !empty($request['term'])) {
            $entity = $entity->whereAny(
                ['acc_head.name', 'acc_head.slug'], 'LIKE', '%' . $request['term'] . '%');
        }
        $total = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('acc_head.id', 'DESC')
            ->get();

        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }
}

