<?php

namespace Modules\Inventory\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * BusinessInvoiceParticular
 *
 * @ORM\Table( name = "inv_sales_item")
 * @ORM\Entity()
 */
class SalesItem
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @var string
     *
     * @ORM\Column(name="unit", type="string", length=225, nullable=true)
     */
    private $unit;

    /**
     * @var string
     *
     * @ORM\Column(name="particular", type="text", nullable=true)
     */
    private $particular;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="smallint",  nullable=true)
     */
    private $quantity = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $returnQnt = 0;

     /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $damageQnt = 0;

     /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $spoilQnt= 0;

     /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $bonusQnt = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $tloPrice;

     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $srCommission;

      /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $srCommissionTotal;

      /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $tloTotal;

     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $tloMode;

    /**
     * @var float
     *
     * @ORM\Column(name="height", type="float", nullable=true)
     */
    private $height = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="width", type="float", nullable=true)
     */
    private $width = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $subQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable=true)
     */
    private $totalQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchasePrice = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $subTotal = 0;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable = true)
     */
    private $startDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable = true)
     */
    private $endDate;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;


}

