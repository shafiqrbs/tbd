<?php

namespace Modules\Inventory\App\Repositories;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityRepository;
use Modules\Inventory\App\Entities\Product;
use Modules\Inventory\App\Entities\PurchaseItem;
use Modules\Inventory\App\Entities\StockInventoryHistory;
use Modules\Inventory\App\Entities\StockItem;
use Modules\Inventory\App\Entities\StockItemHistory;


/**
 * ItemTypeGroupingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StockInventoryHistoryRepository extends EntityRepository
{
    public function openingInventoryHistory(StockItemHistory $history,PurchaseItem $item){

        $em = $this->_em;
        $exist = $this->findOneBy(['stockItemHistory'=>$history->getId()]);
        if(empty($exist)){

            /* @var $stockItem StockItem */

            $stockItem =  $history->getStockItem();
            $entity = new StockInventoryHistory();
            $entity->setStockItemHistory($history);
            $entity->setQuantity($item->getQuantity());
            if($stockItem->getBrand()){
                $entity->setBrand($stockItem->getBrand()->getName());
            }
            if($stockItem->getProduct()->getCategory()){
                $entity->setCategory($stockItem->getProduct()->getCategory()->getName());
            }
            $entity->setPurchaseItem($item);
            $entity->setPrice($item->getPurchasePrice());
            $entity->setPurchasePrice($item->getPurchasePrice());
            $entity->setSalesPrice($item->getSalesPrice());
            $entity->setSubTotal($item->getSubTotal());
            $entity->setTotal($entity->getSubTotal());
            $entity->setCreatedAt(now());
            $entity->setUpdatedAt(now());
            $em->persist($entity);
            $em->flush();
        }

    }
}
