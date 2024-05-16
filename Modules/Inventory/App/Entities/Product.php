<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Utility\App\Entities\ProductUnit;

/**
 * BusinessParticular
 *
 * @ORM\Table( name = "inv_product")
 * @ORM\Entity()
 */
class Product
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config", cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\Setting")
     **/
    private  $productType;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $category;

     /**
     * @ORM\ManyToOne(targetEntity="Brand")
      * @ORM\JoinColumn(name="brand_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")

      **/
    private $brand;

     /**
     * @ORM\ManyToOne(targetEntity="WearHouse")
      * @ORM\JoinColumn(name="wearhouse_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
      **/
    private $wearHouse;


     /**
     * @ORM\ManyToOne(targetEntity="Particular")
      * @ORM\JoinColumn(name="particular_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
      **/
    private $rackNo;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\ProductUnit")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $unit;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="size_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $size;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="color_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $color;



    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $alternativeName;



    /**
     * @Gedmo\Slug(fields={"name"})
     * @Doctrine\ORM\Mapping\Column(length=255,unique=false)
     */
    private $slug;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30,nullable = true)
     */
    private $productionType;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $quantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true,options={"default"="0"})
     */
    private $openingQuantity;


     /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $adjustmentQuantity;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable=true)
     */
    private $openingApprove = false;


    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $reorderQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $transferQuantity;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $stockIn = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchaseQuantity;

    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable=true)
     */
    private $salesQuantity;


    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable=true,options={"default"="0"})
     */
    private $remainingQuantity;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchaseReturnQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $salesReturnQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $damageQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $bonusQuantity = 0;



    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $bonusAdjustment = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $returnBonusQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $bonusPurchaseQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $bonusSalesQuantity = 0;


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
    private $avgPurchasePrice = 0;


	/**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
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
     * @ORM\Column( type="text", nullable=true)
     */
    private $content;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $salesPrice = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $discountPrice;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $commission = 0;

    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable=true)
     */
    private $minimumPrice = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $sorting;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer",  nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $particularCode;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $sku;


     /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $barcode;


     /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $modelNo;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" )
     */
    private $status= true;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $updatedAt;


}

