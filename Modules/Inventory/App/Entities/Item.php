<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * Product
 *
 * @ORM\Table("inv_item")
 * @UniqueEntity(fields="name",message="This product name already existing,Please try another.")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\ItemRepository")
 */
class Item
{


    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
	 * @ORM\ManyToOne(targetEntity="Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
	 **/
	private  $config;


    /**
	 * @ORM\ManyToOne(targetEntity="Modules\NbrVatTax\App\Entities\Setting")
	 **/
	private  $inputTax;


    /**
	 * @ORM\OneToOne(targetEntity="Modules\Production\App\Entities\ProductionItem", mappedBy="item" )
	 **/
	private  $productionItem;


    /**
	 * @ORM\OneToMany(targetEntity="Damage", mappedBy="item" )
	 **/
	private  $damages;


     /**
	 * @ORM\OneToMany(targetEntity="ProductionIssue", mappedBy="item" )
	 **/
	private  $productionItems;

    /**
	 * @ORM\OneToMany(targetEntity="PurchaseReturnItem", mappedBy="item" )
	 **/
	private  $purchaseReturnItems;


    /**
	 * @ORM\ManyToOne(targetEntity="Particular", inversedBy="items" )
	 **/
	private  $productGroup;

    /**
	 * @ORM\ManyToOne(targetEntity="MasterItem", inversedBy="items" )
     * @Assert\NotBlank(message="Master product must be required")
     **/
	private  $masterItem;


    /**
	 * @ORM\ManyToOne(targetEntity="Category", inversedBy="items" )
	 **/
	private  $category;


	/**
	 * @ORM\ManyToOne(targetEntity="Particular", inversedBy="brandItems" )
	 **/
	private  $brand;

    /**
	 * @ORM\ManyToOne(targetEntity="Particular", inversedBy="typeItems" )
	 **/
	private  $productType;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\ProductUnit")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $unit;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $appModule;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Product must be required")
     */
    private $name;


    /**
     * @Gedmo\Slug(fields={"name"}, updatable=false, separator="-")
     * @ORM\Column(length=255, unique=true)
     */
    private $slug;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $assuranceType;


     /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $warningLabel;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50,nullable = true)
     */
    private $sku;

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=100,nullable = true)
     */
    private $manufacture;


    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=50, nullable=true)
     */
    private $code;


    /**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $quantity;

	 /**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $unitPrice;

	/**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $purchasePrice;


	/**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $salesPrice;

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
	private $minPrice;

    /**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $productionPrice;

    /**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $vatPercent;


     /**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $sdPercent;


    /**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $salesAvgPrice;


     /**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $purchaseAvgPrice;


    /**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $openingQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $minQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $maxQuantity;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $bonusQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $bonusPurchaseQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $bonusSalesQuantity;

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
    private $spoilQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $ongoingQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $orderCreateItem;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $remainingQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchaseQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchaseReturnQuantity=0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $salesQuantity = 0;

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
    private $disposalQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $assetsQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $assetsReturnQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $productionIssueQuantity;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $productionInventoryReturnQuantity;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $productionStockQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $productionStockReturnQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $productionBatchItemQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $productionBatchItemReturnQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $productionExpenseQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $productionExpenseReturnQuantity;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $barcode;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $status = true;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $taxOverride = false;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $itemPrefix;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private $serialGeneration = 'auto';


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $itemDimension;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isDelete = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", length=5)
     */
    private $serialFormat = 2;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @Assert\File(maxSize="8388608")
     */
    protected $file;


}

