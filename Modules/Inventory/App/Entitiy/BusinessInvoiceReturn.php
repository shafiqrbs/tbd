<?php

namespace Appstore\Bundle\BusinessBundle\Entity;

use Appstore\Bundle\AccountingBundle\Entity\AccountVendor;
use Core\UserBundle\Entity\User;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * BusinessInvoiceReturn
 *
 * @ORM\Table( name ="business_invoice_return")
 * @ORM\Entity(repositoryClass="Appstore\Bundle\BusinessBundle\Repository\BusinessInvoiceReturnRepository")
 */
class BusinessInvoiceReturn
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessConfig", inversedBy="businessPurchasesReturns" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $businessConfig;

    /**
     * @ORM\OneToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessInvoice", inversedBy="invoiceReturn")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $businessInvoice;

    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessInvoiceReturnItem", mappedBy="invoiceReturn" , cascade={"remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     **/
    private  $invoiceReturnItems;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\DomainUserBundle\Entity\Customer", inversedBy="businessInvoices" ,cascade={"persist"} )
     **/
    private  $customer;


    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User")
     **/
    private  $createdBy;


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
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;

     /**
     * @var string
     *
     * @ORM\Column(name="invoice", type="string",  nullable=true)
     */
    private $invoice;

    /**
     * @var string
     *
     * @ORM\Column(name="mode", type="string",  nullable=true)
     */
    private $mode = "adjustment";

    /**
     * @var float
     *
     * @ORM\Column(name="subTotal", type="float", nullable=true)
     */
    private $subTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="adjustment", type="float", nullable=true)
     */
    private $adjustment=0;

    /**
     * @var float
     *
     * @ORM\Column(name="payment", type="float", nullable=true)
     */
    private $payment=0;

    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", nullable=true)
     */
    private $process = "created";

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
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
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
     * created
     * progress
     * complete
     * approved
     */
    public function setProcess($process)
    {
        $this->process = $process;
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
    public function getInvoiceReturnItems()
    {
        return $this->invoiceReturnItems;
    }

    /**
     * @param mixed $invoiceReturnItems
     */
    public function setInvoiceReturnItems($invoiceReturnItems)
    {
        $this->invoiceReturnItems = $invoiceReturnItems;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
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
    public function getAdjustment()
    {
        return $this->adjustment;
    }

    /**
     * @param float $adjustment
     */
    public function setAdjustment($adjustment)
    {
        $this->adjustment = $adjustment;
    }

    /**
     * @return float
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param float $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return mixed
     */
    public function getBusinessInvoice()
    {
        return $this->businessInvoice;
    }

    /**
     * @param mixed $businessInvoice
     */
    public function setBusinessInvoice($businessInvoice)
    {
        $this->businessInvoice = $businessInvoice;
    }


}

