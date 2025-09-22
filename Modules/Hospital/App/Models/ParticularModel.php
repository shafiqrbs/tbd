<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\UserModel;
use Ramsey\Collection\Collection;

class ParticularModel extends Model
{
    use HasFactory;

    protected $table = 'hms_particular';
    public $timestamps = true;
    protected $guarded = ['id'];


    public function particularDetails()
    {
        return $this->hasOne(ParticularDetailsModel::class, 'particular_id', 'id');
    }

    public function investigationReportFormat()
    {
        return $this->hasMany(InvestigationReportFormatModel::class, 'particular_id', 'id');
    }

    public function treatmentMedicineFormat()
    {
        return $this->hasMany(TreatmentMedicineModel::class, 'treatment_template_id', 'id');
    }


    public static function getRecords($request,$domain){

        $config =  $domain['hms_config'];
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;

        $entity = self::where('hms_particular.config_id',$config)
            ->leftJoin('hms_particular_details','hms_particular_details.particular_id','=','hms_particular.id')
            ->join('hms_particular_type','hms_particular_type.id','=','hms_particular.particular_type_id')
            ->join('hms_particular_master_type','hms_particular_master_type.id','=','hms_particular_type.particular_master_type_id')
            ->leftJoin('inv_category','inv_category.id','=','hms_particular.category_id')
            ->leftJoin('hms_particular as room','room.id','=','hms_particular_details.room_id')
            ->leftJoin('hms_particular_mode as patientType','patientType.id','=','hms_particular_details.patient_type_id')
            ->leftJoin('hms_particular_mode as patientMode','patientMode.id','=','hms_particular_details.patient_mode_id')
            ->leftJoin('hms_particular_mode as genderMode','genderMode.id','=','hms_particular_details.gender_mode_id')
            ->leftJoin('hms_particular_mode as paymentMode','paymentMode.id','=','hms_particular_details.payment_mode_id')
            ->leftJoin('hms_particular_mode as treatmentMode','treatmentMode.id','=','hms_particular_details.treatment_mode_id')
            ->select([
                'hms_particular.id',
                'hms_particular.employee_id',
                'hms_particular.name',
                'hms_particular.display_name',
                'hms_particular.slug',
                'hms_particular.price',
                'hms_particular.status',
                'hms_particular.is_available',
                'hms_particular.opd_referred',
                'hms_particular.content',
                'inv_category.name as category',
                'room.name as room_name',
                'patientType.name as patient_type_name',
                'patientMode.name as patient_mode_name',
                'paymentMode.name as payment_mode_name',
                'genderMode.name as gender_mode_name',
                'treatmentMode.name as  treatment_mode_name',
                DB::raw('DATE_FORMAT(hms_particular.created_at, "%d-%M-%Y") as created'),
                'hms_particular_type.name as particular_type_name',
                'hms_particular_type.slug as particular_type_slug',
                'hms_particular_details.unit_id',
                'hms_particular_details.room_id as opd_room_id',
                'hms_particular_details.id as hms_particular_details_id',
            ]);

        if (!empty($request['term'])) {
             $term = trim($request['term']);
            $entity = $entity->where(function ($q) use ($term) {
                $q->where('hms_particular.name', 'LIKE', "%{$term}%")
                    ->orWhere('hms_particular.slug', 'LIKE', "%{$term}%")
                    ->orWhere('hms_particular_type.slug', 'LIKE', "%{$term}%")
                    ->orWhere('hms_particular_type.name', 'LIKE', "%{$term}%");
            });
        }

        if (isset($request['name']) && !empty($request['name'])){
            $entity = $entity->where('hms_particular.name','LIKE','%'.trim($request['name']));
        }

        if (isset($request['particular_type']) && !empty($request['particular_type'])){
            $entity = $entity->where('hms_particular_master_type.slug',$request['particular_type']);
        }

        if (isset($request['particular_type_id']) && !empty($request['particular_type_id'])){
            $entity = $entity->where('hms_particular.particular_type_id',$request['particular_type_id']);
        }

        $total  = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('hms_particular.ordering','ASC')
            ->get();

        $data = array('count'=>$total,'entities' => $entities);
        return $data;


    }

