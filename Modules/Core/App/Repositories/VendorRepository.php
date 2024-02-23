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

    public function searchAutoComplete($domain, $term, $type = 'NULL')
    {
        $entity = DB::table("cor_customers as e")
            ->select(DB::raw("CONCAT(e.mobile, ' - ', e.name) AS value"),'e.id as id')
            ->where('e.status', 1)
            ->where('e.global_option_id', $domain)
            ->where(function ($query) use ($term) {
                $query->orWhere('e.name','LIKE','%'.$term.'%')
                    ->orWhere('e.mobile','LIKE','%'.$term.'%');
            })
            ->limit(5000)->get();
        return $entity;
    }


}
