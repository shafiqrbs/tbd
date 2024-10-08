<?php

namespace Modules\Production\App\Repositories;
use Doctrine\ORM\EntityRepository;
use Modules\Inventory\App\Entities\StockItem;
use Modules\Production\App\Entities\Config;
use Modules\Production\App\Entities\ProductionElement;
use Modules\Production\App\Entities\ProductionInventory;
use Modules\Production\App\Entities\ProductionItem;
use Modules\Production\App\Entities\ProductionItemAmendment;



/**
 * ProductionElementRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductionElementRepository extends EntityRepository
{
    public function insertProductionElement($data,$domain)
    {
        $em = $this->_em;
        $meterialItem = (isset($data['inv_stock_id']) and $data['inv_stock_id']) ?  $data['inv_stock_id']:'';
        $productionid = (isset($data['item_id']) and $data['item_id']) ?  $data['item_id']:'';
        $quantity = (isset($data['quantity']) and $data['quantity']) ?  $data['quantity']:0;
        $price = (isset($data['price']) and $data['price']) ?  $data['price']:'';
        $wastagePercent = (isset($data['percent']) and $data['percent']) ?  $data['percent']:'';
        $productionItem = $em->getRepository(ProductionItem::class)->find($productionid);
        $item = $em->getRepository(StockItem::class)->find($meterialItem);
        $existParticular = $em->getRepository(ProductionElement::class)->findOneBy(array('productionItem'=> $productionid ,'material' => $meterialItem));
        $findProConfig = $em->getRepository(Config::class)->find($domain['pro_config']);

        if(empty($existParticular) and $productionItem){

                $entity = new ProductionElement();
                $entity->setProductionItem($productionItem);
                $entity->setMaterial($item);
                $entity->setQuantity($quantity);
                $entity->setPrice($price);
                $entity->setSubTotal($entity->getPrice() * $entity->getQuantity());
                $entity->setConfig($findProConfig);
                $entity->setCreatedAt(now());
                if($wastagePercent){
                    $entity->setWastagePercent($wastagePercent);
                    $wsateQnt = $this->wastageCulculation($entity->getWastagePercent(),$entity->getQuantity());
                    $entity->setWastageQuantity($wsateQnt);
                    $entity->setWastageAmount($wsateQnt * $price);
                }elseif ($productionItem->getWastePercent()){
                    $entity->setWastagePercent($productionItem->getWastePercent());
                    $wsateQnt = $this->wastageCulculation($entity->getWastagePercent(),$entity->getQuantity());
                    $entity->setWastageQuantity($wsateQnt);
                    $entity->setWastageAmount($wsateQnt * $price);
                }
                $em->persist($entity);
                $em->flush();
                $this->updateProductionElementPrice( $productionItem );

            }else{

                $entity = $existParticular;
                $entity->setQuantity($quantity);
                $entity->setPrice($price);
                $entity->setSubTotal($entity->getPrice() * $entity->getQuantity());
                if($wastagePercent){
                $entity->setWastagePercent($wastagePercent);
                $wsateQnt = $this->wastageCulculation($entity->getWastagePercent(), $entity->getQuantity());
                $entity->setWastageQuantity($wsateQnt);
                $entity->setWastageAmount($wsateQnt * $entity->getPrice());
                }
                $entity->setUpdatedAt(now());
                $em->persist($entity);
                $em->flush();
                $this->updateProductionElementPrice( $productionItem );
            }

    }

    public function insertProductionAmendmentElement(ProductionItemAmendment $productionItem, $data)
    {
        $em = $this->_em;
        $existParticular = $this->_em->getRepository('TerminalbdProductionBundle:ProductionElement')->findOneBy(array('productionItem'=> $productionItem ,'material' => $data['productId']));
        if(empty($existParticular)){
            $entity = new ProductionElement();
            $item = $em->getRepository(StockItem::class)->find($data['productId']);
            $unit = !empty($item->getUnit() && !empty($item->getUnit()->getName())) ? $item->getUnit()->getName():'';
            $entity->setProductionItemAmendment($productionItem);
            $entity->setMaterial($item);
            $entity->setUom($unit);
            $entity->setQuantity($data['quantity']);
            $entity->setPrice($data['price']);
            $entity->setSubTotal($entity->getPrice() * $entity->getQuantity());
            if($data['wastagePercent']){
                $entity->setWastagePercent($data['wastagePercent']);
                $wsateQnt = $this->wastageCulculation($entity->getWastagePercent(),$entity->getQuantity());
                $entity->setWastageQuantity($wsateQnt);
                $entity->setWastageAmount($wsateQnt * $data['price']);
            }elseif ($productionItem->getWastePercent()){
                $entity->setWastagePercent($productionItem->getWastePercent());
                $wsateQnt = $this->wastageCulculation($entity->getWastagePercent(),$entity->getQuantity());
                $entity->setWastageQuantity($wsateQnt);
                $entity->setWastageAmount($wsateQnt * $data['price']);
            }
            $em->persist($entity);
            $em->flush();
        }
        $this->updateProductionAmendmentElementPrice( $productionItem );
    }

    private function wastageCulculation($percent, $quantity)
    {
        $wastageQnt = (($quantity * $percent) / 100);
        return $wastageQnt;
    }

    public function updateProductionElementPrice(ProductionItem $productionItem)
    {
        $em = $this->_em;
        $qb = $this->createQueryBuilder('e');
        $qb->select('sum(e.subTotal) as subTotal','sum(e.wastageAmount) as wastageAmount','sum(e.quantity) as materialQuantity','sum(e.wastageQuantity) as wastageQuantity');
        $qb->where('e.productionItem = :particular');
        $qb->setParameter('particular', $productionItem->getId());
        $qb->andWhere('e.status=1');
        $element = $qb->getQuery()->getOneOrNullResult();
        if($element and $element['subTotal'] > 0) {
            $productionItem->setMaterialAmount($element['subTotal']);
            $productionItem->setMaterialQuantity($element['materialQuantity']);
            $productionItem->setWasteMaterialQuantity($element['wastageQuantity']);
            $productionItem->setWasteAmount($element['wastageAmount']);
            $subTotal = round($productionItem->getMaterialAmount() + $productionItem->getValueAddedAmount() + $productionItem->getWasteAmount());
            $productionItem->setSubTotal($subTotal);
            $productionItem->setQuantity($productionItem->getMaterialQuantity() + $productionItem->getWasteMaterialQuantity());
        }else{
            $productionItem->setMaterialAmount(0);
            $productionItem->setMaterialQuantity(0);
            $productionItem->setWasteMaterialQuantity(0);
            $productionItem->setWasteAmount(0);
            $productionItem->setSubTotal(0);
        }
        $em->persist($productionItem);
        $em->flush();
    }

    public function updateProductionAmendmentElementPrice(ProductionItemAmendment $productionItem)
    {
        $em = $this->_em;
	    $qb = $this->createQueryBuilder('e');
	    $qb->select('sum(e.subTotal) as subTotal','sum(e.wastageAmount) as wastageAmount','sum(e.quantity) as materialQuantity','sum(e.wastageQuantity) as wastageQuantity');
	    $qb->where('e.productionItemAmendment = :particular');
	    $qb->setParameter('particular', $productionItem->getId());
        $element = $qb->getQuery()->getOneOrNullResult();
        if($element and $element['subTotal'] > 0) {
            $productionItem->setMaterialAmount($element['subTotal']);
            $productionItem->setMaterialQuantity($element['materialQuantity']);
            $productionItem->setWasteMaterialQuantity($element['wastageQuantity']);
            $productionItem->setWasteAmount($element['wastageAmount']);
            $productionItem->setSubTotal($productionItem->getMaterialAmount() + $productionItem->getValueAddedAmount());
            $em->persist($productionItem);
            $em->flush();
        }
    }

    public function particularProductionElements(ProductionItem $particular)
    {
        $entities = $particular->getElements();
        $data = '';
        $i = 1;

        /* @var $entity ProductionElement */

        foreach ($entities as $entity) {
            $data .= "<tr id='remove-{$entity->getId()}'>";
            $data .= "<td class='' >{$i}</td>";
            $data .= "<td class='' >{$entity->getMaterial()->getName()}</td>";
            $data .= "<td class='' >{$entity->getUom()}</td>";
            $data .= "<td class='text-right' >{$entity->getQuantity()}</td>";
            $data .= "<td class='text-right' >{$entity->getPrice()}</td>";
            $data .= "<td class='text-right' >{$entity->getSubTotal()}</td>";
            $data .= "<td class='text-right' >{$entity->getWastagePercent()}</td>";
            $data .= "<td class='text-right' >{$entity->getWastageQuantity()}</td>";
            $data .= "<td class='text-right' >{$entity->getWastageAmount()}</td>";
            $data .= "<td class='' ><a id='{$entity->getId()}' data-action='/en/production/build/{$particular->getId()}/{$entity->getId()}/element-delete' href='javascript:' class='btn btn-sm btn-transparent item-remove red-font' ><i class='fas fa fa-remove'></i></a></td>";
            $data .= '</tr>';
            $i++;
        }
        return $data;
    }

    public function particularProductionAmendmentElements(ProductionItemAmendment $particular)
    {
        $entities = $particular->getElements();
        $data = '';
        $i = 1;

        /* @var $entity ProductionElement */

        foreach ($entities as $entity) {
            $data .= "<tr id='remove-{$entity->getId()}'>";
            $data .= "<td class='' >{$i}</td>";
            $data .= "<td class='' >{$entity->getMaterial()->getName()}</td>";
            $data .= "<td class='' >{$entity->getUom()}</td>";
            $data .= "<td class='text-right' >{$entity->getQuantity()}</td>";
            $data .= "<td class='text-right' >{$entity->getPrice()}</td>";
            $data .= "<td class='text-right' >{$entity->getSubTotal()}</td>";
            $data .= "<td class='text-right' >{$entity->getWastagePercent()}</td>";
            $data .= "<td class='text-right' >{$entity->getWastageQuantity()}</td>";
            $data .= "<td class='text-right' >{$entity->getWastageAmount()}</td>";
            $data .= "<td class='' ><a id='{$entity->getId()}' data-action='/en/production/build/version/{$particular->getId()}/{$entity->getId()}/element-delete' href='javascript:' class='btn btn-sm btn-transparent item-remove red-font' ><i class='fas fa fa-remove'></i></a></td>";
            $data .= '</tr>';
            $i++;
        }
        return $data;
    }


    public function  getProductionAmendmentElement(ProductionItemAmendment $amendment , ProductionItem $item)
    {
        $em = $this->_em;
        if($item->getElements()) {
            /* @var $element ProductionElement */
            foreach ($item->getElements() as $element):
                $entity = new ProductionElement();
                $entity->setProductionItemAmendment($amendment);
                $entity->setMaterial($element->getMaterial());
                $entity->setUom($element->getUom());
                $entity->setQuantity($element->getQuantity());
                $entity->setPrice($element->getPrice());
                $entity->setSubTotal($element->getSubTotal());
                $entity->setWastagePercent($element->getWastagePercent());
                $entity->setWastageQuantity($element->getWastageQuantity());
                $entity->setWastageAmount($element->getWastageAmount());
                $entity->setWastageSubTotal($element->getWastageSubTotal());
                $em->persist($entity);
                $em->flush();
            endforeach;
        }
        return $amendment;

    }
}