    public static function getParticularContentDropdown($domain,$dropdownType)
    {

        $config =  $domain['hms_config'];
        return DB::table('hms_particular')->where('hms_particular.config_id',$config)
            ->join('hms_particular_type','hms_particular_type.id','=','hms_particular.particular_type_id')
            ->join('hms_particular_master_type','hms_particular_master_type.id','=','hms_particular_type.particular_master_type_id')
            ->where('hms_particular_master_type.slug',$dropdownType)
            ->select([
                'hms_particular.id',
                'hms_particular.name',
                'hms_particular.display_name',
                'hms_particular.price',
                'hms_particular.slug',
                'hms_particular.content',
            ])
            ->orderBy('hms_particular.ordering','ASC')
            ->get();
    }

    public static function getTreatmentMedicine($domain,$request){

        $config =  $domain['hms_config'];
        $entity = self::where('hms_particular.config_id',$config)
            ->join('hms_particular_type','hms_particular_type.id','=','hms_particular.particular_type_id')
            ->join('hms_particular_master_type','hms_particular_master_type.id','=','hms_particular_type.particular_master_type_id')
            ->leftJoin('hms_particular_details','hms_particular_details.particular_id','=','hms_particular.id')
            ->leftJoin('hms_particular_mode as treatmentMode','treatmentMode.id','=','hms_particular_details.treatment_mode_id')
            ->select([
                'hms_particular.id',
                'hms_particular.name',
                'treatmentMode.name as  treatment_mode_name',
            ])->with('treatmentMedicineFormat');

        if (isset($request['particular_type']) && !empty($request['particular_type'])){
            $entity = $entity->where('hms_particular_master_type.slug',$request['particular_type']);
        }
        if (isset($request['treatment_mode']) && !empty($request['treatment_mode'])){
            $entity = $entity->where('treatmentMode.slug',$request['treatment_mode']);
        }
        $entities = $entity->get();
        return $entities;

    }


    public static function getDoctorNurseStaff($request,$domain)
     {

         self::doctorNurseStaff($domain, $userGroup = 'doctor');
         $userGroup = (isset($request['user_group']) and $request['user_group']) ? $request['user_group'] : '';
         if ($userGroup) {
             self::doctorNurseStaff($domain, $userGroup);
         }
     }

    public static function getParticularDropdown($domain,$dropdownType)
    {
        $config = $domain['hms_config'];
        return DB::table('hms_particular')
            ->join('hms_particular_type','hms_particular_type.id','=','hms_particular.particular_type_id')
            ->join('hms_particular_master_type','hms_particular_master_type.id','=','hms_particular_type.particular_master_type_id')
            ->select([
                'hms_particular.id',
                'hms_particular.name'
            ])
            ->where([
                ['hms_particular.config_id',$config],
                ['hms_particular_master_type.slug',$dropdownType],
                ['hms_particular.status',1]
            ])
            ->orderBy('hms_particular.name')
            ->get();
    }

    public static function doctorNurseStaff($domain,$userGroup)
    {

        $users = UserModel::with([
            'userGroup' => function ($q) {
                $q->select('id', 'name as user_group_name', 'slug as user_group_slug');
            }
        ])->where([
                'domain_id'  => $domain['id'],
                'user_group' => 'user',
            ])->get();
        $date =  new \DateTime("now");
        collect($users)->map(function ($user) use ($domain,$date) {
            if($user->userGroup){
                $parent = self::getParticularType($domain,$user->userGroup->user_group_slug);
                if($parent){
                    $entity = ParticularModel::updateOrCreate(
                        [
                            'config_id'             => $domain->hms_config,
                            'employee_id'           => $user->id,
                            'particular_type_id'    => $parent->id,
                        ],
                        [
                            'name'      => $user->name,
                            'display_name'      => $user->name,
                            'updated_at'    => $date,
                            'created_at'    => $date,
                        ]
                    );
                    ParticularDetailsModel::insertDoctor($entity);
                }
            }
        })->toArray();




    }

    public static function getParticularType($domain,$slug)
    {
        $config = $domain['hms_config'];
        return DB::table('hms_particular_type')
            ->join('hms_particular_master_type','hms_particular_master_type.id','=','hms_particular_type.particular_master_type_id')
            ->select([
                'hms_particular_type.id'
            ])
            ->where([
                ['hms_particular_type.config_id',$config],
                ['hms_particular_master_type.slug',$slug],
            ])->first();
    }

    public static function getDoctorNurseLabUser($user,$type)
    {

        $particular = DB::table('hms_particular')
            ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->select('hms_particular.id')
            ->where('hms_particular_master_type.slug', $type)
            ->where('hms_particular.employee_id', $user)
            ->first();

        return $particular;
    }


}
