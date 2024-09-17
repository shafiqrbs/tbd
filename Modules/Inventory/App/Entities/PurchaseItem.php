<?php

namespace Modules\Inventory\App\Entities;

use Modules\Inventory\App\Entities\Product;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Utility\App\Entities\ProductUnit;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * PurchaseItem
 *
 * @ORM\Table(name ="inv_purchase_item")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\PurchaseItemRepository")
 */
class PurchaseItem
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
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $approvedBy;

    /**
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $stockItem;

    /**
     * @ORM\ManyToOne(targetEntity="Purchase")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $purchase;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private  $unit;

    /**
     * @ORM\ManyToOne(targetEntity="WearHouse")
     **/
    private  $wearhouse;

    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float",nullable=true)
     */
    private $quantity;

     /**
     * @var float
     *
     * @ORM\Column( type="float",nullable=true)
     */
    private $openingQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="float",nullable=true)
     */
    private $salesQuantity = 0;

    /**
     * @var integer
     *
     * @ORM\Column( type="float",nullable=true)
     */
    private $salesReturnQuantity;

    /**
     * @var integer
     *
     * @ORM\Column( type="float",nullable=true)
     */
    private $salesReplaceQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="float",nullable=true)
     */
    private $purchaseReturnQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="float",nullable=true)
     */
    private $damageQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $bonusQuantity = 0;


    /**
     * @var integer
     *
     * @ORM\Column( type="float",nullable=true)
     */
    private $remainingQuantity;


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
    private $actualPurchasePrice;


    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer", nullable = true)
     */
    private $code;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesPrice;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $subTotal;


    /**
     * @var string
     *
     * @ORM\Column(name="barcode", type="string",  nullable = true)
     */
    private $barcode;


    /**
     * @var string
     *
     * @ORM\Column(type="string",options={"default"="purchase"})
     */
    private $mode;


	/**
	 * @var float
	 *
	 * @ORM\Column(name="height", type="float", nullable=true)
	 */
	private $height;


	/**
	 * @var float
	 *
	 * @ORM\Column(name="width", type="float", nullable=true)
	 */
	private $width;


	/**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $particularType;


	/**
	 * @var float
	 *
	 * @ORM\Column(name="length", type="float", nullable=true)
	 */
	private $length;


	/**
	 * @var float
	 *
	 * @ORM\Column( type="float", nullable=true)
	 */
	private $subQuantity;

	/**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $totalQuantity;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="status", type="boolean", nullable=true)
	 */
	private $status=false;

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
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param mixed $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return mixed
     */
    public function getApprovedBy()
    {
        return $this->approvedBy;
    }

    /**
     * @param mixed $approvedBy
     */
    public function setApprovedBy($approvedBy)
    {
        $this->approvedBy = $approvedBy;
    }

    /**
     * @return Product
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
     * @return StockItem
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
    public function getPurchase()
    {
        return $this->purchase;
    }

    /**
     * @param mixed $purchase
     */
    public function setPurchase($purchase)
    {
        $this->purchase = $purchase;
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
     * @return mixed
     */
    public function getWearhouse()
    {
        return $this->wearhouse;
    }

    /**
     * @param mixed $wearhouse
     */
    public function setWearhouse($wearhouse)
    {
        $this->wearhouse = $wearhouse;
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
    public function getSalesQuantity()
    {
        return $this->salesQuantity;
    }

    /**
     * @param int $salesQuantity
     */
    public function setSalesQuantity($salesQuantity)
    {
        $this->salesQuantity = $salesQuantity;
    }

    /**
     * @return int
     */
    public function getSalesReturnQuantity()
    {
        return $this->salesReturnQuantity;
    }

    /**
     * @param int $salesReturnQuantity
     */
    public function setSalesReturnQuantity($salesReturnQuantity)
    {
        $this->salesReturnQuantity = $salesReturnQuantity;
    }

    /**
     * @return int
     */
    public function getSalesReplaceQuantity()
    {
        return $this->salesReplaceQuantity;
    }

    /**
     * @param int $salesReplaceQuantity
     */
    public function setSalesReplaceQuantity($salesReplaceQuantity)
    {
        $this->salesReplaceQuantity = $salesReplaceQuantity;
    }

    /**
     * @return int
     */
    public function getPurchaseReturnQuantity()
    {
        return $this->purchaseReturnQuantity;
    }

    /**
     * @param int $purchaseReturnQuantity
     */
    public function setPurchaseReturnQuantity($purchaseReturnQuantity)
    {
        $this->purchaseReturnQuantity = $purchaseReturnQuantity;
    }

    /**
     * @return int
     */
    public function getDamageQuantity()
    {
        return $this->damageQuantity;
    }

    /**
     * @param int $damageQuantity
     */
    public function setDamageQuantity($damageQuantity)
    {
        $this->damageQuantity = $damageQuantity;
    }

    /**
     * @return int
     */
    public function getBonusQuantity()
    {
        return $this->bonusQuantity;
    }

    /**
     * @param int $bonusQuantity
     */
    public function setBonusQuantity($bonusQuantity)
    {
        $this->bonusQuantity = $bonusQuantity;
    }

    /**
     * @return int
     */
    public function getRemainingQuantity()
    {
        return $this->remainingQuantity;
    }

    /**
     * @param int $remainingQuantity
     */
    public function setRemainingQuantity($remainingQuantity)
    {
        $this->remainingQuantity = $remainingQuantity;
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
    public function getActualPurchasePrice()
    {
        return $this->actualPurchasePrice;
    }

    /**
     * @param float $actualPurchasePrice
     */
    public function setActualPurchasePrice($actualPurchasePrice)
    {
        $this->actualPurchasePrice = $actualPurchasePrice;
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
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
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
    public function getParticularType()
    {
        return $this->particularType;
    }

    /**
     * @param float $particularType
     */
    public function setParticularType($particularType)
    {
        $this->particularType = $particularType;
    }

    /**
     * @return float
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param float $length
     */
    public function setLength($length)
    {
        $this->length = $length;
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
     * @return bool
     */
    public function isStatus()
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

