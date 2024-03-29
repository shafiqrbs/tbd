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
     **/
    private  $config;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\Setting")
     **/
    private  $productType;

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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\WearHouse")
     **/
    private $businessParticularType;


     /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private $rackNo;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\ProductUnit")
     **/
    private  $unit;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\ProductSize")
     **/
    private  $size;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\ProductColor")
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
     * @ORM\Column(type="float", nullable=true)
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
     * @ORM\Column( type="float", nullable=true)
     */
    private $remainingQuantity = 0;


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



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Set content
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set price
     * @param string $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }


    /**
     * @return string
     */
    public function getMinimumPrice()
    {
        return $this->minimumPrice;
    }

    /**
     * @param string $minimumPrice
     */
    public function setMinimumPrice($minimumPrice)
    {
        $this->minimumPrice = $minimumPrice;
    }


    /**
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }


    /**
     * @return string
     */
    public function getParticularCode()
    {
        return $this->particularCode;
    }

    /**
     * @param string $particularCode
     */
    public function setParticularCode($particularCode)
    {
        $this->particularCode = $particularCode;
    }


    /**
     * @return ProductUnit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param ProductUnit $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }


    /**
     * Sets file.
     *
     * @param Product $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return Product
     */
    public function getFile()
    {
        return $this->file;
    }

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'uploads/domain/'.$this->getBusinessConfig()->getGlobalOption()->getId().'/product/';
    }

    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->getFile()->getClientOriginalName()
        );

        // set the path property to the filename where you've saved the file
        $this->path = $this->getFile()->getClientOriginalName();

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }



    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }



    /**
     * @return string
     */
    public function getPurchasePrice()
    {
        return $this->purchasePrice;
    }

    /**
     * @param string $purchasePrice
     */
    public function setPurchasePrice($purchasePrice)
    {
        $this->purchasePrice = $purchasePrice;
    }

    /**
     * @return string
     */
    public function getDiscountPrice()
    {
        return $this->discountPrice;
    }

    /**
     * @param string $discountPrice
     */
    public function setDiscountPrice($discountPrice)
    {
        $this->discountPrice = $discountPrice;
    }



    public function getCodeName()
    {
        $codeName = $this->getSorting().' - '.$this->getName();
        return $codeName;
    }

    /**
     * @return string
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * @param string $sorting
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return Config
     */
    public function getBusinessConfig()
    {
        return $this->config;
    }

    /**
     * @param Config $businessConfig
     */
    public function setBusinessConfig($businessConfig)
    {
        $this->businessConfig = $config;
    }


    /**
     * @return BusinessPurchaseItem
     */
    public function getBusinessPurchaseItems()
    {
        return $this->businessPurchaseItems;
    }


    /**
     * @return BusinessDamage
     */
    public function getBusinessDamages()
    {
        return $this->businessDamages;
    }

    /**
     * @return BusinessInvoiceParticular
     */
    public function getBusinessInvoiceParticulars()
    {
        return $this->businessInvoiceParticulars;
    }

    /**
     * @return BusinessProductionExpense
     */
    public function getBusinessProductionExpense()
    {
        return $this->businessProductionExpense;
    }

    /**
     * @return BusinessProductionExpense
     */
    public function getBusinessProductionExpenseItem()
    {
        return $this->businessProductionExpenseItem;
    }

    /**
     * @return ParticularType
     */
    public function getBusinessParticularType()
    {
        return $this->businessParticularType;
    }

    /**
     * @param ParticularType $businessParticularType
     */
    public function setBusinessParticularType($businessParticularType)
    {
        $this->businessParticularType = $businessParticularType;
    }

    /**
     * @return string
     */
    public function getProductionType()
    {
        return $this->productionType;
    }

    /**
     * @param string $productionType
     */
    public function setProductionType(string $productionType)
    {
        $this->productionType = $productionType;
    }

	/**
	 * @return WearHouse
	 */
	public function getWearHouse() {
		return $this->wearHouse;
	}

	/**
	 * @param WearHouse $wearHouse
	 */
	public function setWearHouse( $wearHouse ) {
		$this->wearHouse = $wearHouse;
	}

	/**
	 * @return float
	 */
	public function getSalesPrice() {
		return $this->salesPrice;
	}

	/**
	 * @param float $salesPrice
	 */
	public function setSalesPrice( float $salesPrice ) {
		$this->salesPrice = $salesPrice;
	}

	/**
	 * @return BusinessProductionElement
	 */
	public function getProductionElements() {
		return $this->productionElements;
	}

	/**
	 * @return mixed
	 */
	public function getProduction() {
		return $this->production;
	}

	/**
	 * @return float
	 */
	public function getProductionSalesPrice(){
		return $this->productionSalesPrice;
	}

	/**
	 * @param float $productionSalesPrice
	 */
	public function setProductionSalesPrice( float $productionSalesPrice ) {
		$this->productionSalesPrice = $productionSalesPrice;
	}


	/**
	 * @return BusinessProduction
	 */
	public function getBusinessProductions() {
		return $this->businessProductions;
	}

    /**
     * @return BusinessVendorStockItem
     */
    public function getBusinessVendorStockItems()
    {
        return $this->businessVendorStockItems;
    }

    /**
     * @return float
     */
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * @param float $commission
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;
    }



    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return mixed
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param mixed $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param float $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return float
     */
    public function getOpeningQuantity()
    {
        return $this->openingQuantity;
    }

    /**
     * @param float $openingQuantity
     */
    public function setOpeningQuantity($openingQuantity)
    {
        $this->openingQuantity = $openingQuantity;
    }

    /**
     * @return int
     */
    public function getAdjustmentQuantity()
    {
        return $this->adjustmentQuantity;
    }

    /**
     * @param int $adjustmentQuantity
     */
    public function setAdjustmentQuantity($adjustmentQuantity)
    {
        $this->adjustmentQuantity = $adjustmentQuantity;
    }

    /**
     * @return bool
     */
    public function isOpeningApprove()
    {
        return $this->openingApprove;
    }

    /**
     * @param bool $openingApprove
     */
    public function setOpeningApprove($openingApprove)
    {
        $this->openingApprove = $openingApprove;
    }

    /**
     * @return int
     */
    public function getMinQuantity()
    {
        return $this->minQuantity;
    }

    /**
     * @param int $minQuantity
     */
    public function setMinQuantity($minQuantity)
    {
        $this->minQuantity = $minQuantity;
    }

    /**
     * @return float
     */
    public function getTransferQuantity()
    {
        return $this->transferQuantity;
    }

    /**
     * @param float $transferQuantity
     */
    public function setTransferQuantity($transferQuantity)
    {
        $this->transferQuantity = $transferQuantity;
    }

    /**
     * @return float
     */
    public function getStockIn()
    {
        return $this->stockIn;
    }

    /**
     * @param float $stockIn
     */
    public function setStockIn($stockIn)
    {
        $this->stockIn = $stockIn;
    }

    /**
     * @return float
     */
    public function getPurchaseQuantity()
    {
        return $this->purchaseQuantity;
    }

    /**
     * @param float $purchaseQuantity
     */
    public function setPurchaseQuantity($purchaseQuantity)
    {
        $this->purchaseQuantity = $purchaseQuantity;
    }

    /**
     * @return float
     */
    public function getSalesQuantity()
    {
        return $this->salesQuantity;
    }

    /**
     * @param float $salesQuantity
     */
    public function setSalesQuantity($salesQuantity)
    {
        $this->salesQuantity = $salesQuantity;
    }

    /**
     * @return float
     */
    public function getRemainingQuantity()
    {
        return $this->remainingQuantity;
    }

    /**
     * @param float $remainingQuantity
     */
    public function setRemainingQuantity($remainingQuantity)
    {
        $this->remainingQuantity = $remainingQuantity;
    }

    /**
     * @return float
     */
    public function getPurchaseReturnQuantity()
    {
        return $this->purchaseReturnQuantity;
    }

    /**
     * @param float $purchaseReturnQuantity
     */
    public function setPurchaseReturnQuantity($purchaseReturnQuantity)
    {
        $this->purchaseReturnQuantity = $purchaseReturnQuantity;
    }

    /**
     * @return float
     */
    public function getSalesReturnQuantity()
    {
        return $this->salesReturnQuantity;
    }

    /**
     * @param float $salesReturnQuantity
     */
    public function setSalesReturnQuantity($salesReturnQuantity)
    {
        $this->salesReturnQuantity = $salesReturnQuantity;
    }

    /**
     * @return float
     */
    public function getDamageQuantity()
    {
        return $this->damageQuantity;
    }

    /**
     * @param float $damageQuantity
     */
    public function setDamageQuantity($damageQuantity)
    {
        $this->damageQuantity = $damageQuantity;
    }

    /**
     * @return float
     */
    public function getBonusQuantity()
    {
        return $this->bonusQuantity;
    }

    /**
     * @param float $bonusQuantity
     */
    public function setBonusQuantity($bonusQuantity)
    {
        $this->bonusQuantity = $bonusQuantity;
    }

    /**
     * @return float
     */
    public function getBonusAdjustment()
    {
        return $this->bonusAdjustment;
    }

    /**
     * @param float $bonusAdjustment
     */
    public function setBonusAdjustment($bonusAdjustment)
    {
        $this->bonusAdjustment = $bonusAdjustment;
    }

    /**
     * @return float
     */
    public function getReturnBonusQuantity()
    {
        return $this->returnBonusQuantity;
    }

    /**
     * @param float $returnBonusQuantity
     */
    public function setReturnBonusQuantity($returnBonusQuantity)
    {
        $this->returnBonusQuantity = $returnBonusQuantity;
    }

    /**
     * @return float
     */
    public function getBonusPurchaseQuantity()
    {
        return $this->bonusPurchaseQuantity;
    }

    /**
     * @param float $bonusPurchaseQuantity
     */
    public function setBonusPurchaseQuantity($bonusPurchaseQuantity)
    {
        $this->bonusPurchaseQuantity = $bonusPurchaseQuantity;
    }

    /**
     * @return float
     */
    public function getBonusSalesQuantity()
    {
        return $this->bonusSalesQuantity;
    }

    /**
     * @param float $bonusSalesQuantity
     */
    public function setBonusSalesQuantity($bonusSalesQuantity)
    {
        $this->bonusSalesQuantity = $bonusSalesQuantity;
    }

    /**
     * @return float
     */
    public function getAvgPurchasePrice()
    {
        return $this->avgPurchasePrice;
    }

    /**
     * @param float $avgPurchasePrice
     */
    public function setAvgPurchasePrice($avgPurchasePrice)
    {
        $this->avgPurchasePrice = $avgPurchasePrice;
    }

    /**
     * @return float
     */
    public function getTloPrice()
    {
        return $this->tloPrice;
    }

    /**
     * @param float $tloPrice
     */
    public function setTloPrice($tloPrice)
    {
        $this->tloPrice = $tloPrice;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function getRackNo()
    {
        return $this->rackNo;
    }

    /**
     * @param mixed $rackNo
     */
    public function setRackNo($rackNo)
    {
        $this->rackNo = $rackNo;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param mixed $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getAlternativeName()
    {
        return $this->alternativeName;
    }

    /**
     * @param string $alternativeName
     */
    public function setAlternativeName($alternativeName)
    {
        $this->alternativeName = $alternativeName;
    }

    /**
     * @return int
     */
    public function getReorderQuantity()
    {
        return $this->reorderQuantity;
    }

    /**
     * @param int $reorderQuantity
     */
    public function setReorderQuantity($reorderQuantity)
    {
        $this->reorderQuantity = $reorderQuantity;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return string
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * @param string $barcode
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;
    }

    /**
     * @return string
     */
    public function getModelNo()
    {
        return $this->modelNo;
    }

    /**
     * @param string $modelNo
     */
    public function setModelNo($modelNo)
    {
        $this->modelNo = $modelNo;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

}

