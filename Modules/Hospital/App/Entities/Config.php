<?php

namespace Modules\Hospital\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * HospitalConfig
 *
 * @ORM\Table( name ="hms_config")
 * @ORM\Entity()
 */
class Config
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
     *
     * @ORM\OneToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $domain;

    /**
     *
     * @ORM\OneToOne(targetEntity="Modules\Core\App\Entities\Warehouse", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $ipdStore;

    /**
     *
     * @ORM\OneToOne(targetEntity="Modules\Core\App\Entities\Warehouse", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $opdStore;

    /**
     *
     * @ORM\OneToOne(targetEntity="Modules\Core\App\Entities\Warehouse", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $otStore;


    /**
     *
     * @ORM\OneToOne(targetEntity="Particular", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $admissionFee;


    /**
     *
     * @ORM\OneToOne(targetEntity="Particular", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $emergencyFee;

     /**
     *
     * @ORM\OneToOne(targetEntity="Particular", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $emergencyRoom;

    /**
     *
     * @ORM\OneToOne(targetEntity="Particular", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $opdTicketFee;

    /**
     *
     * @ORM\OneToOne(targetEntity="Particular", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $otFee;

    /**
     * @var smallint
     *
     * @ORM\Column( type="smallint",  nullable=true)
     */
    private $minimumDaysRoomRent = 3;

    /**
     * @var string
     *
     * @ORM\Column( type="string", length=50,nullable = true)
     */
    private $prescriptionTemplate;


    /**
     * @var string
     *
     * @ORM\Column( type="string", length=50,nullable = true)
     */
    private $printer;

    /**
     * @var smallint
     *
     * @ORM\Column( type="smallint",  nullable=true)
     */
    private $vatPercentage;


    /**
     * @var smallint
     *
     * @ORM\Column( type="smallint",  nullable=true)
     */
    private $fontSizeLabel;

    /**
     * @var smallint
     *
     * @ORM\Column( type="smallint",  nullable = true)
     */
    private $fontSizeValue;

    /**
     * @var string
     *
     * @ORM\Column(type="string",  nullable = true)
     */
    private $vatRegNo;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable = true)
     */
    private $healthShare = false;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable = true)
     */
    private $isMultiPayment = false;


     /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable = true)
     */
    private $isInventory = false;

     /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable = true)
     */
    private $advanceSearchParticular = false;

     /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable = true)
     */
    private $isMarketingExecutive = false;

     /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable = true)
     */
    private $appointmentPrescription = false;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable = true)
     */
    private $initialDiagnosticShow = true;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable = true)
     */
    private $barcodePrint = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable = true)
     */
    private $customPrint = false;


    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable = true)
     */
    private $printOff = false;


    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable = true)
     */
    private $commissionAutoApproved = false;


    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable=true)
     */
    private $isBranchInvoice = false;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable=true)
     */
    private $vatEnable = false;

    /**
     * @var smallint
     *
     * @ORM\Column( type="smallint",  nullable=true)
     */
    private $printTopMargin = 0;


    /**
     * @var smallint
     *
     * @ORM\Column( type="smallint",  nullable=true)
     */
    private $printMarginBottom = 0;

    /**
     * @var smallint
     *
     * @ORM\Column( type="smallint",  nullable=true)
     */
    private $printMarginReportTop = 0;

    /**
     * @var smallint
     *
     * @ORM\Column( type="smallint",  nullable=true)
     */
    private $printMarginReportLeft = 0;

    /**
     * @var smallint
     *
     * @ORM\Column( type="smallint",  nullable=true)
     */
    private $minimumAdmissionDays = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $isPrintHeader = true;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable=true)
     */
    private $isInvoiceTitle = true;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $isPrintFooter = true;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable=true)
     */
    private $isPrintReportHeader = true;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10,nullable = true)
     */
    private $invoicePrefix;


    /**
     * @var string
     *
     * @ORM\Column( type="string", length=10,nullable = true)
     */
    private $customerPrefix;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text",nullable = true)
     */
    private $address;


    /**
     * @var string
     *
     * @ORM\Column(type="text",nullable = true)
     */
    private $messageDiagnostic;


     /**
     * @var string
     *
     * @ORM\Column(type="text",nullable = true)
     */
    private $messageAdmission;


     /**
     * @var string
     *
     * @ORM\Column(type="text",nullable = true)
     */
    private $messageVisit;


    /**
     * @var smallint
     *
     * @ORM\Column( type="smallint", nullable = true)
     */
    private $printLeftMargin = 0;


    /**
     * @var integer
     *
     * @ORM\Column( type="integer", nullable = true)
     */
    private $invoiceHeight = 0;

    /**
     * @var integer
     *
     * @ORM\Column( type="integer", nullable = true)
     */
    private $reportHeight = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="consultant_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $consultantBy;

    /**
     * @var boolean
     *
     * @ORM\Column(name="prescriptionBuilder", type="boolean",  nullable=true)
     */
    private $prescriptionBuilder = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true ,options={"default"="true"})
     */
    private $invoicePrintLogo = true;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true,options={"default"="true"})
     */
    private $printInstruction = true;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",nullable=true,options={"default"="false"})
     */
    private $prescriptionShowSimilarProduct;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",nullable=true,options={"default"="false"})
     */
    private $prescriptionShowMarketingExecutive;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",nullable=true,options={"default"="false"})
     */
    private $prescriptionShowReferred;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",nullable=true,options={"default"="false"})
     */
    private $opdSelectDoctor;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",nullable=true,options={"default"="false"})
     */
    private $specialDiscountDoctor;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",nullable=true,options={"default"="false"})
     */
    private $amountSplit;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",nullable=true,options={"default"="false"})
     */
    private $specialDiscountInvestigation;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $headerPath;


   /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $footerPath;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $cssContent;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


}

