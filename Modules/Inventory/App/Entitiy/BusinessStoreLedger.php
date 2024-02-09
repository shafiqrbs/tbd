<?php

namespace Appstore\Bundle\BusinessBundle\Entity;

use Appstore\Bundle\AccountingBundle\Entity\AccountBank;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Setting\Bundle\ToolBundle\Entity\TransactionMethod;

/**
 * Courier
 *
 * @ORM\Table( name ="business_store_ledger")
 * @ORM\Entity(repositoryClass="Appstore\Bundle\BusinessBundle\Repository\BusinessStoreLedgerRepository")
 */
class BusinessStoreLedger
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
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessConfig", inversedBy="storeLedgers", cascade={"detach","merge"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected $businessConfig;

     /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessInvoice",inversedBy="storeLedgers")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"id" = "ASC"})
     **/
    protected $invoice;

     /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessStore", inversedBy="storeLedgers")
     **/
    protected $store;

    /**
     * @ORM\ManyToOne(targetEntity="Setting\Bundle\ToolBundle\Entity\TransactionMethod")
     **/
    private  $transactionMethod;

    /**
     * @ORM\ManyToOne(targetEntity="Setting\Bundle\ToolBundle\Entity\PaymentCard", inversedBy="businessInvoice" )
     **/
    private  $paymentCard;


    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\AccountingBundle\Entity\AccountBank")
     **/
    private  $accountBank;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\AccountingBundle\Entity\AccountMobileBank")
     **/
    private  $accountMobileBank;

    /**
     * @var string
     *
     * @ORM\Column(name="cardNo", type="string", length=100, nullable=true)
     */
    private $cardNo;

    /**
     * @var string
     *
     * @ORM\Column(name="paymentMobile", type="string", length=50, nullable=true)
     */
    private $paymentMobile;


    /**
     * @var string
     *
     * @ORM\Column(name="transactionId", type="string", length=100, nullable=true)
     */
    private $transactionId;



    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float",  nullable = true)
     */
    private $amount;

    /**
     * @var float
     *
     * @ORM\Column(name="debit", type="float",  nullable = true)
     */
    private $debit;


    /**
     * @var float
     *
     * @ORM\Column(name="credit", type="float",  nullable = true)
     */
    private $credit;


    /**
     * @var float
     *
     * @ORM\Column(name="balance", type="float",  nullable = true)
     */
    private $balance;

    /**
     * @var string
     *
     * @ORM\Column(name="transactionType", type="string", length=25, nullable = true)
     */
    private $transactionType = 'Debit';

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User", inversedBy="businessInvoiceCreatedBy" )
     **/
    private  $createdBy;


    /**
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User", inversedBy="businessInvoiceApprovedBy" )
     **/
    private  $approvedBy;



    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;


     /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean",  nullable=true)
     */
    private $status = false;


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
     * Set amount
     *
     * @param float $amount
     *
     * @return accountLoan
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
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
     * @return TransactionMethod
     */
    public function getTransactionMethod()
    {
        return $this->transactionMethod;
    }

    /**
     * @param TransactionMethod $transactionMethod
     */
    public function setTransactionMethod($transactionMethod)
    {
        $this->transactionMethod = $transactionMethod;
    }

    /**
     * @return AccountBank
     */
    public function getAccountBank()
    {
        return $this->accountBank;
    }

    /**
     * @param AccountBank $accountBank
     */
    public function setAccountBank($accountBank)
    {
        $this->accountBank = $accountBank;
    }


    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param mixed $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
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
     * @return mixed
     */
    public function getBusinessConfig()
    {
        return $this->businessConfig;
    }

    /**
     * @param mixed $businessConfig
     */
    public function setBusinessConfig($businessConfig)
    {
        $this->businessConfig = $businessConfig;
    }

    /**
     * @return mixed
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param mixed $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return string
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * @param string $transactionType
     */
    public function setTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;
    }

    /**
     * @return mixed
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @param mixed $store
     */
    public function setStore($store)
    {
        $this->store = $store;
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
     * @return string
     */
    public function getCardNo()
    {
        return $this->cardNo;
    }

    /**
     * @param string $cardNo
     */
    public function setCardNo($cardNo)
    {
        $this->cardNo = $cardNo;
    }

    /**
     * @return string
     */
    public function getPaymentMobile()
    {
        return $this->paymentMobile;
    }

    /**
     * @param string $paymentMobile
     */
    public function setPaymentMobile($paymentMobile)
    {
        $this->paymentMobile = $paymentMobile;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return mixed
     */
    public function getPaymentCard()
    {
        return $this->paymentCard;
    }

    /**
     * @param mixed $paymentCard
     */
    public function setPaymentCard($paymentCard)
    {
        $this->paymentCard = $paymentCard;
    }

    /**
     * @return mixed
     */
    public function getAccountMobileBank()
    {
        return $this->accountMobileBank;
    }

    /**
     * @param mixed $accountMobileBank
     */
    public function setAccountMobileBank($accountMobileBank)
    {
        $this->accountMobileBank = $accountMobileBank;
    }






}

