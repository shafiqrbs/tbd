<?php

namespace Terminalbd\InventoryBundle\Entity;
namespace Modules\Inventory\App\Entities;



use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints\DateTime;
use Modules\Core\App\Entities\User;
use Modules\Inventory\App\Entities\Item;
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
     * @ORM\ManyToOne(targetEntity="Item")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $item;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\NbrVatTax\App\Entities\Setting", inversedBy="stockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $taxReturnNote;


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
     * @ORM\ManyToOne(targetEntity="ProductionIssue")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionIssue;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionInventory")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionInventoryReturn;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionBatchItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionBatchItem;

      /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionBatch")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionBatch;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionBatchItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionBatchItemReturn;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionReceiveBatchItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionReceiveItem;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionReceiveBatch")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionReceive;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionExpense")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionExpense;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionExpense")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionExpenseReturn;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Setting")
     **/
    private  $branch;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Setting")
     **/
    private  $wearhouse;


    /**
     * @var string
     * @ORM\Column(type="string", nullable = true)
     */
    protected $damage;


    /**
     * @var string
     * @ORM\Column(type="string", nullable = true)
     */
    protected $createdBy;


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
     * @var string
     *
     * @ORM\Column(name="hsCode", type="string", nullable = true)
     */
    private $hsCode;


     /**
     * @var float
     *
     * @ORM\Column(name="purchasePrice", type="float", nullable = true)
     */
    private $purchasePrice = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="salesPrice", type="float", nullable = true)
     */
    private $salesPrice = 0;


     /**
     * @var float
     *
     * @ORM\Column(name="actualPrice", type="float", nullable = true)
     */
    private $actualPrice = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="customsDuty", type="float", nullable=true)
     */
    private $customsDuty = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="customsDutyPercent", type="float", nullable=true)
     */
    private $customsDutyPercent = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="supplementaryDuty", type="float", nullable=true)
     */
    private $supplementaryDuty = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(name="supplementaryDutyPercent", type="float", nullable=true)
     */
    private $supplementaryDutyPercent = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(name="valueAddedTax", type="float", nullable=true)
     */
    private $valueAddedTax = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="valueAddedTaxPercent", type="float", nullable=true)
     */
    private $valueAddedTaxPercent = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceTax", type="float", nullable=true)
     */
    private $advanceTax = 0.00;


     /**
     * @var float
     *
     * @ORM\Column(name="advanceIncomeTax", type="float", nullable=true)
     */
    private $advanceIncomeTax = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceIncomeTaxPercent", type="float", nullable=true)
     */
    private $advanceIncomeTaxPercent = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="recurringDeposit", type="float", nullable=true)
     */
    private $recurringDeposit = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(name="recurringDepositPercent", type="float", nullable=true)
     */
    private $recurringDepositPercent = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceTradeVat", type="float", nullable=true)
     */
    private $advanceTradeVat = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceTradeVatPercent", type="float", nullable=true)
     */
    private $advanceTradeVatPercent = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(name="rebatePercent", type="float", nullable=true)
     */
    private $rebatePercent = 0.00;


     /**
     * @var float
     *
     * @ORM\Column(name="rebate", type="float", nullable=true)
     */
    private $rebate = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="totalTaxIncidence", type="float", nullable=true)
     */
    private $totalTaxIncidence = 0.00;


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
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $openingQuantity= 0.00;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $openingBalance= 0.00;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $closingQuantity= 0.00;

   /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $closingBalance= 0.00;


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
     * @var \DateTime
     *
     * @ORM\Column(name="issueDate", type="datetime", nullable=true)
     */
    private $issueDate;


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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param Item $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }


    /**
     * @return PurchaseItem
     */
    public function getPurchaseItem()
    {
        return $this->purchaseItem;
    }

    /**
     * @param PurchaseItem $purchaseItem
     */
    public function setPurchaseItem($purchaseItem)
    {
        $this->purchaseItem = $purchaseItem;
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
     * @return mixed
     */
    public function getDamage()
    {
        return $this->damage;
    }

    /**
     * @param mixed $damage
     */
    public function setDamage($damage)
    {
        $this->damage = $damage;
    }


    /**
     * @return Sales
     */
    public function getSales()
    {
        return $this->sales;
    }

    /**
     * @param Sales $sales
     */
    public function setSales($sales)
    {
        $this->sales = $sales;
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
    public function getCustomsDuty()
    {
        return $this->customsDuty;
    }

    /**
     * @param float $customsDuty
     */
    public function setCustomsDuty($customsDuty)
    {
        $this->customsDuty = $customsDuty;
    }

    /**
     * @return float
     */
    public function getSupplementaryDuty()
    {
        return $this->supplementaryDuty;
    }

    /**
     * @param float $supplementaryDuty
     */
    public function setSupplementaryDuty($supplementaryDuty)
    {
        $this->supplementaryDuty = $supplementaryDuty;
    }

    /**
     * @return float
     */
    public function getValueAddedTax()
    {
        return $this->valueAddedTax;
    }

    /**
     * @param float $valueAddedTax
     */
    public function setValueAddedTax($valueAddedTax)
    {
        $this->valueAddedTax = $valueAddedTax;
    }

    /**
     * @return float
     */
    public function getAdvanceIncomeTax()
    {
        return $this->advanceIncomeTax;
    }

    /**
     * @param float $advanceIncomeTax
     */
    public function setAdvanceIncomeTax($advanceIncomeTax)
    {
        $this->advanceIncomeTax = $advanceIncomeTax;
    }

    /**
     * @return float
     */
    public function getRecurringDeposit()
    {
        return $this->recurringDeposit;
    }

    /**
     * @param float $recurringDeposit
     */
    public function setRecurringDeposit($recurringDeposit)
    {
        $this->recurringDeposit = $recurringDeposit;
    }

    /**
     * @return float
     */
    public function getAdvanceTradeVat()
    {
        return $this->advanceTradeVat;
    }

    /**
     * @param float $advanceTradeVat
     */
    public function setAdvanceTradeVat($advanceTradeVat)
    {
        $this->advanceTradeVat = $advanceTradeVat;
    }

    /**
     * @return float
     */
    public function getTotalTaxIncidence()
    {
        return $this->totalTaxIncidence;
    }

    /**
     * @param float $totalTaxIncidence
     */
    public function setTotalTaxIncidence($totalTaxIncidence)
    {
        $this->totalTaxIncidence = $totalTaxIncidence;
    }

    /**
     * @return float
     */
    public function getAdvanceTradeVatPercent()
    {
        return $this->advanceTradeVatPercent;
    }

    /**
     * @param float $advanceTradeVatPercent
     */
    public function setAdvanceTradeVatPercent($advanceTradeVatPercent)
    {
        $this->advanceTradeVatPercent = $advanceTradeVatPercent;
    }

    /**
     * @return float
     */
    public function getRecurringDepositPercent()
    {
        return $this->recurringDepositPercent;
    }

    /**
     * @param float $recurringDepositPercent
     */
    public function setRecurringDepositPercent($recurringDepositPercent)
    {
        $this->recurringDepositPercent = $recurringDepositPercent;
    }

    /**
     * @return float
     */
    public function getAdvanceIncomeTaxPercent()
    {
        return $this->advanceIncomeTaxPercent;
    }

    /**
     * @param float $advanceIncomeTaxPercent
     */
    public function setAdvanceIncomeTaxPercent($advanceIncomeTaxPercent)
    {
        $this->advanceIncomeTaxPercent = $advanceIncomeTaxPercent;
    }

    /**
     * @return float
     */
    public function getValueAddedTaxPercent()
    {
        return $this->valueAddedTaxPercent;
    }

    /**
     * @param float $valueAddedTaxPercent
     */
    public function setValueAddedTaxPercent($valueAddedTaxPercent)
    {
        $this->valueAddedTaxPercent = $valueAddedTaxPercent;
    }

    /**
     * @return float
     */
    public function getSupplementaryDutyPercent()
    {
        return $this->supplementaryDutyPercent;
    }

    /**
     * @param float $supplementaryDutyPercent
     */
    public function setSupplementaryDutyPercent($supplementaryDutyPercent)
    {
        $this->supplementaryDutyPercent = $supplementaryDutyPercent;
    }

    /**
     * @return float
     */
    public function getCustomsDutyPercent()
    {
        return $this->customsDutyPercent;
    }

    /**
     * @param float $customsDutyPercent
     */
    public function setCustomsDutyPercent($customsDutyPercent)
    {
        $this->customsDutyPercent = $customsDutyPercent;
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
     * @return string
     */
    public function getAssuranceFromVendor()
    {
        return $this->assuranceFromVendor;
    }

    /**
     * @param string $assuranceFromVendor
     */
    public function setAssuranceFromVendor($assuranceFromVendor)
    {
        $this->assuranceFromVendor = $assuranceFromVendor;
    }

    /**
     * @return string
     */
    public function getAssuranceToCustomer()
    {
        return $this->assuranceToCustomer;
    }

    /**
     * @param string $assuranceToCustomer
     */
    public function setAssuranceToCustomer($assuranceToCustomer)
    {
        $this->assuranceToCustomer = $assuranceToCustomer;
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
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return Inventory
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Inventory $config
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
     * @return TaxTariff
     */
    public function getHsCode()
    {
        return $this->hsCode;
    }

    /**
     * @param TaxTariff $hsCode
     */
    public function setHsCode($hsCode)
    {
        $this->hsCode = $hsCode;
    }

    /**
     * @return mixed
     */
    public function getPurchaseStockReturn()
    {
        return $this->purchaseStockReturn;
    }

    /**
     * @param mixed $purchaseStockReturn
     */
    public function setPurchaseStockReturn($purchaseStockReturn)
    {
        $this->purchaseStockReturn = $purchaseStockReturn;
    }

    /**
     * @return mixed
     */
    public function getSalesStockReturn()
    {
        return $this->salesStockReturn;
    }

    /**
     * @param mixed $salesStockReturn
     */
    public function setSalesStockReturn($salesStockReturn)
    {
        $this->salesStockReturn = $salesStockReturn;
    }

    /**
     * @return mixed
     */
    public function getAssetsStockReturn()
    {
        return $this->assetsStockReturn;
    }

    /**
     * @param mixed $assetsStockReturn
     */
    public function setAssetsStockReturn($assetsStockReturn)
    {
        $this->assetsStockReturn = $assetsStockReturn;
    }

    /**
     * @return Purchase
     */
    public function getPurchase()
    {
        return $this->purchase;
    }

    /**
     * @param Purchase $purchase
     */
    public function setPurchase($purchase)
    {
        $this->purchase = $purchase;
    }

    /**
     * @return float
     */
    public function getRebatePercent()
    {
        return $this->rebatePercent;
    }

    /**
     * @param float $rebatePercent
     */
    public function setRebatePercent($rebatePercent)
    {
        $this->rebatePercent = $rebatePercent;
    }

    /**
     * @return float
     */
    public function getRebate()
    {
        return $this->rebate;
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
     * @return PurchaseReturn
     */
    public function getPurchaseReturn()
    {
        return $this->purchaseReturn;
    }

    /**
     * @param PurchaseReturn $purchaseReturn
     */
    public function setPurchaseReturn($purchaseReturn)
    {
        $this->purchaseReturn = $purchaseReturn;
    }

    /**
     * @return SalesItem
     */
    public function getSalesItem()
    {
        return $this->salesItem;
    }

    /**
     * @param SalesItem $salesItem
     */
    public function setSalesItem($salesItem)
    {
        $this->salesItem = $salesItem;
    }


    /**
     * @return float
     */
    public function getQuantity(): ? float
    {
        return $this->quantity;
    }

    /**
     * @param float $quantity
     */
    public function setQuantity(float $quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return float
     */
    public function getOpeningQuantity(): ? float
    {
        return $this->openingQuantity;
    }

    /**
     * @param float $openingQuantity
     */
    public function setOpeningQuantity(float $openingQuantity)
    {
        $this->openingQuantity = $openingQuantity;
    }

    /**
     * @return float
     */
    public function getClosingQuantity(): ? float
    {
        return $this->closingQuantity;
    }

    /**
     * @param float $closingQuantity
     */
    public function setClosingQuantity(float $closingQuantity)
    {
        $this->closingQuantity = $closingQuantity;
    }

    /**
     * @return float
     */
    public function getClosingBalance(): ? float
    {
        return $this->closingBalance;
    }

    /**
     * @param float $closingBalance
     */
    public function setClosingBalance(float $closingBalance)
    {
        $this->closingBalance = $closingBalance;
    }

    /**
     * @return float
     */
    public function getPurchaseQuantity(): ? float
    {
        return $this->purchaseQuantity;
    }

    /**
     * @param float $purchaseQuantity
     */
    public function setPurchaseQuantity(float $purchaseQuantity)
    {
        $this->purchaseQuantity = $purchaseQuantity;
    }

    /**
     * @return float
     */
    public function getPurchaseReturnQuantity(): ? float
    {
        return $this->purchaseReturnQuantity;
    }

    /**
     * @param float $purchaseReturnQuantity
     */
    public function setPurchaseReturnQuantity(float $purchaseReturnQuantity)
    {
        $this->purchaseReturnQuantity = $purchaseReturnQuantity;
    }

    /**
     * @return float
     */
    public function getSalesQuantity(): ? float
    {
        return $this->salesQuantity;
    }

    /**
     * @param float $salesQuantity
     */
    public function setSalesQuantity(float $salesQuantity)
    {
        $this->salesQuantity = $salesQuantity;
    }

    /**
     * @return float
     */
    public function getSalesReturnQuantity(): ? float
    {
        return $this->salesReturnQuantity;
    }

    /**
     * @param float $salesReturnQuantity
     */
    public function setSalesReturnQuantity(float $salesReturnQuantity)
    {
        $this->salesReturnQuantity = $salesReturnQuantity;
    }

    /**
     * @return float
     */
    public function getAssetsQuantity(): ? float
    {
        return $this->assetsQuantity;
    }

    /**
     * @param float $assetsQuantity
     */
    public function setAssetsQuantity(float $assetsQuantity)
    {
        $this->assetsQuantity = $assetsQuantity;
    }

    /**
     * @return float
     */
    public function getAssetsReturnQuantity(): ? float
    {
        return $this->assetsReturnQuantity;
    }

    /**
     * @param float $assetsReturnQuantity
     */
    public function setAssetsReturnQuantity(float $assetsReturnQuantity)
    {
        $this->assetsReturnQuantity = $assetsReturnQuantity;
    }

    /**
     * @return float
     */
    public function getDamageQuantity(): ? float
    {
        return $this->damageQuantity;
    }

    /**
     * @param float $damageQuantity
     */
    public function setDamageQuantity(float $damageQuantity)
    {
        $this->damageQuantity = $damageQuantity;
    }

    /**
     * @return float
     */
    public function getOpeningBalance(): ? float
    {
        return $this->openingBalance;
    }

    /**
     * @param float $openingBalance
     */
    public function setOpeningBalance(float $openingBalance)
    {
        $this->openingBalance = $openingBalance;
    }

    /**
     * @return PurchaseReturnItem
     */
    public function getPurchaseReturnItem()
    {
        return $this->purchaseReturnItem;
    }

    /**
     * @param PurchaseReturnItem $purchaseReturnItem
     */
    public function setPurchaseReturnItem($purchaseReturnItem)
    {
        $this->purchaseReturnItem = $purchaseReturnItem;
    }

    /**
     * @return SalesReturnItem
     */
    public function getSalesReturnItem()
    {
        return $this->salesReturnItem;
    }

    /**
     * @param SalesReturnItem $salesReturnItem
     */
    public function setSalesReturnItem($salesReturnItem)
    {
        $this->salesReturnItem = $salesReturnItem;
    }

    /**
     * @return ProductionIssue
     */
    public function getProductionIssue()
    {
        return $this->productionIssue;
    }

    /**
     * @param ProductionIssue $productionIssue
     */
    public function setProductionIssue($productionIssue)
    {
        $this->productionIssue = $productionIssue;
    }


    /**
     * @return float
     */
    public function getProductionIssueQuantity()
    {
        return $this->productionIssueQuantity;
    }

    /**
     * @param float $productionIssueQuantity
     */
    public function setProductionIssueQuantity( $productionIssueQuantity)
    {
        $this->productionIssueQuantity = $productionIssueQuantity;
    }



    /**
     * @return ProductionInventory
     */
    public function getProductionInventoryReturn()
    {
        return $this->productionInventoryReturn;
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
    public function getProductionInventoryReturnQuantity(): ? float
    {
        return $this->productionInventoryReturnQuantity;
    }

    /**
     * @param float $productionInventoryReturnQuantity
     */
    public function setProductionInventoryReturnQuantity(float $productionInventoryReturnQuantity)
    {
        $this->productionInventoryReturnQuantity = $productionInventoryReturnQuantity;
    }



    /**
     * @return ProductionExpense
     */
    public function getProductionExpense()
    {
        return $this->productionExpense;
    }

    /**
     * @param ProductionExpense $productionExpense
     */
    public function setProductionExpense($productionExpense)
    {
        $this->productionExpense = $productionExpense;
    }

    /**
     * @return ProductionExpense
     */
    public function getProductionExpenseReturn()
    {
        return $this->productionExpenseReturn;
    }

    /**
     * @param ProductionExpense $productionExpenseReturn
     */
    public function setProductionExpenseReturn($productionExpenseReturn)
    {
        $this->productionExpenseReturn = $productionExpenseReturn;
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
     * @return ProductionReceiveBatchItem
     */
    public function getProductionReceiveItem()
    {
        return $this->productionReceiveItem;
    }

    /**
     * @param ProductionReceiveBatchItem $productionReceiveItem
     */
    public function setProductionReceiveItem($productionReceiveItem)
    {
        $this->productionReceiveItem = $productionReceiveItem;
    }

    /**
     * @return ProductionBatchItem
     */
    public function getProductionBatchItem()
    {
        return $this->productionBatchItem;
    }

    /**
     * @param ProductionBatchItem $productionBatchItem
     */
    public function setProductionBatchItem($productionBatchItem)
    {
        $this->productionBatchItem = $productionBatchItem;
    }

    /**
     * @return ProductionBatchItem
     */
    public function getProductionBatchItemReturn()
    {
        return $this->productionBatchItemReturn;
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
    public function setProductionBatchItemQuantity(float $productionBatchItemQuantity)
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
    public function setProductionBatchItemReturnQuantity(float $productionBatchItemReturnQuantity)
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
    public function setProductionExpenseReturnQuantity(float $productionExpenseReturnQuantity)
    {
        $this->productionExpenseReturnQuantity = $productionExpenseReturnQuantity;
    }

    /**
     * @return \App\Entity\Core\Setting
     */
    public function getBranch()
    {
        return $this->branch;
    }


    /**
     * @param \App\Entity\Core\Setting $branch
     */
    public function setBranch($branch)
    {
        $this->branch = $branch;
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
    public function setBranchIssueQuantity(float $branchIssueQuantity)
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
    public function setBranchIssueReturnQuantity(float $branchIssueReturnQuantity)
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
    public function setCode(int $code)
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
    public function setSku(string $sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return float
     */
    public function getAdvanceTax()
    {
        return $this->advanceTax;
    }

    /**
     * @param float $advanceTax
     */
    public function setAdvanceTax(float $advanceTax)
    {
        $this->advanceTax = $advanceTax;
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
     * @return SalesReturn
     */
    public function getSalesReturn()
    {
        return $this->salesReturn;
    }

    /**
     * @param SalesReturn $salesReturn
     */
    public function setSalesReturn($salesReturn)
    {
        $this->salesReturn = $salesReturn;
    }

    /**
     * @return ProductionReceiveBatch
     */
    public function getProductionReceive()
    {
        return $this->productionReceive;
    }

    /**
     * @param ProductionReceiveBatch $productionReceive
     */
    public function setProductionReceive($productionReceive)
    {
        $this->productionReceive = $productionReceive;
    }

    /**
     * @return ProductionBatch
     */
    public function getProductionBatch()
    {
        return $this->productionBatch;
    }

    /**
     * @param ProductionBatch $productionBatch
     */
    public function setProductionBatch($productionBatch)
    {
        $this->productionBatch = $productionBatch;
    }

    /**
     * @return \Terminalbd\NbrvatBundle\Entity\Setting
     */
    public function getTaxReturnNote()
    {
        return $this->taxReturnNote;
    }

    /**
     * @param \Terminalbd\NbrvatBundle\Entity\Setting $taxReturnNote
     */
    public function setTaxReturnNote($taxReturnNote)
    {
        $this->taxReturnNote = $taxReturnNote;
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


}

