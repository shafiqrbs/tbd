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
use Modules\Core\App\Models\CustomerModel;
use Modules\Domain\App\Entities\GlobalOption;

class VendorRepository extends EntityRepository {


    public function listWithSearch(array $queryParams = [])
    {

        $page = (isset($queryParams['page']) and $queryParams['page'] ) ? $queryParams['page']:1;
        $limit = (isset($queryParams['limit']) and $queryParams['limit'] ) ? $queryParams['limit']:200;
        $data = Cache::remember('Customer'.$page, 200, function() use ($queryParams,$limit){
            $queryBuilder = CustomerModel::with(['location'])->select('id','name','mobile','created_at')->orderBy('created_at','DESC');
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
