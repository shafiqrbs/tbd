<?php

namespace Modules\Inventory\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * BusinessInvoiceParticular
 *
 * @ORM\Table( name = "inv_sales_item")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\SalesItemRepository")
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
     * @ORM\ManyToOne(targetEntity="Sales")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $sale;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;


    /**
     * @ORM\ManyToOne(targetEntity="Discount")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private  $discount;



    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="rack_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private  $rack;



     /**
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $stockItem;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private  $unit;

    /**
     * @ORM\ManyToOne(targetEntity="ProductUnitMeasurement")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private  $productUnitMeasurement;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=225, nullable=true)
     */
    private $measurementUnit;

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $uom;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

     /**
     * @var float
     *
     * @ORM\Column(name="receivable_discount_percent", type="float",  nullable=true)
     */
    private $receivableDiscountPercent = 0;


     /**
     * @var float
     *
     * @ORM\Column(name="receivable_discount_amount", type="float",  nullable=true)
     */
    private $receivableDiscountAmount = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float",  nullable=true)
     */
    private $quantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $returnQuantity = 0;

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
    private $spoilQuantity= 0;

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
    private $salesPrice = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchasePrice = 0;

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
    private $percent;

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
     * @ORM\Column(type="float", nullable=true)
     */
    private $height = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
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
     * @ORM\Column(type="float", nullable=true)
     */
    private $totalQuantity = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="assuranceType", type="string", length=50, nullable = true)
     */
    private $assuranceType;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="effectedDate", type="datetime", nullable=true)
     */
    private $effectedDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiredDate", type="datetime", nullable=true)
     */
    private $expiredDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="issueDate", type="datetime", nullable=true)
     */
    private $issueDate;

    /**
     * @var array
     *
     * @ORM\Column(name="internalSerial", type="simple_array",  nullable = true)
     */
    private $internalSerial;

    /**
     * @var string
     *
     * @ORM\Column(name="externalSerial", type="text",  nullable = true)
     */
    private $externalSerial;



    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $subTotal = 0;


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

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Warehouse")
     **/
    private  $warehouse;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getSale()
    {
        return $this->sale;
    }

    /**
     * @param mixed $sale
     */
    public function setSale($sale)
    {
        $this->sale = $sale;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getStockItem()
    {
        return $this->stockItem;
    }

    /**
     * @param mixed $stockItem
     */
    public function setStockItem($stockItem)
    {
        $this->stockItem = $stockItem;
    }

    /**
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param mixed $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return string
     */
    public function getUom()
    {
        return $this->uom;
    }

    /**
     * @param string $uom
     */
    public function setUom($uom)
    {
        $this->uom = $uom;
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
    public function getSalesPrice()
    {
        return $this->salesPrice;
    }

    /**
     * @param float $salesPrice
     */
    public function setSalesPrice($salesPrice)
    {
        $this->salesPrice = $salesPrice;
    }

    /**
     * @return float
     */
    public function getPurchasePrice()
    {
        return $this->purchasePrice;
    }

    /**
     * @param float $purchasePrice
     */
    public function setPurchasePrice($purchasePrice)
    {
        $this->purchasePrice = $purchasePrice;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @param float $percent
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;
    }

    /**
     * @return float
     */
    public function getDiscountPrice()
    {
        return $this->discountPrice;
    }

    /**
     * @param float $discountPrice
     */
    public function setDiscountPrice($discountPrice)
    {
        $this->discountPrice = $discountPrice;
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
     * @return float
     */
    public function getSrCommission()
    {
        return $this->srCommission;
    }

    /**
     * @param float $srCommission
     */
    public function setSrCommission($srCommission)
    {
        $this->srCommission = $srCommission;
    }

    /**
     * @return float
     */
    public function getSrCommissionTotal()
    {
        return $this->srCommissionTotal;
    }

    /**
     * @param float $srCommissionTotal
     */
    public function setSrCommissionTotal($srCommissionTotal)
    {
        $this->srCommissionTotal = $srCommissionTotal;
    }

    /**
     * @return float
     */
    public function getTloTotal()
    {
        return $this->tloTotal;
    }

    /**
     * @param float $tloTotal
     */
    public function setTloTotal($tloTotal)
    {
        $this->tloTotal = $tloTotal;
    }

    /**
     * @return string
     */
    public function getTloMode()
    {
        return $this->tloMode;
    }

    /**
     * @param string $tloMode
     */
    public function setTloMode($tloMode)
    {
        $this->tloMode = $tloMode;
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param float $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param float $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return float
     */
    public function getSubQuantity()
    {
        return $this->subQuantity;
    }

    /**
     * @param float $subQuantity
     */
    public function setSubQuantity($subQuantity)
    {
        $this->subQuantity = $subQuantity;
    }

    /**
     * @return float
     */
    public function getTotalQuantity()
    {
        return $this->totalQuantity;
    }

    /**
     * @param float $totalQuantity
     */
    public function setTotalQuantity($totalQuantity)
    {
        $this->totalQuantity = $totalQuantity;
    }

    /**
     * @return string
     */
    public function getAssuranceType()
    {
        return $this->assuranceType;
    }

    /**
     * @param string $assuranceType
     */
    public function setAssuranceType($assuranceType)
    {
        $this->assuranceType = $assuranceType;
    }

    /**
     * @return \DateTime
     */
    public function getEffectedDate()
    {
        return $this->effectedDate;
    }

    /**
     * @param \DateTime $effectedDate
     */
    public function setEffectedDate($effectedDate)
    {
        $this->effectedDate = $effectedDate;
    }

    /**
     * @return \DateTime
     */
    public function getExpiredDate()
    {
        return $this->expiredDate;
    }

    /**
     * @param \DateTime $expiredDate
     */
    public function setExpiredDate($expiredDate)
    {
        $this->expiredDate = $expiredDate;
    }

    /**
     * @return \DateTime
     */
    public function getIssueDate()
    {
        return $this->issueDate;
    }

    /**
     * @param \DateTime $issueDate
     */
    public function setIssueDate($issueDate)
    {
        $this->issueDate = $issueDate;
    }

    /**
     * @return array
     */
    public function getInternalSerial()
    {
        return $this->internalSerial;
    }

    /**
     * @param array $internalSerial
     */
    public function setInternalSerial($internalSerial)
    {
        $this->internalSerial = $internalSerial;
    }

    /**
     * @return string
     */
    public function getExternalSerial()
    {
        return $this->externalSerial;
    }

    /**
     * @param string $externalSerial
     */
    public function setExternalSerial($externalSerial)
    {
        $this->externalSerial = $externalSerial;
    }

    /**
     * @return float
     */
    public function getSubTotal()
    {
        return $this->subTotal;
    }

    /**
     * @param float $subTotal
     */
    public function setSubTotal($subTotal)
    {
        $this->subTotal = $subTotal;
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

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
    public function setConfig($config): void
    {
        $this->config = $config;
    }

    public function getReturnQuantity(): float|int
    {
        return $this->returnQuantity;
    }

    public function setReturnQuantity(float|int $returnQuantity): void
    {
        $this->returnQuantity = $returnQuantity;
    }

    public function getDamageQuantity(): float|int
    {
        return $this->damageQuantity;
    }

    public function setDamageQuantity(float|int $damageQuantity): void
    {
        $this->damageQuantity = $damageQuantity;
    }

    public function getSpoilQuantity(): float|int
    {
        return $this->spoilQuantity;
    }

    public function setSpoilQuantity(float|int $spoilQuantity): void
    {
        $this->spoilQuantity = $spoilQuantity;
    }

    public function getBonusQuantity(): float|int
    {
        return $this->bonusQuantity;
    }

    public function setBonusQuantity(float|int $bonusQuantity): void
    {
        $this->bonusQuantity = $bonusQuantity;
    }

    /**
     * @return mixed
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * @param mixed $warehouse
     */
    public function setWarehouse($warehouse): void
    {
        $this->warehouse = $warehouse;
    }








}

