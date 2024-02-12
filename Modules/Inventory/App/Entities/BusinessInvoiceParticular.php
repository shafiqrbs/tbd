<?php

namespace Modules\Inventory\App\Entities;

use Modules\Core\App\Entities\Vendor;
use Doctrine\ORM\Mapping as ORM;


/**
 * BusinessInvoiceParticular
 *
 * @ORM\Table( name = "inv_invoice_particular")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\BusinessInvoiceParticularRepository")
 */
class BusinessInvoiceParticular
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessInvoice", inversedBy="businessInvoiceParticulars")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"id" = "ASC"})
     **/
    private $businessInvoice;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessAndroidProcess")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $androidProcess;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessParticular", inversedBy="businessInvoiceParticulars", cascade={"persist"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $businessParticular;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Vendor", inversedBy="businessInvoiceParticulars", cascade={"persist"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $vendor;

    /**
     * @ORM\OneToMany(targetEntity="Modules\Inventory\App\Entities\BusinessProductionExpense", mappedBy="businessInvoiceParticular", cascade={"persist"} )
     **/
    private $businessProductionExpense;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessVendorStockItem", inversedBy="businessInvoiceParticulars", cascade={"persist"} )
     **/
    private $vendorStockItem;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessBatchParticular")
     **/
    private $batchItem;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\WearHouse")
     **/
    private $wearhouse;

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
     * @ORM\Column(name="returnQnt", type="smallint", nullable=true)
     */
    private $returnQnt = 0;

     /**
     * @var integer
     *
     * @ORM\Column(name="damageQnt", type="smallint", nullable=true)
     */
    private $damageQnt = 0;

     /**
     * @var integer
     *
     * @ORM\Column(name="spoilQnt", type="smallint", nullable=true)
     */
    private $spoilQnt= 0;

     /**
     * @var integer
     *
     * @ORM\Column(name="bonusQnt", type="smallint", nullable=true)
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
     * @ORM\Column(name="subQuantity", type="float", nullable=true)
     */
    private $subQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="totalQuantity", type="float", nullable=true)
     */
    private $totalQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="purchasePrice", type="float", nullable=true)
     */
    private $purchasePrice = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="subTotal", type="float", nullable=true)
     */
    private $subTotal = 0;

    /**
     * @var \DateTime
     * @ORM\Column(name="startDate", type="datetime", nullable = true)
     */
    private $startDate;

    /**
     * @var \DateTime
     * @ORM\Column(name="endDate", type="datetime", nullable = true)
     */
    private $endDate;


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
     * @return BusinessParticular
     */
    public function getBusinessParticular()
    {
        return $this->businessParticular;
    }

    /**
     * @param BusinessParticular $businessParticular
     */
    public function setBusinessParticular($businessParticular)
    {
        $this->businessParticular = $businessParticular;
    }

    /**
     * @return BusinessInvoice
     */
    public function getBusinessInvoice()
    {
        return $this->businessInvoice;
    }

    /**
     * @param BusinessInvoice $businessInvoice
     */
    public function setBusinessInvoice($businessInvoice)
    {
        $this->businessInvoice = $businessInvoice;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return string
     */
    public function getParticular()
    {
        return $this->particular;
    }

    /**
     * @param string $particular
     */
    public function setParticular($particular)
    {
        $this->particular = $particular;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
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
     * @return float
     */
    public function getPurchasePrice()
    {
        return $this->purchasePrice;
    }

    /**
     * @param float $purchasePrice
     */
    public function setPurchasePrice( $purchasePrice)
    {
        $this->purchasePrice = $purchasePrice;
    }

    /**
     * @return BusinessProductionExpense
     */
    public function getBusinessProductionExpense()
    {
        return $this->businessProductionExpense;
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
    public function setHeight( $height)
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
    public function setWidth( $width)
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
     * @return AccountVendor
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param AccountVendor $vendor
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
    }

    /**
     * @return BusinessVendorStockItem
     */
    public function getVendorStockItem()
    {
        return $this->vendorStockItem;
    }

    /**
     * @param BusinessVendorStockItem $vendorStockItem
     */
    public function setVendorStockItem($vendorStockItem)
    {
        $this->vendorStockItem = $vendorStockItem;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate( $endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return int
     */
    public function getReturnQnt()
    {
        return $this->returnQnt;
    }

    /**
     * @param int $returnQnt
     */
    public function setReturnQnt($returnQnt)
    {
        $this->returnQnt = $returnQnt;
    }

    /**
     * @return int
     */
    public function getDamageQnt()
    {
        return $this->damageQnt;
    }

    /**
     * @param int $damageQnt
     */
    public function setDamageQnt($damageQnt)
    {
        $this->damageQnt = $damageQnt;
    }

    /**
     * @return int
     */
    public function getBonusQnt()
    {
        return $this->bonusQnt;
    }

    /**
     * @param int $bonusQnt
     */
    public function setBonusQnt($bonusQnt)
    {
        $this->bonusQnt = $bonusQnt;
    }

    /**
     * @return int
     */
    public function getSpoilQnt()
    {
        return $this->spoilQnt;
    }

    /**
     * @param int $spoilQnt
     */
    public function setSpoilQnt($spoilQnt)
    {
        $this->spoilQnt = $spoilQnt;
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
     * @return WearHouse
     */
    public function getWearhouse()
    {
        return $this->wearhouse;
    }

    /**
     * @param WearHouse $wearhouse
     */
    public function setWearhouse($wearhouse)
    {
        $this->wearhouse = $wearhouse;
    }

    /**
     * @return mixed
     */
    public function getAndroidProcess()
    {
        return $this->androidProcess;
    }

    /**
     * @param mixed $androidProcess
     */
    public function setAndroidProcess($androidProcess)
    {
        $this->androidProcess = $androidProcess;
    }

    /**
     * @return mixed
     */
    public function getBatchItem()
    {
        return $this->batchItem;
    }

    /**
     * @param mixed $batchItem
     */
    public function setBatchItem($batchItem)
    {
        $this->batchItem = $batchItem;
    }

}

