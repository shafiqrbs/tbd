<?php

namespace Modules\Accounting\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * AccountJournal
 *
 * @ORM\Table(name="acc_journal_item")
 * @ORM\Entity()
 */
class AccountJournalItem
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
     * @ORM\ManyToOne(targetEntity="AccountJournal")
     * @ORM\JoinColumn(name="account_journal_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private  $accountJournal;


    /**
     * @ORM\ManyToOne(targetEntity="AccountHead",cascade={"detach","merge"})
     * @ORM\JoinColumn(name="account_head_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private  $accountHead;


    /**
     * @ORM\ManyToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_sub_head_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    protected $accountSubHead;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\Bank")
     **/
    protected $bank;

    /**
     * @ORM\ManyToOne(targetEntity="AccountJournalItem", inversedBy="children", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="AccountJournalItem" , mappedBy="parent")
     **/
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Sales")
     * @ORM\JoinColumn(name="sales_id", referencedColumnName="id", nullable=true,onDelete="CASCADE")
     **/
    private  $sales;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\SalesReturn")
     * @ORM\JoinColumn(name="sales_return_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private  $salesReturn;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Purchase")
     * @ORM\JoinColumn(name="purchase_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private  $purchase;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\SalesReturn")
     * @ORM\JoinColumn(name="purchase_return_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private  $purchaseReturn;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItemReconciliation")
     * @ORM\JoinColumn(name="stock_adjustment_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private  $stockItemReconciliation;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Damage")
     * @ORM\JoinColumn(name="damage_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private  $damage;


     /**
     * @ORM\ManyToOne(targetEntity="Setting")
     * @ORM\JoinColumn(name="account_mode_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private  $accountMode;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $amount = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $openingAmount = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $closingAmount = 0;

     /**
     * @var float
     *
     * @ORM\Column(name="debit", type="float", nullable=true)
     */
    private $debit = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="credit", type="float", nullable=true)
     */
    private $credit = 0;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $mode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $balanceMode;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=50, nullable = true)
     */
    private $process;


    /**
     * @var string
     *
     * @ORM\Column(name="narration", type="text", length=50, nullable = true)
     */
    private $narration;


    /**
     * @var string
     *
     * @ORM\Column(type="string",nullable = true)
     */
    private $initiatNo;


    /**
     * @var string
     *
     * @ORM\Column(type="text",  nullable = true)
     */
    private $branchName;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $receivedFrom;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $forwardingName;


     /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    private $initiatDate;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" , nullable=true)
     */
    private $isParent = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="firstChild", type="boolean" , nullable=true)
     */
    private $firstChild = false;


     /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" , nullable=true)
     */
    private $status = false;

    /**
     * @var \DateTime
     * @ORM\Column(name="reconcile_date", type="datetime", nullable=true)
     */
    private $reconcileDate;


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
     * @var \DateTime
     * @ORM\Column(name="issue_date", type="date",nullable=true)
     */
    private $issueDate;

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
    public function getAccountLedger()
    {
        return $this->accountLedger;
    }

    /**
     * @param mixed $accountLedger
     */
    public function setAccountLedger($accountLedger)
    {
        $this->accountLedger = $accountLedger;
    }

    /**
     * @return mixed
     */
    public function getAccountJournal()
    {
        return $this->accountJournal;
    }

    /**
     * @param mixed $accountJournal
     */
    public function setAccountJournal($accountJournal)
    {
        $this->accountJournal = $accountJournal;
    }

    /**
     * @return mixed
     */
    public function getAccountHead()
    {
        return $this->accountHead;
    }

    /**
     * @param mixed $accountHead
     */
    public function setAccountHead($accountHead)
    {
        $this->accountHead = $accountHead;
    }

    /**
     * @return mixed
     */
    public function getAccountSubHead()
    {
        return $this->accountSubHead;
    }

    /**
     * @param mixed $accountSubHead
     */
    public function setAccountSubHead($accountSubHead)
    {
        $this->accountSubHead = $accountSubHead;
    }

    /**
     * @return mixed
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @param mixed $bank
     */
    public function setBank($bank)
    {
        $this->bank = $bank;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
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
     * @return string
     */
    public function getBalanceMode()
    {
        return $this->balanceMode;
    }

    /**
     * @param string $balanceMode
     */
    public function setBalanceMode($balanceMode)
    {
        $this->balanceMode = $balanceMode;
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
    public function getNarration()
    {
        return $this->narration;
    }

    /**
     * @param string $narration
     */
    public function setNarration($narration)
    {
        $this->narration = $narration;
    }

    /**
     * @return string
     */
    public function getInitiatNo()
    {
        return $this->initiatNo;
    }

    /**
     * @param string $initiatNo
     */
    public function setInitiatNo($initiatNo)
    {
        $this->initiatNo = $initiatNo;
    }

    /**
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
    }

    /**
     * @return string
     */
    public function getBranchName()
    {
        return $this->branchName;
    }

    /**
     * @param string $branchName
     */
    public function setBranchName($branchName)
    {
        $this->branchName = $branchName;
    }

    /**
     * @return string
     */
    public function getReceivedFrom()
    {
        return $this->receivedFrom;
    }

    /**
     * @param string $receivedFrom
     */
    public function setReceivedFrom($receivedFrom)
    {
        $this->receivedFrom = $receivedFrom;
    }

    /**
     * @return string
     */
    public function getAccountMode()
    {
        return $this->accountMode;
    }

    /**
     * @param string $accountMode
     */
    public function setAccountMode($accountMode)
    {
        $this->accountMode = $accountMode;
    }

    /**
     * @return string
     */
    public function getForwardingName()
    {
        return $this->forwardingName;
    }

    /**
     * @param string $forwardingName
     */
    public function setForwardingName($forwardingName)
    {
        $this->forwardingName = $forwardingName;
    }

    /**
     * @return \DateTime
     */
    public function getInitiatDate()
    {
        return $this->initiatDate;
    }

    /**
     * @param \DateTime $initiatDate
     */
    public function setInitiatDate($initiatDate)
    {
        $this->initiatDate = $initiatDate;
    }

    /**
     * @return bool
     */
    public function isParent()
    {
        return $this->isParent;
    }

    /**
     * @param bool $isParent
     */
    public function setIsParent($isParent)
    {
        $this->isParent = $isParent;
    }

    /**
     * @return bool
     */
    public function isFirstChild()
    {
        return $this->firstChild;
    }

    /**
     * @param bool $firstChild
     */
    public function setFirstChild($firstChild)
    {
        $this->firstChild = $firstChild;
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
    public function getReconcileDate()
    {
        return $this->reconcileDate;
    }

    /**
     * @param \DateTime $reconcileDate
     */
    public function setReconcileDate($reconcileDate)
    {
        $this->reconcileDate = $reconcileDate;
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
     * @return mixed
     */
    public function getStockItemReconciliation()
    {
        return $this->stockItemReconciliation;
    }

    /**
     * @param mixed $stockItemReconciliation
     */
    public function setStockItemReconciliation($stockItemReconciliation)
    {
        $this->stockItemReconciliation = $stockItemReconciliation;
    }





}

