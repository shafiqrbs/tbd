<?php

namespace Modules\Hospital\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Entities\Customer;
use Modules\Core\App\Models\LocationModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Inventory\App\Models\ConfigSalesModel;


class PatientModel extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = 'cor_customers';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [];

    public static function getAllCustomers()
    {
        $data = self::where(['status' => 1])->whereNotNull('mobile')->orderBy('name', 'ASC')
            ->select([
                'customers.id as id',
                'customers.name as name',
                'customers.mobile as mobile'
            ])
            ->get()->toArray();
        return $data;
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(LocationModel::class);
    }

    public function accountHead(): HasOne
    {
        return $this->hasOne(AccountHeadModel::class, 'customer_id');
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $date = new DateTime("now");
            $model->unique_id = self::quickRandom();
            $model->customer_id = self::customerEventListener($model)['generateId'];
            $model->code = self::customerEventListener($model)['code'];
            $model->created_at = $date;
            $model->updated_at = $date;
            $model->created = $date;
            $model->updated = $date;
            $model->status = true;
            $datetime = new DateTime("now");
        });

        self::updating(function ($model) {
            $date = new DateTime("now");
            $model->unique_id = self::quickRandom();
            $model->updated_at = $date;
            $model->updated = $date;
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

    public static function quickRandom($length = 32)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    public static function customerEventListener($model)
    {
        $patternCodeService = app(GeneratePatternCodeService::class);
        $params = [
            'domain' => $model->domain_id,
            'table' => 'cor_customers',
            'prefix' => 'TBH-',
        ];
        return $patternCodeService->PatientCode($params);
    }


    public static function getRecords($domain, $request)
    {

        $global = $domain['global_id'];
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        $customers = self::where('cor_customers.domain_id', $global)
            ->leftJoin('users', 'users.id', '=', 'cor_customers.marketing_id')
            ->leftJoin('cor_setting', 'cor_setting.id', '=', 'cor_customers.customer_group_id')
            ->leftJoin('cor_locations', 'cor_locations.id', '=', 'cor_customers.location_id')
            ->join('acc_head', 'acc_head.customer_id', '=', 'cor_customers.id')
            ->select([
                'cor_customers.id as id',
                'acc_head.amount as outstanding',
                'acc_head.id as account_id',
                'cor_customers.name as name',
                'cor_customers.mobile as mobile',
                'cor_customers.credit_limit as credit_limit',
                'cor_customers.discount_percent as discount_percent',
                'cor_setting.name as customer_group',
                'cor_setting.id as customer_group_id',
                'users.id as marketing_id',
                'users.name as marketing_name',
                'cor_customers.created_at as created_at'
            ]);

        if (isset($request['term']) && !empty($request['term'])) {
            $customers = $customers->whereAny(['cor_customers.name', 'cor_setting.name', 'users.name', 'cor_customers.mobile'], 'LIKE', '%' . $request['term'] . '%');
        }

        if (isset($request['name']) && !empty($request['name'])) {
            $customers = $customers->where('cor_customers.name', $request['name']);
        }

        if (isset($request['mobile']) && !empty($request['mobile'])) {
            $customers = $customers->where('cor_customers.mobile', $request['mobile']);
        }


        $totalUsers = $customers->count();
        $customers = $customers->skip($skip)
            ->take($perPage)
            ->orderBy('id', 'DESC')
            ->get();

        $data = array('count' => $totalUsers, 'entities' => $customers);
        return $data;
    }

    public static function getRecordsForLocalStorage($domain, $request)
    {

        $global = $domain['global_id'];

        $customers = self::where('cor_customers.domain_id', $global)
            ->leftJoin('users', 'users.id', '=', 'cor_customers.marketing_id')
            ->leftJoin('cor_locations', 'cor_locations.id', '=', 'cor_customers.location_id')
            ->select([
                'cor_customers.id',
                'cor_customers.name',
                'cor_customers.mobile',
                'cor_customers.address',
                'cor_customers.email',
                'cor_customers.code',
                'cor_customers.customer_id as customer_id',
                'cor_customers.alternative_mobile',
                'cor_customers.reference_id',
                'cor_customers.credit_limit',
                'cor_customers.customer_group_id',
                'cor_customers.unique_id',
                'cor_customers.slug',
                'cor_customers.marketing_id',
                'users.username as marketing_username',
                'users.email as marketing_email',
                'cor_customers.location_id',
                'cor_locations.name as location_name',
                DB::raw('DATE_FORMAT(cor_customers.created_at, "%d-%m-%Y") as created_date'),
                'cor_customers.created_at',
                DB::raw('"5000" as debit'),
                DB::raw('"2000" as credit'),
                DB::raw('"3000" as balance')
            ])
            ->orderBy('cor_customers.id', 'DESC')
            ->get();

        $data = array('entities' => $customers);
        return $data;
    }

    public static function getCustomerDetails($id)
    {
        return self::query()
            ->where('cor_customers.id', $id)
            ->leftJoin('users', 'users.id', '=', 'cor_customers.marketing_id')
            ->leftJoin('cor_setting', 'cor_setting.id', '=', 'cor_customers.customer_group_id')
            ->leftJoin('cor_locations', 'cor_locations.id', '=', 'cor_customers.location_id')
            ->select([
                'cor_customers.id as id',
                'cor_customers.name as name',
                'cor_customers.mobile as mobile',
                'cor_customers.credit_limit as credit_limit',
                'cor_setting.name as customer_group',
                'cor_setting.id as customer_group_id',
                'users.id as marketing_id',
                'users.name as marketing_name',
                DB::raw('DATE_FORMAT(cor_customers.created_at, "%d-%m-%Y") as created_date'),
                'cor_customers.discount_percent',
                'cor_customers.bonus_percent',
                'cor_customers.monthly_target_amount',
            ])
            ->first();
    }

    public static function insertSalesCustomer($domain, $input)
    {
        $domainConfig = ConfigSalesModel::where('config_id', $domain['inv_config'])->first();
        if ($domainConfig and $domainConfig->default_customer_group_id) {
            $customer['customer_group_id'] = $domainConfig->default_customer_group_id;
        }
        $customer['domain_id'] = $domain['domain_id'];
        $customer['name'] = ($input['customer_name']) ? $input['customer_name'] : '';
        $customer['mobile'] = ($input['customer_mobile']) ? $input['customer_mobile'] : '';
        $customer['email'] = ($input['customer_email']) ? $input['customer_email'] : '';
        return self::create($customer);


    }

    public static function uniqueCustomerCheck($domain, $mobile, $name)
    {
        return self::where([['name', $name], ['mobile', $mobile], ['domain_id', $domain]])->first();
    }

    public static function uniqueCustomerKey($domain,$data)
    {

        $name   = preg_replace('/\s+/', '', strtolower(trim($data['name'])));
        $mobile = trim($data['mobile']);
        $age    = (int) $data['year'];
        $uniqueKey = $domain . '_' . $name . '_' . $mobile . '_' . $age;
        return $uniqueKey;

    }
}
