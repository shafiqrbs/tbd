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
        'email_verified_at',
        'enabled',
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
        $data = self::select(['dom_domain.id as global_id','users.id as user_id','inv_config.id as config_id','inv_config.id as inv_config','acc_config.id as acc_config','pro_config.id as pro_config'])
            ->join('dom_domain','dom_domain.id','=','users.domain_id')
            ->leftjoin('inv_config','inv_config.domain_id','=','dom_domain.id')
            ->leftjoin('acc_config','acc_config.domain_id','=','dom_domain.id')
            ->leftjoin('pro_config','pro_config.domain_id','=','dom_domain.id')
            ->where('users.id',$id)->first();
        return $data;
    }

    public static function getRecordsForLocalStorage($request,$domain){
        $users = self::where('domain_id',$domain['global_id'])->whereNull('deleted_at')
            ->select([
                'id',
                'name',
                'username',
                'email',
                'mobile',
                DB::raw('DATE_FORMAT(created_at, "%d-%m-%Y") as created_date'),
                'created_at'
            ])
            ->orderBy('id','DESC')
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
            'core_user_profiles.alternative_email',
            'core_user_profiles.location_id',
            'core_user_profiles.designation_id',
            'core_user_profiles.about_me',
            'core_user_profiles.employee_group_id',
            'core_user_profiles.department_id',
            'core_user_profiles.address',
            DB::raw("CONCAT('".url('')."/uploads/core/user/profile/', core_user_profiles.path) AS path"),
            DB::raw("CONCAT('".url('')."/uploads/core/user/signature/', core_user_profiles.signature_path) AS signature_path")
        ])
            ->leftJoin('core_user_profiles', 'core_user_profiles.user_id', '=', 'users.id')
            ->where('users.id', $id)
            ->first();
        return $data;

    }

    public static function getAccessControlRoles($userId, $type)
    {
        // Set the base group based on the provided type
        $baseGroup = ($type == 'access_control_role') ? ['Accounting', 'HR & Payroll'] : ['Android Accounting', 'Android HR & Payroll'];

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
