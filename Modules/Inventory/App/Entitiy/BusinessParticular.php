<?php

namespace Appstore\Bundle\BusinessBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Setting\Bundle\ToolBundle\Entity\ProductUnit;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * BusinessParticular
 *
 * @ORM\Table( name = "business_particular")
 * @ORM\Entity(repositoryClass="Appstore\Bundle\BusinessBundle\Repository\BusinessParticularRepository")
 */
class BusinessParticular
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
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessConfig", inversedBy="businessParticulars" , cascade={"detach","merge"} )
     **/
    private  $businessConfig;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\Category", inversedBy="businessParticulars" )
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $category;

     /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessBrand")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $brand;

     /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\WearHouse", inversedBy="businessParticulars" )
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $wearHouse;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessParticularType", inversedBy="businessParticulars" )
     **/
    private $businessParticularType;

    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessInvoiceParticular", mappedBy="businessParticular" )
     * @ORM\OrderBy({"id" = "ASC"})
     **/
    private $businessInvoiceParticulars;

    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessProductionExpense", mappedBy="productionItem" )
     * @ORM\OrderBy({"id" = "ASC"})
     **/
    private $businessProductionExpense;

    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessProductionExpense", mappedBy="productionElement" )
     * @ORM\OrderBy({"id" = "ASC"})
     **/
    private $businessProductionExpenseItem;


	/**
	 * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessProductionElement", mappedBy="businessParticular" )
	 **/
	private $productionElements;

    /**
	 * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessProduction", mappedBy="businessParticular" )
	 **/
	private $businessProductions;

    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessProductionElement", mappedBy="particular" )
     **/
    private $production;

    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessPurchaseItem", mappedBy="businessParticular" )
     **/
    private $businessPurchaseItems;

     /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessDamage", mappedBy="businessParticular" )
     **/
    private $businessDamages;


     /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessVendorStockItem", mappedBy="particular" )
     **/
    private $businessVendorStockItems;


    /**
     * @ORM\ManyToOne(targetEntity="Setting\Bundle\ToolBundle\Entity\ProductUnit", inversedBy="businessParticulars" )
     **/
    private  $unit;


    /**
     * @var string
     *
     * @ORM\Column(name="productType", type="string", length=20, nullable=true)
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
     * @ORM\Column(name="productionType", type="string", length=30,nullable = true)
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
     * @Assert\File(maxSize="8388608")
     */
    protected $file;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;



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
     * @param BusinessParticular $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return BusinessParticular
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
     * @return BusinessConfig
     */
    public function getBusinessConfig()
    {
        return $this->businessConfig;
    }

    /**
     * @param BusinessConfig $businessConfig
     */
    public function setBusinessConfig($businessConfig)
    {
        $this->businessConfig = $businessConfig;
    }

    /**
     * @return string
     */
    public function getProductType()
    {
        return $this->productType;
    }

    /**
     * @param string $productType
     */
    public function setProductType($productType)
    {
        $this->productType = $productType;
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
     * @return BusinessParticularType
     */
    public function getBusinessParticularType()
    {
        return $this->businessParticularType;
    }

    /**
     * @param BusinessParticularType $businessParticularType
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

}

