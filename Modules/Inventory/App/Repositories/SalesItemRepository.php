<?php

namespace Modules\Inventory\App\Repositories;
use Doctrine\ORM\EntityRepository;
use Modules\Inventory\App\Entities\Sales;
use Modules\Inventory\App\Entities\SalesItem;
use Modules\Inventory\App\Entities\StockItem;


/**
 * ItemTypeGroupingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SalesItemRepository extends EntityRepository
{
    public function salesInsert($salesId, $datas)
    {

        $em = $this->_em;
        $sales = $em->getRepository(Sales::class)->find($salesId);
        foreach ($datas['items'] as $data) {

            $stockId            = (isset( $data['product_id']) and  $data['product_id']) ?  $data['product_id']:'';
            $sales_price        = (isset( $data['sales_price']) and  $data['sales_price']) ?  $data['sales_price']:0;
            $purchase_price     = (isset( $data['purchase_price']) and  $data['purchase_price']) ?  $data['purchase_price']:0;
            $quantity           = (isset( $data['quantity']) and  $data['quantity']) ?  $data['quantity']:0;
            $percent            = (isset( $data['percent']) and  $data['percent']) ?  $data['percent']:0;
            $price              = (isset( $data['price']) and  $data['price']) ?  $data['price']:0;

            /* @var $item StockItem */

            $item = $em->getRepository(StockItem::class)->find($stockId);
            $entity = new SalesItem();
            $entity->setSale($sales);
            $entity->setStockItem($item);
            $entity->setName($item->getName());
            $entity->setUom(($item->getProduct() and $item->getProduct()->getUnit()) ? $item->getProduct()->getUnit()->getName():null);
            $entity->setProduct($item->getProduct());
            $entity->setQuantity($quantity);
            $entity->setPercent($percent);
            $entity->setPrice($item->getPrice());
            $entity->setSalesPrice($sales_price);
            $entity->setPurchasePrice($purchase_price);
            $entity->setSubTotal($entity->getQuantity() * $entity->getSalesPrice());
            $entity->setPurchasePrice( $item->getPurchasePrice() );
            $entity->setCreatedAt(new \DateTime());
            $entity->setUpdatedAt(new \DateTime());
            $entity->setPurchasePrice( $item->getPurchasePrice() );
            if ($percent) {
                $entity->setDiscountPrice($this->itemDiscountPrice($percent,$item->getSalesPrice()));
            } else {
                $entity->setDiscountPrice($item->getSalesPrice());
            }
            $em->persist($entity);
            $em->flush();
        }
    }

    public function itemDiscountPrice($percent,$price)
    {
        $discountPrice = $price;
        if($percent){
            $discount = (($price * $percent)/100);
            $discountPrice = ($price - $discount);
        }
        return round($discountPrice,2);
    }

}
