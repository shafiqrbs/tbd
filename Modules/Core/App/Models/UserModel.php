<?php

namespace Modules\Core\App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class UserModel extends Model
{
    use HasFactory;

    protected $table = 'users';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'username',
        'mobile',
        'email',
        'password',
        'is_delete',
        'domain_id',
        'employee_group_id',
        'email_verified_at',
        'enabled',
        'user_group',
    ];

    public static function getRecords($request,$domain){

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;

        $users = self::where('domain_id',$domain['global_id'])
        ->select([
            'id',
            'name',
            'username',
            'email',
            'mobile',
            'created_at'
        ]);

        if (isset($request['term']) && !empty($request['term'])){
            $users = $users->whereAny(['name','email','username','mobile'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $users = $users->where('name',$request['name']);
        }

        if (isset($request['mobile']) && !empty($request['mobile'])){
            $users = $users->where('mobile',$request['mobile']);
        }

        if (isset($request['email']) && !empty($request['email'])){
            $users = $users->where('email',$request['email']);
        }

        $total  = $users->count();
        $entities = $users->skip($skip)
            ->take($perPage)
            ->orderBy('id','DESC')
            ->get();

        $data = array('count'=>$total,'entities' => $entities);
        return $data;


    }


    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->email_verified_at = $date;
        });

         self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            $model->enabled = 1;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });

    }

    public static function getUserData($id)
    {
        $data = self::select([
            'dom_domain.id as global_id',
            'users.id as user_id',
            'inv_config.id as config_id',
            'inv_config.id as inv_config',
            'acc_config.id as acc_config',
            'pro_config.id as pro_config',
            'nbr_config.id as nbr_config'
        ])
            ->join('dom_domain','dom_domain.id','=','users.domain_id')
            ->leftjoin('inv_config','inv_config.domain_id','=','dom_domain.id')
            ->leftjoin('acc_config','acc_config.domain_id','=','dom_domain.id')
            ->leftjoin('pro_config','pro_config.domain_id','=','dom_domain.id')
            ->leftjoin('nbr_config','nbr_config.domain_id','=','dom_domain.id')
            ->where('users.id',$id)->first();
        return $data;
    }

    public static function getDomainData($id)
    {
        $data = self::select([
            'dom_domain.id as global_id',
            'users.id as user_id',
            'inv_config.id as config_id',
            'inv_config.id as inv_config',
            'inv_config_product.id as inv_config_product',
            'inv_config_discount.id as inv_config_discount',
            'inv_config_sales.id as inv_config_sales',
            'inv_config_purchase.id as inv_config_purchase',
            'acc_config.id as acc_config',
            'pro_config.id as pro_config',
            'nbr_config.id as nbr_config'
        ])
            ->join('dom_domain','dom_domain.id','=','users.domain_id')
            ->leftjoin('inv_config','inv_config.domain_id','=','dom_domain.id')
            ->leftjoin('acc_config','acc_config.domain_id','=','dom_domain.id')
            ->leftjoin('pro_config','pro_config.domain_id','=','dom_domain.id')
            ->leftjoin('nbr_config','nbr_config.domain_id','=','dom_domain.id')
            ->leftjoin('inv_config_product','inv_config_product.config_id','=','inv_config.id')
            ->leftjoin('inv_config_discount','inv_config_discount.config_id','=','inv_config.id')
            ->leftjoin('inv_config_sales','inv_config_sales.config_id','=','inv_config.id')
            ->leftjoin('inv_config_purchase','inv_config_purchase.config_id','=','inv_config.id')
            ->where('dom_domain.id',$id)->first();
        return $data;
    }

    public static function getRecordsForLocalStorage($request,$domain){
        $users = self::where('users.domain_id',$domain['global_id'])->whereNull('users.deleted_at')
            ->leftJoin('cor_user_role','cor_user_role.user_id','=','users.id')
            ->leftJoin('cor_user_transaction','cor_user_transaction.user_id','=','users.id')
            ->select([
                'users.id',
                'users.name',
                'users.username',
                'users.email',
                'users.mobile',
                'cor_user_transaction.max_discount',
                'cor_user_transaction.sales_target',
                DB::raw('DATE_FORMAT(users.created_at, "%d-%m-%Y") as created_date'),
                'users.created_at',
                'cor_user_role.access_control_role',
                'cor_user_role.android_control_role',
            ])
            ->orderByDesc('users.id')
            ->get();

        $data = array('entities' => $users);
        return $data;


    }

    public static function getRecordsForDomain(){
        $users = self::where('users.user_group','domain')->whereNull('users.deleted_at')
            ->join('dom_domain','dom_domain.id','=','users.domain_id')
            ->leftJoin('cor_user_role','cor_user_role.user_id','=','users.id')
            ->select([
                'users.id',
                'users.name',
                'users.username',
                'users.email',
                'users.mobile',
                DB::raw('DATE_FORMAT(users.created_at, "%d-%m-%Y") as created_date'),
                'dom_domain.id as domain_id',
                'dom_domain.company_name as company_name',
                'dom_domain.mobile as company_mobile',
                'dom_domain.status as company_status',
                'users.created_at'
            ])
            ->orderByDesc('users.id')
            ->get();

        $data = array('entities' => $users);
        return $data;


    }

    public static function showUserDetails($id)
    {
        $data = self::select([
            'users.id',
            'users.name',
            'users.username',
            'users.email',
            'users.domain_id',
            DB::raw('DATE_FORMAT(users.email_verified_at, "%d-%m-%Y") as email_verified_at'),
            DB::raw('COALESCE(DATE_FORMAT(users.created_at, "%d-%m-%Y"), "") as created'),
            DB::raw('COALESCE(DATE_FORMAT(users.updated_at, "%d-%m-%Y"), "") as updated'),
            'users.mobile',
            'users.enabled',
            'cor_user_profiles.alternative_email',
            'cor_user_profiles.location_id',
            'cor_user_profiles.designation_id',
            'cor_user_profiles.about_me',
            'cor_user_profiles.employee_group_id',
            'cor_user_profiles.department_id',
            'cor_user_profiles.address',
            'cor_user_transaction.max_discount',
            'cor_user_transaction.sales_target',
            DB::raw("CONCAT('".url('')."/uploads/core/user/profile/', cor_user_profiles.path) AS path"),
            DB::raw("CONCAT('".url('')."/uploads/core/user/signature/', cor_user_profiles.signature_path) AS signature_path")
        ])
            ->leftJoin('cor_user_profiles', 'cor_user_profiles.user_id', '=', 'users.id')
            ->leftJoin('cor_user_transaction', 'cor_user_transaction.user_id', '=', 'users.id')
            ->where('users.id', $id)
            ->first();
        return $data;

    }

    public static function getAccessControlRoles($userId, $type)
    {
        // Set the base group based on the provided type
        $baseGroup = ($type == 'access_control_role') ? ['Accounting', 'Sales & Purchase', 'Procurement','Production','Inventory & Product','Core & Master Data'] : ['Android Apps'];

        // Fetch user roles from the database
        $roles = DB::table('cor_user_role_group')
            ->select('group_name', 'role_name as id', 'role_label as label')
            ->where('user_id', $userId)
            ->where('role_type', $type)
            ->get()
            ->toArray();

        // Initialize the formatted roles array
        $formattedRoles = [];

        foreach ($roles as $role) {
            if (!isset($formattedRoles[$role->group_name])) {
                $formattedRoles[$role->group_name] = [
                    'Group' => $role->group_name,
                    'actions' => []
                ];
            }
            if ($role->id) {
                $formattedRoles[$role->group_name]['actions'][] = [
                    'id' => $role->id,
                    'label' => $role->label
                ];
            }
        }

        // Merge missing groups from the base group into the formatted roles
        $result = self::mergeGroups($baseGroup, $formattedRoles);

        // Convert associative array to indexed array for frontend consumption
        return array_values($result);
    }

    public static function mergeGroups($baseGroups, $formattedRoles)
    {
        // Iterate through each base group and ensure it exists in the formatted roles
        foreach ($baseGroups as $group) {
            if (!isset($formattedRoles[$group])) {
                $formattedRoles[$group] = [
                    'Group' => $group,
                    'actions' => []
                ];
            }
        }
        return $formattedRoles;
    }

}
