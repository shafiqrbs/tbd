<?php

namespace Modules\Inventory\App\Repositories;
use Modules\Inventory\App\Entities\businessVendorStock;
use Modules\Core\App\Entities\User;
use Doctrine\ORM\EntityRepository;


/**
 * BusinessVendorStockRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BusinessVendorStockRepository extends EntityRepository
{



    protected function handleSearchBetween($qb,$data)
    {

        $grn = isset($data['grn'])? $data['grn'] :'';
        $vendor = isset($data['vendor'])? $data['vendor'] :'';
        $business = isset($data['name'])? $data['name'] :'';
        $brand = isset($data['brandName'])? $data['brandName'] :'';
        $mode = isset($data['mode'])? $data['mode'] :'';
        $vendorId = isset($data['vendorId'])? $data['vendorId'] :'';
        $startDate = isset($data['startDate'])? $data['startDate'] :'';
        $endDate = isset($data['endDate'])? $data['endDate'] :'';

        if(!empty($vendor)){
            $qb->join('e.vendor','v');
            $qb->andWhere($qb->expr()->like("v.companyName", "'%$vendor%'"  ));
        }
        if(!empty($vendorId)){
            $qb->join('e.vendor','v');
            $qb->andWhere("v.id = :vendorId")->setParameter('vendorId', $vendorId);
        }
        if(!empty($grn)){
            $qb->andWhere($qb->expr()->like("e.grn", "'%$grn%'"  ));
        }
        if (!empty($startDate) ) {
            $datetime = new \DateTime($data['startDate']);
            $start = $datetime->format('Y-m-d 00:00:00');
            $qb->andWhere("e.created >= :startDate");
            $qb->setParameter('startDate', $start);
        }

        if (!empty($endDate)) {
            $datetime = new \DateTime($data['endDate']);
            $end = $datetime->format('Y-m-d 23:59:59');
            $qb->andWhere("e.created <= :endDate");
            $qb->setParameter('endDate', $end);
        }
    }


    public function findWithSearch(User $user, $data)
    {
        $config = $user->getGlobalOption()->getBusinessConfig()->getId();
        $qb = $this->createQueryBuilder('e');
        $qb->where('e.businessConfig = :config')->setParameter('config', $config) ;
        $this->handleSearchBetween($qb,$data);
        $qb->orderBy('e.created','DESC');
        $qb->getQuery();
        return  $qb;
    }


    public function updateVendorStockTotalPrice(businessVendorStock $entity)
    {
        $em = $this->_em;
        $total = $em->createQueryBuilder()
            ->from('BusinessBundle:BusinessVendorStockItem','si')
            ->select('sum(si.subTotal) as total, SUM(si.quantity) as quantity ')
            ->where('si.businessVendorStock = :entity')
            ->setParameter('entity', $entity ->getId())
            ->getQuery()->getSingleResult();

        if($total['total'] > 0){
            $subTotal = $total['total'];
            $entity->setSubTotal($subTotal);
            $entity->setStockIn($total['quantity']);
        }else{
            $entity->setSubTotal(0);
            $entity->setStockIn(0);
        }

        $em->persist($entity);
        $em->flush();

        return $entity;

    }



    public function monthlyPurchase(User $user , $data =array())
    {

        $config =  $user->getGlobalOption()->getBusinessConfig()->getId();
        $compare = new \DateTime();
        $year =  $compare->format('Y');
        $year = isset($data['year'])? $data['year'] :$year;
        $sql = "SELECT MONTH (purchase.updated) as month,SUM(purchase.netTotal) AS total
                FROM business_vendor_stock as purchase
                WHERE purchase.businessConfig_id = :config AND purchase.process = :process  AND YEAR(purchase.updated) =:year
                GROUP BY month ORDER BY month ASC";
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('config', $config);
        $stmt->bindValue('process', 'Approved');
        $stmt->bindValue('year', $year);
        $stmt->execute();
        $result =  $stmt->fetchAll();
        return $result;


    }



    public function purchaseVendorReport(User $user , $data = array())
    {

        $global =  $user->getGlobalOption()->getId();
        $qb = $this->createQueryBuilder('e');
        $qb->join('e.accountVendor','t');
        $qb->select('t.companyName as companyName ,t.name as vendorName ,t.mobile as vendorMobile , sum(e.subTotal) as total');
        $qb->where('e.globalOption = :config');
        $qb->setParameter('config', $global);
        $qb->andWhere('e.process = :process');
        $qb->setParameter('process', 'approved');
        $this->handleSearchBetween($qb,$data);
        $qb->groupBy("e.accountVendor");
        $qb->orderBy("t.companyName",'ASC');
        $res = $qb->getQuery();
        return $result = $res->getArrayResult();

    }


}
