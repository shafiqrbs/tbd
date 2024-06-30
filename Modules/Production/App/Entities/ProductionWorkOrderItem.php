<?php

namespace Modules\Production\App\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * ProductionWorkOrderItem
 *
 * @ORM\Table(name ="pro_work_order_item")
 * @ORM\Entity(repositoryClass="Modules\Production\App\Repositories\ProductionItemRepository")
 */
class ProductionWorkOrderItem
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="ProductionItem", inversedBy="productionWorkOrderItems" )
     **/
    private  $productionItem;



    /**
     * @ORM\OneToMany(targetEntity="ProductionBatchItem", mappedBy="workorderItem" )
     **/
    private  $productionWorkOrderItems;


    /**
     * @ORM\ManyToOne(targetEntity="ProductionWorkOrder", inversedBy="productionWorkOrderItems")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $productionWorkOrder;


    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", nullable = true)
     */
    private $amount;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $issueQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $receiveQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $damageQuantity;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $subTotal;



}

