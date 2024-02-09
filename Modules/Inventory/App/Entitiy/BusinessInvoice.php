<?php

namespace Appstore\Bundle\BusinessBundle\Entity;

use Appstore\Bundle\AccountingBundle\Entity\AccountBank;
use Appstore\Bundle\AccountingBundle\Entity\AccountCondition;
use Appstore\Bundle\AccountingBundle\Entity\AccountMobileBank;
use Appstore\Bundle\AccountingBundle\Entity\AccountSales;
use Appstore\Bundle\AccountingBundle\Entity\AccountVendor;
use Appstore\Bundle\DomainUserBundle\Entity\Customer;
use Core\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Setting\Bundle\ToolBundle\Entity\Bank;
use Setting\Bundle\ToolBundle\Entity\PaymentCard;
use Setting\Bundle\ToolBundle\Entity\TransactionMethod;

/**
 * Invoice
 *
 * @ORM\Table( name ="business_invoice")
 * @ORM\Entity(repositoryClass="Appstore\Bundle\BusinessBundle\Repository\BusinessInvoiceRepository")
 */
class BusinessInvoice
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
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessConfig", inversedBy="businessInvoices")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $businessConfig;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessAndroidProcess", inversedBy="sales")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $androidProcess;


    /**
     * @ORM\OneToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessReverse", mappedBy="businessInvoice")
     **/
    private $businessReverse;

     /**
     * @ORM\OneToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessInvoiceReturn", mappedBy="businessInvoice")
     **/
    private $invoiceReturn;

    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessInvoiceParticular", mappedBy="businessInvoice" , cascade={"remove"} )
     * @ORM\OrderBy({"id" = "ASC"})
     **/
    private  $businessInvoiceParticulars;

     /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessInvoiceReturnItem", mappedBy="invoice" , cascade={"remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     **/
    private  $invoiceReturnItems;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\DomainUserBundle\Entity\Customer", inversedBy="businessInvoices" ,cascade={"persist"} )
     **/
    private  $customer;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\AccountingBundle\Entity\AccountVendor",cascade={"persist"} )
     **/
    private  $vendor;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User", inversedBy="businessInvoiceCreatedBy" )
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User", inversedBy="salesUser" )
     **/
    private $salesBy;

    /**
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User", inversedBy="businessInvoiceApprovedBy" )
     **/
    private  $approvedBy;

    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\AccountingBundle\Entity\AccountSales", mappedBy="businessInvoice" )
     **/
    private  $accountSales;

    /**
     * @ORM\ManyToOne(targetEntity="Setting\Bundle\ToolBundle\Entity\TransactionMethod", inversedBy="businessInvoice" )
     **/
    private  $transactionMethod;

    /**
     * @ORM\ManyToOne(targetEntity="Setting\Bundle\ToolBundle\Entity\Bank", inversedBy="businessInvoice" )
     **/
    private  $bank;

    /**
     * @ORM\ManyToOne(targetEntity="Setting\Bundle\ToolBundle\Entity\PaymentCard", inversedBy="businessInvoice" )
     **/
    private  $paymentCard;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\AccountingBundle\Entity\AccountBank", inversedBy="businessInvoice" )
     **/
    private  $accountBank;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\AccountingBundle\Entity\AccountMobileBank", inversedBy="businessInvoice" )
     **/
    private  $accountMobileBank;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\Marketing", inversedBy="invoices" )
     **/
    private  $marketing;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\AccountingBundle\Entity\AccountCondition")
     **/
    private  $condition;

    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\AccountingBundle\Entity\AccountConditionLedger", mappedBy="invoice" )
     **/
    private  $conditionLedger;

    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessStoreLedger", mappedBy="invoice", cascade={"remove"} )
     **/
    private  $storeLedgers;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessArea", inversedBy="invoices" )
     **/
    private  $area;

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
     * @ORM\Column(name="venue", type="string", length=256, nullable=true)
     */
    private $venue;


    /**
     * @var string
     *
     * @ORM\Column(name="paymentInWord", type="string", length=255, nullable=true)
     */
    private $paymentInWord;


    /**
     * @var string
     *
     * @ORM\Column(name="transactionId", type="string", length=100, nullable=true)
     */
    private $transactionId;

    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=50, nullable=true)
     */
    private $process ='Created';

    /**
     * @var string
     *
     * @ORM\Column(name="invoice", type="string", length=50, nullable=true)
     */
    private $invoice;


     /**
     * @var string
     *
     * @ORM\Column(name="deviceSalesId", type="string", length=50, nullable=true)
     */
    private $deviceSalesId;


    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;


    /**
     * @var string
     *
     * @ORM\Column(name="paymentStatus", type="string", length=50, nullable=true)
     */
    private $paymentStatus = "Pending";

    /**
     * @var string
     *
     * @ORM\Column(name="discountType", type="string", length=20, nullable=true)
     */
    private $discountType ='';

    /**
     * @var float
     *
     * @ORM\Column(name="discountCalculation", type="float" , nullable=true)
     */
    private $discountCalculation;


    /**
     * @var float
     *
     * @ORM\Column(name="subTotal", type="float", nullable=true)
     */
    private $subTotal;


    /**
     * @var float
     *
     * @ORM\Column(name="discount", type="float", nullable=true)
     */
    private $discount;

    /**
     * @var float
     *
     * @ORM\Column(name="vat", type="float", nullable=true)
     */
    private $vat;

     /**
     * @var float
     *
     * @ORM\Column(name="ait", type="float", nullable=true)
     */
    private $ait;

    /**
     * @var float
     *
     * @ORM\Column(name="total", type="float", nullable=true)
     */
    private $total;

    /**
     * @var float
     *
     * @ORM\Column(name="tloPrice", type="float", nullable=true)
     */
    private $tloPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="sr_commission", type="float", nullable=true)
     */
    private $srCommission;


    /**
     * @var float
     *
     * @ORM\Column(name="payment", type="float", nullable=true)
     */
    private $payment;

    /**
     * @var float
     *
     * @ORM\Column(name="received", type="float", nullable=true)
     */
    private $received;

    /**
     * @var float
     *
     * @ORM\Column(name="commission", type="float", nullable=true)
     */
    private $commission;


    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;


    /**
     * @var float
     *
     * @ORM\Column(name="salesReturn", type="float", nullable=true)
     */
    private $salesReturn;


    /**
     * @var float
     *
     * @ORM\Column(name="due", type="float", nullable=true)
     */
    private $due;


    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="text", nullable=true)
     */
    private $mobile;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isReversed", type="boolean", nullable=true)
     */
    private $isReversed;

    /**
     * @var string
     *
     * @ORM\Column(name="eventType", type="string", length=50, nullable=true)
     */
    private $eventType;

    /**
     * @var \DateTime
     * @ORM\Column(name="startDate", type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @var boolean
     * @ORM\Column(name="isCondition", type="boolean", nullable=true)
     */
    private $isCondition;

    /**
     * @var boolean
     * @ORM\Column(name="printPreviousDue", type="boolean", nullable=true)
     */
    private $printPreviousDue = true;

    /**
     * @var \DateTime
     * @ORM\Column(name="endDate", type="datetime",  nullable=true)
     */
    private $endDate;


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
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * @return float
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @param float $vat
     */
    public function setVat($vat)
    {
        $this->vat = $vat;
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
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param string $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    /**
     * @return string
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @param string $paymentStatus
     * Paid
     * Pending
     * Partial
     * Due
     * Other
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
    }


    /**
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param integer $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param string $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }


    /**
     * @return string
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param string $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }



    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
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
    public function getPaymentInWord()
    {
        return $this->paymentInWord;
    }

    /**
     * @param string $paymentInWord
     */
    public function setPaymentInWord($paymentInWord)
    {
        $this->paymentInWord = $paymentInWord;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
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
     * @return User
     */
    public function getApprovedBy()
    {
        return $this->approvedBy;
    }

    /**
     * @param User $approvedBy
     */
    public function setApprovedBy($approvedBy)
    {
        $this->approvedBy = $approvedBy;
    }

    /**
     * @return string
     */
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * @param string $commission
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;
    }

    /**
     * @return BusinessInvoiceParticular
     */
    public function getBusinessInvoiceParticulars()
    {
        return $this->businessInvoiceParticulars;
    }

    /**
     * @return string
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }

    /**
     * @param string $discountType
     */
    public function setDiscountType($discountType)
    {
        $this->discountType = $discountType;
    }

    /**
     * @return float
     */
    public function getDiscountCalculation()
    {
        return $this->discountCalculation;
    }

    /**
     * @param float $discountCalculation
     */
    public function setDiscountCalculation($discountCalculation)
    {
        $this->discountCalculation = $discountCalculation;
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
     * @return AccountMobileBank
     */
    public function getAccountMobileBank()
    {
        return $this->accountMobileBank;
    }

    /**
     * @param AccountMobileBank $accountMobileBank
     */
    public function setAccountMobileBank($accountMobileBank)
    {
        $this->accountMobileBank = $accountMobileBank;
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
     * @return Bank
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @param Bank $bank
     */
    public function setBank($bank)
    {
        $this->bank = $bank;
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
     * @return AccountSales
     */
    public function getAccountSales()
    {
        return $this->accountSales;
    }

    /**
     * @return PaymentCard
     */
    public function getPaymentCard()
    {
        return $this->paymentCard;
    }

    /**
     * @param PaymentCard $paymentCard
     */
    public function setPaymentCard($paymentCard)
    {
        $this->paymentCard = $paymentCard;
    }

    /**
     * @return User
     */
    public function getSalesBy()
    {
        return $this->salesBy;
    }

    /**
     * @param User $salesBy
     */
    public function setSalesBy($salesBy)
    {
        $this->salesBy = $salesBy;
    }

    /**
     * @return string
     */
    public function getReceived()
    {
        return $this->received;
    }

    /**
     * @param string $received
     */
    public function setReceived($received)
    {
        $this->received = $received;
    }

	/**
	 * @return BusinessReverse
	 */
	public function getBusinessReverse() {
		return $this->businessReverse;
	}

	/**
	 * @return bool
	 */
	public function isReversed(){
		return $this->isReversed;
	}

	/**
	 * @param bool $isReversed
	 */
	public function setIsReversed( bool $isReversed ) {
		$this->isReversed = $isReversed;
	}

    /**
     * @return string
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @param string $eventType
     */
    public function setEventType(string $eventType)
    {
        $this->eventType = $eventType;
    }

    /**
     * @param \DateTime
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
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return BusinessAndroidProcess
     */
    public function getAndroidProcess()
    {
        return $this->androidProcess;
    }

    /**
     * @param BusinessAndroidProcess $androidProcess
     */
    public function setAndroidProcess($androidProcess)
    {
        $this->androidProcess = $androidProcess;
    }

    /**
     * @return string
     */
    public function getVenue()
    {
        return $this->venue;
    }

    /**
     * @param string $venue
     */

    public function setVenue($venue)
    {
        $this->venue = $venue;
    }

    /**
     * @return BusinessArea
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param BusinessArea $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @return Marketing
     */
    public function getMarketing()
    {
        return $this->marketing;
    }

    /**
     * @param Marketing $marketing
     */
    public function setMarketing($marketing)
    {
        $this->marketing = $marketing;
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
     * @return float
     */
    public function getDue()
    {
        return $this->due;
    }

    /**
     * @param float $due
     */
    public function setDue($due)
    {
        $this->due = $due;
    }

    /**
     * @return bool
     */
    public function isCondition()
    {
        return $this->isCondition;
    }

    /**
     * @param bool $isCondition
     */
    public function setIsCondition($isCondition)
    {
        $this->isCondition = $isCondition;
    }

    /**
     * @return mixed
     */
    public function getCourier()
    {
        return $this->courier;
    }

    /**
     * @param mixed $courier
     */
    public function setCourier($courier)
    {
        $this->courier = $courier;
    }

    /**
     * @return float
     */
    public function getSalesReturn()
    {
        return $this->salesReturn;
    }

    /**
     * @param float $salesReturn
     */
    public function setSalesReturn($salesReturn)
    {
        $this->salesReturn = $salesReturn;
    }

    /**
     * @return mixed
     */
    public function getStoreLedgers()
    {
        return $this->storeLedgers;
    }

    /**
     * @return mixed
     */
    public function getInvoiceReturnItems()
    {
        return $this->invoiceReturnItems;
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
     * @return mixed
     */
    public function getInvoiceReturn()
    {
        return $this->invoiceReturn;
    }

    /**
     * @return bool
     */
    public function isPrintPreviousDue()
    {
        return $this->printPreviousDue;
    }

    /**
     * @param bool $printPreviousDue
     */
    public function setPrintPreviousDue($printPreviousDue)
    {
        $this->printPreviousDue = $printPreviousDue;
    }

    /**
     * @return AccountCondition
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param AccountCondition $condition
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    /**
     * @return mixed
     */
    public function getConditionLedger()
    {
        return $this->conditionLedger;
    }

    /**
     * @return float
     */
    public function getAit()
    {
        return $this->ait;
    }

    /**
     * @param float $ait
     */
    public function setAit($ait)
    {
        $this->ait = $ait;
    }

    /**
     * @return string
     */
    public function getDeviceSalesId()
    {
        return $this->deviceSalesId;
    }

    /**
     * @param string $deviceSalesId
     */
    public function setDeviceSalesId($deviceSalesId)
    {
        $this->deviceSalesId = $deviceSalesId;
    }





}

