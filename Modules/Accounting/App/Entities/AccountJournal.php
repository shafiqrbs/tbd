<?php

namespace Modules\Accounting\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * AccountJournal
 *
 * @ORM\Table(name="acc_journal")
 * @ORM\Entity(repositoryClass="Modules\Accounting\App\Repositories\AccountJournalRepository")
 */

class AccountJournal
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
     * @ORM\ManyToOne(targetEntity="Config", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;

    /**
     * @ORM\OneToMany(targetEntity="AccountJournalItem", mappedBy="accountJournal")
     * @ORM\OrderBy({"balanceMode" = "DESC"})
     **/
    protected $journalItems;

    /**
     * @ORM\ManyToOne(targetEntity="AccountVoucher")
     **/
    protected $voucher;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Sales")
     * @ORM\JoinColumn(name="sales_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $sales;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\SalesReturn")
     * @ORM\JoinColumn(name="sales_return_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $salesReturn;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Purchase")
     * @ORM\JoinColumn(name="purchase_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $purchase;


     /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\PurchaseItem")
     * @ORM\JoinColumn(name="purchase_item_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $purchaseItem;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\PurchaseReturn")
     * @ORM\JoinColumn(name="purchase_return_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $purchaseReturn;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItemReconciliation")
     * @ORM\JoinColumn(name="stock_item_reconciliation_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $stockItemReconciliation;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Damage")
     * @ORM\JoinColumn(name="damage_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $damage;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" , nullable=true)
     */
    private $isSignature = false;

    /**
     * @var integer
     * @ORM\Column(type="integer",nullable=true)
     */
    private $processOrdering = 0;

    /**
     * @var string
     * @ORM\Column(type="string",nullable=true)
     */
    private $entryMode='voucher';


    /**
     * @var string
     * @ORM\Column(type="string",nullable=true)
     */
    private $process='New';

    /**
     * @var string
     * @ORM\Column(type="string",nullable=true)
     */
    private $module ="account-journal";


    /**
     * @var string
     * @ORM\Column(type="string",nullable=true)
     */
    private $waitingProcess;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float",nullable=true)
     */
    private $amount = 0;

     /**
     * @var float
     *
     * @ORM\Column(name="debit", type="float",nullable=true)
     */
    private $debit = 0;

     /**
     * @var float
     *
     * @ORM\Column(name="credit", type="float",nullable=true)
     */
    private $credit = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="balance", type="float", nullable=true)
     */
    private $balance = 0;


    /**
     * @var string
     *
     * @ORM\Column(name="payment_method", type="string", length=50, nullable=true)
     */
    private $paymentMethod;


    /**
     * @var string
     *
     * @ORM\Column(name="invoice_no", type="string", length=150, nullable=true)
     */
    private $invoiceNo;


    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;


    /**
     * @var integer
     * @ORM\Column(name="ref_no", type="integer",  nullable=true)
     */
    private $refNo;


    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $approvedBy;


    /**
     * @var text
     *
     * @ORM\Column(name="description", type="text", nullable = true)
     */
    private $description;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $reportTo;


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
     * @ORM\Column(name="issue_date", type="date", nullable=true)
     */
    private $issueDate;


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
     * @return AccountJournalItem
     */
    public function getJournalItems()
    {
        return $this->journalItems;
    }

    /**
     * @return AccountVoucher
     */
    public function getVoucher()
    {
        return $this->voucher;
    }

    /**
     * @param AccountVoucher $voucher
     */
    public function setVoucher($voucher)
    {
        $this->voucher = $voucher;
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
    public function getStockAdjustment()
    {
        return $this->stockAdjustment;
    }

    /**
     * @param mixed $stockAdjustment
     */
    public function setStockAdjustment($stockAdjustment)
    {
        $this->stockAdjustment = $stockAdjustment;
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
     * @return bool
     */
    public function isSignature()
    {
        return $this->isSignature;
    }

    /**
     * @param bool $isSignature
     */
    public function setIsSignature($isSignature)
    {
        $this->isSignature = $isSignature;
    }

    /**
     * @return int
     */
    public function getProcessOrdering()
    {
        return $this->processOrdering;
    }

    /**
     * @param int $processOrdering
     */
    public function setProcessOrdering($processOrdering)
    {
        $this->processOrdering = $processOrdering;
    }

    /**
     * @return string
     */
    public function getEntryMode()
    {
        return $this->entryMode;
    }

    /**
     * @param string $entryMode
     */
    public function setEntryMode($entryMode)
    {
        $this->entryMode = $entryMode;
    }

    /**
     * @return string
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param string $process
     */
    public function setProcess($process)
    {
        $this->process = $process;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param string $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * @return string
     */
    public function getWaitingProcess()
    {
        return $this->waitingProcess;
    }

    /**
     * @param string $waitingProcess
     */
    public function setWaitingProcess($waitingProcess)
    {
        $this->waitingProcess = $waitingProcess;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getDebit()
    {
        return $this->debit;
    }

    /**
     * @param float $debit
     */
    public function setDebit($debit)
    {
        $this->debit = $debit;
    }

    /**
     * @return float
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * @param float $credit
     */
    public function setCredit($credit)
    {
        $this->credit = $credit;
    }

    /**
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param float $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
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
     * @return mixed
     */
    public function getReportTo()
    {
        return $this->reportTo;
    }

    /**
     * @param mixed $reportTo
     */
    public function setReportTo($reportTo)
    {
        $this->reportTo = $reportTo;
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
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
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
    public function getInvoiceNo()
    {
        return $this->invoiceNo;
    }

    /**
     * @param string $invoiceNo
     */
    public function setInvoiceNo($invoiceNo)
    {
        $this->invoiceNo = $invoiceNo;
    }


}

