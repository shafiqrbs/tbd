<?php
/**
 * Created by PhpStorm.
 * User: shafiq
 * Date: 10/9/15
 * Time: 8:05 AM
 */

namespace Modules\Core\App\Repositories;

use Doctrine\ORM\EntityRepository;
use Illuminate\Support\Facades\DB;

class UserRepository extends EntityRepository {


    public function listWithSearch(array $queryParams = [])
    {
        $entity = DB::table("users as e")
            ->select(DB::raw("e.id as id,e.username as name"))->limit(5000)->get();
        return $entity;
    }


    public function searchAutoComplete($q)
    {
        $query = $this->createQueryBuilder('e');
        $query->select('e.id as id');
        $query->addSelect('username.name) AS text');
        $query->andWhere($query->expr()->like("e.username", "'$q%'"  ));
        $query->orderBy('e.username', 'ASC');
        $query->setMaxResults( '100' );
        return $query->getQuery()->getResult();

    }


}
