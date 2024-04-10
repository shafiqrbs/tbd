<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Utility\App\Entities\ProductUnit;

/**
 * BusinessParticular
 *
 * @ORM\Table( name = "inv_stock_item")
 * @ORM\Entity()
 */
class StockItem
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config" , cascade={"detach","merge"} )
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Category")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $category;

     /**
     * @ORM\ManyToOne(targetEntity="Brand")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $brand;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\WearHouse")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $wearHouse;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="businessParticulars" )
     **/
    private $product;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\ProductUnit")
     **/
    private  $unit;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $productType;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;


    /**
     * @Gedmo\Slug(fields={"name"})
     * @Doctrine\ORM\Mapping\Column(length=255,unique=false)
     */
    private $slug;


    /**
     * @var string
     *
     * @ORM\Column( type="string", length=30,nullable = true)
     */
    private $productionType;



    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float", nullable=true)
     */
    private $quantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="openingQuantity", type="float", nullable=true)
     */
    private $openingQuantity;


     /**
     * @var integer
     *
     * @ORM\Column(name="adjustmentQuantity", type="integer", nullable=true)
     */
    private $adjustmentQuantity;

    /**
     * @var boolean
     *
     * @ORM\Column(name="openingApprove", type="boolean",  nullable=true)
     */
    private $openingApprove = false;


    /**
     * @var integer
     *
     * @ORM\Column(name="minQuantity", type="integer", nullable=true)
     */
    private $minQuantity;

    /**
     * @var float
     *
     * @ORM\Column(name="transferQuantity", type="float", nullable=true)
     */
    private $transferQuantity;


    /**
     * @var float
     *
     * @ORM\Column(name="stockIn", type="float", nullable=true)
     */
    private $stockIn = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="purchaseQuantity", type="float", nullable=true)
     */
    private $purchaseQuantity;

    /**
     * @var float
     *
     * @ORM\Column(name="salesQuantity", type="float", nullable=true)
     */
    private $salesQuantity;


    /**
     * @var float
     *
     * @ORM\Column(name="remainingQuantity", type="float", nullable=true)
     */
    private $remainingQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="purchaseReturnQuantity", type="float", nullable=true)
     */
    private $purchaseReturnQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="salesReturnQuantity", type="float", nullable=true)
     */
    private $salesReturnQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="damageQuantity", type="float", nullable=true)
     */
    private $damageQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="bonusQuantity", type="float", nullable=true)
     */
    private $bonusQuantity = 0;



    /**
     * @var float
     *
     * @ORM\Column(name="bonusAdjustment", type="float", nullable=true)
     */
    private $bonusAdjustment = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="returnBonusQuantity", type="float", nullable=true)
     */
    private $returnBonusQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="bonusPurchaseQuantity", type="float", nullable=true)
     */
    private $bonusPurchaseQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="bonusSalesQuantity", type="float", nullable=true)
     */
    private $bonusSalesQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="purchasePrice", type="float", nullable=true)
     */
    private $purchasePrice = 0;


	/**
     * @var float
     *
     * @ORM\Column(name="avgPurchasePrice", type="float", nullable=true)
     */
    private $avgPurchasePrice = 0;


	/**
	 * @var float
	 *
	 * @ORM\Column(name="productionSalesPrice", type="float", nullable=true)
	 */
	private $productionSalesPrice;

    /**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $tloPrice;



    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;


    /**
     * @var float
     *
     * @ORM\Column(name="salesPrice", type="float", nullable=true)
     */
    private $salesPrice = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(name="discountPrice", type="float", nullable=true)
     */
    private $discountPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="commission", type="float", nullable=true)
     */
    private $commission = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="minimumPrice", type="float", nullable=true)
     */
    private $minimumPrice = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="sorting", type="string", length=5, nullable=true)
     */
    private $sorting;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="particularCode", type="string", length=10, nullable=true)
     */
    private $particularCode;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime",nullable=true)
     */
    private $created;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime",nullable=true)
     */
    private $updated;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;





}

