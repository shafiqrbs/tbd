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

class CustomerRepository extends EntityRepository {

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

    public function searchPatientAutoComplete(GlobalOption $globalOption, $q, $type = 'NULL')
    {
        $query = $this->createQueryBuilder('e');
        $query->select('e.id as id');
        $query->addSelect('e.id as customer');
        $query->addSelect('CONCAT(e.customerId, \' - \',e.mobile, \' - \', e.name) AS text');
        $query->where($query->expr()->like("e.mobile", "'$q%'"  ));
        $query->orWhere($query->expr()->like("e.name", "'%$q%'"  ));
        $query->orWhere($query->expr()->like("e.customerId", "'%$q%'"  ));
        $query->andWhere("e.globalOption = :globalOption");
        $query->setParameter('globalOption', $globalOption->getId());
        $query->andWhere('e.status=1');
        $query->orderBy('e.name', 'ASC');
        $query->groupBy('e.mobile');
        $query->setMaxResults( '20' );
        return $query->getQuery()->getResult();

    }

    public function searchMobileAutoComplete(GlobalOption $globalOption, $q, $type = 'NULL')
    {
        $query = $this->createQueryBuilder('e');

        $query->select('e.mobile as id');
        $query->addSelect('e.id as customer');
        $query->addSelect('CONCAT(e.mobile, \'-\', e.name) AS text');
        $query->where($query->expr()->like("e.mobile", "'$q%'"  ));
        $query->andWhere("e.globalOption = :globalOption");
        $query->setParameter('globalOption', $globalOption->getId());
        $query->orderBy('e.mobile', 'ASC');
        $query->groupBy('e.mobile,e.name');
        $query->setMaxResults( '10' );
        return $query->getQuery()->getResult();

    }

    public function searchCustomerAutoComplete(GlobalOption $globalOption, $q, $type = 'NULL')
    {
        $query = $this->createQueryBuilder('e');
        $query->select('e.name as id');
        $query->addSelect('e.id as name');
        $query->addSelect('e.name as text');
        $query->where($query->expr()->like("e.mobile", "'$q%'"  ));
        $query->andWhere("e.globalOption = :globalOption");
        $query->setParameter('globalOption', $globalOption->getId());
        $query->orderBy('e.name', 'ASC');
        $query->groupBy('e.mobile,e.name');
        $query->setMaxResults( '10' );
        return $query->getQuery()->getResult();

    }

    public function searchAutoCompleteName(GlobalOption $globalOption, $q)
    {
        $query = $this->createQueryBuilder('e');
        $query->select('e.name as id');
        $query->addSelect('e.id as customer');
        $query->addSelect('e.name as text');
        $query->where($query->expr()->like("e.name", "'$q%'"  ));
        $query->andWhere("e.globalOption = :globalOption");
        $query->setParameter('globalOption', $globalOption->getId());
        $query->groupBy('e.name');
        $query->orderBy('e.name', 'ASC');
        $query->setMaxResults( '10' );
        return $query->getQuery()->getResult();

    }

    public function searchAutoCompleteCode(GlobalOption $globalOption, $q)
    {
        $query = $this->createQueryBuilder('e');

        $query->select('e.mobile as id');
        $query->addSelect('e.id as customer');
        $query->addSelect('e.customerId as text');
        //$query->addSelect('CONCAT(e.customerId, " - ", e.name) AS text');
        $query->where($query->expr()->like("e.customerId", "'$q%'"  ));
        $query->andWhere("e.globalOption = :globalOption");
        $query->setParameter('globalOption', $globalOption->getId());
        $query->orderBy('e.customerId', 'ASC');
        $query->setMaxResults( '10' );
        return $query->getQuery()->getResult();

    }



}
