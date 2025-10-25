<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * BusinessStockHistory
 *
 * @ORM\Table( name="inv_stock_item_inventory_history")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\StockItemInventoryHistoryRepository")
 */
class StockItemInventoryHistory
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
     * @ORM\ManyToOne(targetEntity="Config" , cascade={"detach","merge"} )
     **/
    private  $config;

    /**
     * @ORM\OneToOne(targetEntity="StockItemHistory")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $stockItemHistory;


    /**
     * @ORM\ManyToOne(targetEntity="PurchaseItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $purchaseItem;

    /**
     * @ORM\ManyToOne(targetEntity="Purchase")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $purchase;

    /**
     * @ORM\ManyToOne(targetEntity="PurchaseReturnItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $purchaseReturnItem;

    /**
     * @ORM\ManyToOne(targetEntity="PurchaseReturn")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $purchaseReturn;

    /**
     * @ORM\ManyToOne(targetEntity="Sales", inversedBy="stockItems")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $sales;

    /**
     * @ORM\ManyToOne(targetEntity="SalesItem", inversedBy="stockItems")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $salesItem;

    /**
     * @ORM\ManyToOne(targetEntity="SalesReturnItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $salesReturnItem;

    /**
     * @ORM\ManyToOne(targetEntity="SalesReturn")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $salesReturn;

    /**
     * @ORM\ManyToOne(targetEntity="StockTransfer")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $stockTransfer;

    /**
     * @ORM\ManyToOne(targetEntity="StockTransferItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $stockTransferItem;


    /**
     * @var string
     * @ORM\Column(type="string", nullable = true)
     */
    protected $damage;


    /**
     * @var string
     * @ORM\Column(type="string", nullable = true)
     */
    protected $vendor;

    /**
     * @var string
     * @ORM\Column(type="string", nullable = true)
     */
    protected $brand;


    /**
     * @var string
     * @ORM\Column(type="string", nullable = true)
     */
    private  $category;


    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable = true)
     */
    private $price = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $purchasePrice = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesPrice = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $actualPrice = 0;



     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $discountPrice = 0;



    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $subTotal = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="total", type="float", nullable = true)
     */
    private $total = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float", nullable = true)
     */
    private $quantity= 0.00;


    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $purchaseQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $purchaseReturnQuantity = 0.00;


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
     * @var string
     *
     * @ORM\Column(name="serialNo", type="text", length=255, nullable = true)
     */
    private $serialNo;


    /**
     * @var DateTime
     *
     * @ORM\Column(name="expiredDate", type="datetime", nullable=true)
     */
    private $expiredDate;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create_at")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update_at")
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
    public function getPurchaseItem()
    {
        return $this->purchaseItem;
    }

    /**
     * @param mixed $purchaseItem
     */
    public function setPurchaseItem($purchaseItem)
    {
        $this->purchaseItem = $purchaseItem;
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
    public function getPurchaseReturnItem()
    {
        return $this->purchaseReturnItem;
    }

    /**
     * @param mixed $purchaseReturnItem
     */
    public function setPurchaseReturnItem($purchaseReturnItem)
    {
        $this->purchaseReturnItem = $purchaseReturnItem;
    }

    /**
     * @return mixed
     */
    public function getPurchaseReturn()
    {
        return $this->purchaseReturn;
    }

    /**
     * @param mixed $purchaseReturn
     */
    public function setPurchaseReturn($purchaseReturn)
    {
        $this->purchaseReturn = $purchaseReturn;
    }

    /**
     * @return mixed
     */
    public function getSales()
    {
        return $this->sales;
    }

    /**
     * @param mixed $sales
     */
    public function setSales($sales)
    {
        $this->sales = $sales;
    }

    /**
     * @return mixed
     */
    public function getSalesItem()
    {
        return $this->salesItem;
    }

    /**
     * @param mixed $salesItem
     */
    public function setSalesItem($salesItem)
    {
        $this->salesItem = $salesItem;
    }

    /**
     * @return mixed
     */
    public function getSalesReturnItem()
    {
        return $this->salesReturnItem;
    }

    /**
     * @param mixed $salesReturnItem
     */
    public function setSalesReturnItem($salesReturnItem)
    {
        $this->salesReturnItem = $salesReturnItem;
    }

    /**
     * @return mixed
     */
    public function getSalesReturn()
    {
        return $this->salesReturn;
    }

    /**
     * @param mixed $salesReturn
     */
    public function setSalesReturn($salesReturn)
    {
        $this->salesReturn = $salesReturn;
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
     * @return string
     */
    public function getDamage()
    {
        return $this->damage;
    }

    /**
     * @param string $damage
     */
    public function setDamage($damage)
    {
        $this->damage = $damage;
    }


    /**
     * @return string
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param string $vendor
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
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
    public function getBranchIssueQuantity()
    {
        return $this->branchIssueQuantity;
    }

    /**
     * @param float $branchIssueQuantity
     */
    public function setBranchIssueQuantity($branchIssueQuantity)
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
    public function setBranchIssueReturnQuantity($branchIssueReturnQuantity)
    {
        $this->branchIssueReturnQuantity = $branchIssueReturnQuantity;
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
    public function getAssetsQuantity()
    {
        return $this->assetsQuantity;
    }

    /**
     * @param float $assetsQuantity
     */
    public function setAssetsQuantity($assetsQuantity)
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
    public function setAssetsReturnQuantity($assetsReturnQuantity)
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
    public function setDamageQuantity($damageQuantity)
    {
        $this->damageQuantity = $damageQuantity;
    }


    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
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
     * @return DateTime
     */
    public function getExpiredDate()
    {
        return $this->expiredDate;
    }

    /**
     * @param DateTime $expiredDate
     */
    public function setExpiredDate($expiredDate)
    {
        $this->expiredDate = $expiredDate;
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
     * @return StockItemHistory
     */
    public function getStockItemHistory()
    {
        return $this->stockItemHistory;
    }

    /**
     * @param StockItemHistory $stockItemHistory
     */
    public function setStockItemHistory($stockItemHistory)
    {
        $this->stockItemHistory = $stockItemHistory;
    }



}

