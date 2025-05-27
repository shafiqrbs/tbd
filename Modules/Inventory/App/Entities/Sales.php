<?php

namespace Modules\Inventory\App\Entities;



use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Sales
 *
 * @ORM\Table( name ="inv_sales")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\SalesRepository")
 */
class Sales
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;

    /**
     * @ORM\ManyToOne(targetEntity="InvoiceBatch")
     * @ORM\JoinColumn(name="invoice_batch_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $invoiceBatch;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Customer")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $customer;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Vendor")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $vendor;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $salesBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $approvedBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Accounting\App\Entities\TransactionMode" ,cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     **/
    private $transactionMode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $paymentInWord;


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
     * @ORM\Column(type="string", length=50, nullable=true)
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
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $paymentStatus = "Pending";

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $discountType ='';

    /**
     * @var float
     *
     * @ORM\Column(type="float" , nullable=true)
     */
    private $discountCalculation;


    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable=true)
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
     * @ORM\Column( type="float", nullable=true)
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
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isReversed;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $eventType;

    /**
     * @var \DateTime
     * @ORM\Column(name="startDate", type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isCondition;

    /**
     * @var boolean
     * @ORM\Column(type="boolean",  options={"default"="0"},nullable=true)
     */
    private $isMultiPayment;



     /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isOnline;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $printPreviousDue = true;


    /**
     * @var \Date
     * @ORM\Column(type="date", nullable=true)
     */
    private $invoiceDate;

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
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isDomainSalesCompleted = false;


    /**
     * @var string
     * @ORM\Column(name="sales_form", type="text", nullable=true)
     */
    private $salesForm;

    /**
     * @var string
     * @ORM\Column(name="narration", type="text", nullable=true)
     */
    private $narration;


}

