<?php
/**
 * Created by PhpStorm.
 * User: shafiq
 * Date: 10/9/15
 * Time: 8:05 AM
 */

namespace Modules\Core\App\Repositories;

use Doctrine\ORM\EntityRepository;
use Modules\Core\App\Filters\CustomerFilter;
use Modules\Core\App\Models\CustomerModel;

class CustomerRepository extends EntityRepository {



    public function listWithSearch(array $queryParams = [])
    {
        $queryBuilder = CustomerModel::with(['location'])->select('id','name','mobile','created_at')->latest();
        $query = resolve(CustomerFilter::class)->getResults([
            'builder' => $queryBuilder,
            'params' => $queryParams
        ]);
        return $query;
    }

  /*  public function findWithSearch()
    {
        $qb = $this->createQueryBuilder('customer');
        $qb->select('customer.id','customer.name','customer.mobile');
        $qb->where("customer.status =1");
      //  $qb->andWhere("customer.globalOption = :globalOption")->setParameter('globalOption', $globalOption);
      //  $qb->andWhere("customer.name != :name")->setParameter('name', 'Default');
      //  $qb->andWhere("customer.mobile IS NOT NULL");
     //   $qb->getMaxResults(10);
       // $this->handleSearchBetween($qb,$data);
        $qb->orderBy('customer.created','DESC');
        $results =$qb->getQuery()->getArrayResult();
        return  $results;

    }*/


}
