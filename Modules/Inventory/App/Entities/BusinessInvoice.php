<?php

namespace Modules\Inventory\App\Entities;



use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Invoice
 *
 * @ORM\Table( name ="inv_invoice")
 * @ORM\Entity()
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessAndroidProcess")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $androidProcess;


    /**
     * @ORM\OneToOne(targetEntity="Modules\Inventory\App\Entities\BusinessReverse")
     **/
    private $businessReverse;

     /**
     * @ORM\OneToOne(targetEntity="Modules\Inventory\App\Entities\BusinessInvoiceReturn")
     **/
    private $invoiceReturn;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Customer")
     **/
    private  $customer;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Vendor",cascade={"persist"} )
     **/
    private  $vendor;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private $salesBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $approvedBy;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\TransactionMethod")
     **/
    private  $transactionMethod;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Marketing")
     **/
    private  $marketing;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessArea")
     **/
    private  $area;


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
     * @ORM\Column(type="string", length=100, nullable=true)
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
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $printPreviousDue = true;



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



}

