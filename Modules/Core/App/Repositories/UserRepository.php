<?php
/**
 * Created by PhpStorm.
 * User: shafiq
 * Date: 10/9/15
 * Time: 8:05 AM
 */

namespace Modules\Core\App\Repositories;

use Doctrine\ORM\EntityRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Filters\CustomerFilter;
use Modules\Core\App\Models\UserModel;

class UserRepository extends EntityRepository {


    public function listWithSearch(array $queryParams = [])
    {

        $page = (isset($queryParams['page']) and $queryParams['page'] ) ? $queryParams['page']:1;
        $limit = (isset($queryParams['limit']) and $queryParams['limit'] ) ? $queryParams['limit']:200;
        $data = Cache::remember('users'.$page, 200, function() use ($queryParams,$limit){
            $queryBuilder = UserModel::where('isDelete',0)->select('id','username as name','email','created_at')->orderBy('created_at','DESC');
            $query = resolve(CustomerFilter::class)->getResults([
                'builder' => $queryBuilder,
                'params' => $queryParams,
                'limit' => $limit
            ]);
            return $query;
        });
        return $data;

    }



}
