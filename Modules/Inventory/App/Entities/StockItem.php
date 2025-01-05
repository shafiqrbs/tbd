<?php

namespace Terminalbd\InventoryBundle\Entity;
namespace Modules\Inventory\App\Entities;



use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints\DateTime;
use Modules\Core\App\Entities\User;
use Modules\Inventory\App\Entities\Product;
use Modules\Production\App\Entities\ProductionBatch;
use Modules\Production\App\Entities\ProductionBatchItem;
use Modules\Production\App\Entities\ProductionExpense;
use Modules\Production\App\Entities\ProductionInventory;
use Modules\Production\App\Entities\ProductionReceiveBatch;
use Modules\Production\App\Entities\ProductionReceiveBatchItem;

/**
 * StockItem
 *
 * @ORM\Table("inv_stock")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\StockItemRepository")
 */
class StockItem
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
    protected  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="stockItems")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $product;


    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="rack_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $rackNo;


    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="grade_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $grade;

     /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $brand;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="size_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $size;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $model;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="color_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $color;


    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable = true)
     */
    private $name;


     /**
     * @var string
     *
     * @ORM\Column(type="text", nullable = true)
     */
    private $displayName;


      /**
     * @var string
     *
     * @ORM\Column(type="text", nullable = true)
     */
    private $uom;


     /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", options={"default":0})
     */
    private $price=0;


     /**
     * @var float
     *
     * @ORM\Column(type="float", options={"default":0})
     */
    private $purchasePrice=0;


    /**
     * @var float
     *
     * @ORM\Column( type="float",options={"default":0})
     */
     private $salesPrice=0;

     /**
     * @var float
     *
     * @ORM\Column( type="float",options={"default":0})
     */
     private $actualPrice = 0;



     /**
     * @var float
     *
     * @ORM\Column(name="total", type="float", options={"default"="0"})
     */
    private $total=0;


    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float", options={"default"="0"})
     */
    private $quantity=0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", options={"default"="0"})
     */
    private $openingQuantity=0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", options={"default"="0"})
     */
    private $openingBalance=0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", options={"default"="0"})
     */
    private $closingQuantity=0;



    /**
     * @var float
     * @ORM\Column(type="float",options={"default"="0"})
     */
    private $purchaseQuantity=0;

     /**
     * @var float
     * @ORM\Column(type="float",options={"default"="0"})
     */
    private $minQuantity=0;


     /**
     * @var float
     * @ORM\Column(type="float",options={"default"="0"})
     */
    private $reorderQuantity=0;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $purchaseReturnQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $productionIssueQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $productionInventoryReturnQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $productionBatchItemQuantity= 0.00;


    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $productionBatchItemReturnQuantity= 0.00;


    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $productionExpenseQuantity= 0.00;


    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $productionExpenseReturnQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $branchIssueQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $branchIssueReturnQuantity= 0.00;


    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesReturnQuantity= 0.00;


    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $assetsQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $assetsReturnQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $damageQuantity = 0.00;


     /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $remainingQuantity = 0.00;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", nullable = true)
     */
    private $process;

     /**
     * @var string
     *
     * @ORM\Column(name="mode", type="string", length = 40, nullable = true)
     */
    private $mode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $remark;

    /**
     * @var string
     *
     * @ORM\Column( type="text", length=255, nullable = true)
     */
    private $serialNo;


    /**
     * @var DateTime
     *
     * @ORM\Column( type="datetime", nullable=true)
     */
    private $expiredDate;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=50, nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $sku;

     /**
     * @var string
     *
     * @ORM\Column(type="string",  nullable=true)
     */
    private $barcode;

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $slug;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default":0} )
     */
    private $status= true;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default":0} )
     */
    private $isMaster= false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", options={"default":0})
     */
    private $isDelete = false;


    /**
     * @var \DateTime
     *
     * @ORM\Column( type="datetime", nullable=true)
     */
    private $issueDate;

    /**
     * @var string
     * @ORM\Column( type="text", length=255, nullable = true)
     */
    private $itemSize;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column( type="datetime", options={"default": "CURRENT_TIMESTAMP"}, nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column( type="datetime",options={"default": "CURRENT_TIMESTAMP"},nullable=true)
     */
    private $updatedAt ;



    /**
     * Get id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getItemSize(): string
    {
        return $this->itemSize;
    }

    public function setItemSize(string $itemSize): void
    {
        $this->itemSize = $itemSize;
    }


    /**
     * @return string
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param int $process
     * purchase         = 1
     * purchase return  = 2
     * sales            = 3
     * sales return     = 4
     * reject           = 5
     * damage          = 6
     */


    public function setProcess($process)
    {
        $this->process = $process;
    }


    /**
     * @return float
     */
    public function getActualPrice()
    {
        return $this->actualPrice;
    }

    /**
     * @param float $actualPrice
     */
    public function setActualPrice($actualPrice)
    {
        $this->actualPrice = $actualPrice;
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
    public function getSerialNo()
    {
        return $this->serialNo;
    }

    /**
     * @param string $serialNo
     */
    public function setSerialNo($serialNo)
    {
        $this->serialNo = $serialNo;
    }



    /**
     * @return mixed
     */
    public function getExpiredDate()
    {
        return $this->expiredDate;
    }

    /**
     * @param mixed $expiredDate
     */
    public function setExpiredDate($expiredDate)
    {
        $this->expiredDate = $expiredDate;
    }


    /**
     * @return Particular
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param Particular $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
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
     * @param float $rebate
     */
    public function setRebate($rebate)
    {
        $this->rebate = $rebate;
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
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param float $quantity
     */
    public function setQuantity( $quantity)
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
    public function setOpeningQuantity( $openingQuantity)
    {
        $this->openingQuantity = $openingQuantity;
    }

    /**
     * @return float
     */
    public function getClosingQuantity()
    {
        return $this->closingQuantity;
    }

    /**
     * @param float $closingQuantity
     */
    public function setClosingQuantity( $closingQuantity)
    {
        $this->closingQuantity = $closingQuantity;
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
    public function setPurchaseQuantity( $purchaseQuantity)
    {
        $this->purchaseQuantity = $purchaseQuantity;
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
    public function setPurchaseReturnQuantity( $purchaseReturnQuantity)
    {
        $this->purchaseReturnQuantity = $purchaseReturnQuantity;
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
    public function setSalesQuantity( $salesQuantity)
    {
        $this->salesQuantity = $salesQuantity;
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
    public function setSalesReturnQuantity( $salesReturnQuantity)
    {
        $this->salesReturnQuantity = $salesReturnQuantity;
    }

    /**
     * @return float
     */
    public function getAssetsQuantity()
    {
        return $this->assetsQuantity;
    }

    /**
     * @param float $assetsQuantity
     */
    public function setAssetsQuantity( $assetsQuantity)
    {
        $this->assetsQuantity = $assetsQuantity;
    }

    /**
     * @return float
     */
    public function getAssetsReturnQuantity()
    {
        return $this->assetsReturnQuantity;
    }

    /**
     * @param float $assetsReturnQuantity
     */
    public function setAssetsReturnQuantity( $assetsReturnQuantity)
    {
        $this->assetsReturnQuantity = $assetsReturnQuantity;
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
    public function setDamageQuantity( $damageQuantity)
    {
        $this->damageQuantity = $damageQuantity;
    }

    /**
     * @return float
     */
    public function getOpeningBalance()
    {
        return $this->openingBalance;
    }

    /**
     * @param float $openingBalance
     */
    public function setOpeningBalance( $openingBalance)
    {
        $this->openingBalance = $openingBalance;
    }




    /**
     * @return float
     */
    public function getProductionIssueQuantity()
    {
        return $this->productionIssueQuantity;
    }



    /**
     * @param ProductionInventory $productionInventoryReturn
     */
    public function setProductionInventoryReturn($productionInventoryReturn)
    {
        $this->productionInventoryReturn = $productionInventoryReturn;
    }

    /**
     * @return float
     */
    public function getProductionInventoryReturnQuantity()
    {
        return $this->productionInventoryReturnQuantity;
    }

    /**
     * @param float $productionInventoryReturnQuantity
     */
    public function setProductionInventoryReturnQuantity( $productionInventoryReturnQuantity)
    {
        $this->productionInventoryReturnQuantity = $productionInventoryReturnQuantity;
    }



    /**
     * @return float
     */
    public function getProductionExpenseQuantity()
    {
        return $this->productionExpenseQuantity;
    }

    /**
     * @param float $productionExpenseQuantity
     */
    public function setProductionExpenseQuantity($productionExpenseQuantity)
    {
        $this->productionExpenseQuantity = $productionExpenseQuantity;
    }



    /**
     * @param ProductionBatchItem $productionBatchItemReturn
     */
    public function setProductionBatchItemReturn($productionBatchItemReturn)
    {
        $this->productionBatchItemReturn = $productionBatchItemReturn;
    }

    /**
     * @return float
     */
    public function getProductionBatchItemQuantity()
    {
        return $this->productionBatchItemQuantity;
    }

    /**
     * @param float $productionBatchItemQuantity
     */
    public function setProductionBatchItemQuantity( $productionBatchItemQuantity)
    {
        $this->productionBatchItemQuantity = $productionBatchItemQuantity;
    }

    /**
     * @return float
     */
    public function getProductionBatchItemReturnQuantity()
    {
        return $this->productionBatchItemReturnQuantity;
    }

    /**
     * @param float $productionBatchItemReturnQuantity
     */
    public function setProductionBatchItemReturnQuantity( $productionBatchItemReturnQuantity)
    {
        $this->productionBatchItemReturnQuantity = $productionBatchItemReturnQuantity;
    }

    /**
     * @return float
     */
    public function getProductionExpenseReturnQuantity()
    {
        return $this->productionExpenseReturnQuantity;
    }

    /**
     * @param float $productionExpenseReturnQuantity
     */
    public function setProductionExpenseReturnQuantity( $productionExpenseReturnQuantity)
    {
        $this->productionExpenseReturnQuantity = $productionExpenseReturnQuantity;
    }



    /**
     * @return float
     */
    public function getBranchIssueQuantity()
    {
        return $this->branchIssueQuantity;
    }

    /**
     * @param float $branchIssueQuantity
     */
    public function setBranchIssueQuantity( $branchIssueQuantity)
    {
        $this->branchIssueQuantity = $branchIssueQuantity;
    }

    /**
     * @return float
     */
    public function getBranchIssueReturnQuantity()
    {
        return $this->branchIssueReturnQuantity;
    }

    /**
     * @param float $branchIssueReturnQuantity
     */
    public function setBranchIssueReturnQuantity( $branchIssueReturnQuantity)
    {
        $this->branchIssueReturnQuantity = $branchIssueReturnQuantity;
    }

    /**
     * @return int
     */
    public function getCode(): ? int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode( $code)
    {
        $this->code = $code;
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
    public function setSku( $sku)
    {
        $this->sku = $sku;
    }



    /**
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param string $remark
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
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
    public function setIssueDate(\DateTime $issueDate)
    {
        $this->issueDate = $issueDate;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $item
     */
    public function setItem($item)
    {
        $this->item = $item;
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
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * @param mixed $grade
     */
    public function setGrade($grade)
    {
        $this->grade = $grade;
    }

    /**
     * @return Particular
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param Particular $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return Particular
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param Particular $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
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
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
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
    public function getSalesPrice()
    {
        return $this->salesPrice;
    }

    /**
     * @param mixed $salesPrice
     */
    public function setSalesPrice($salesPrice)
    {
        $this->salesPrice = $salesPrice;
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
     * @return bool
     */
    public function isDelete()
    {
        return $this->isDelete;
    }

    /**
     * @param bool $isDelete
     */
    public function setIsDelete($isDelete)
    {
        $this->isDelete = $isDelete;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    /**
     * @return float
     */
    public function getMinQuantity()
    {
        return $this->minQuantity;
    }

    /**
     * @param float $minQuantity
     */
    public function setMinQuantity($minQuantity)
    {
        $this->minQuantity = $minQuantity;
    }

    /**
     * @return float
     */
    public function getReorderQuantity()
    {
        return $this->reorderQuantity;
    }

    /**
     * @param float $reorderQuantity
     */
    public function setReorderQuantity($reorderQuantity)
    {
        $this->reorderQuantity = $reorderQuantity;
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
     * @return bool
     */
    public function isMaster()
    {
        return $this->isMaster;
    }

    /**
     * @param bool $isMaster
     */
    public function setIsMaster($isMaster)
    {
        $this->isMaster = $isMaster;
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


}

