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
     * @var string
     *
     * @ORM\Column(name="printer", type="string", length=50,nullable = true)
     */
    private $printer;

    /**
     * @var smallint
     *
     * @ORM\Column(name="vatPercentage", type="smallint",  nullable=true)
     */
    private $vatPercentage;

    /**
     * @var smallint
     *
     * @ORM\Column(name="fontSizeLabel", type="smallint",  nullable=true)
     */
    private $fontSizeLabel;

    /**
     * @var smallint
     *
     * @ORM\Column(name="fontSizeValue", type="smallint",  nullable = true)
     */
    private $fontSizeValue;

    /**
     * @var string
     *
     * @ORM\Column(name="vatRegNo", type="string",  nullable = true)
     */
    private $vatRegNo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isBranch", type="boolean",  nullable = true)
     */
    private $isBranch = false;

     /**
     * @var boolean
     *
     * @ORM\Column(name="isInventory", type="boolean",  nullable = true)
     */
    private $isInventory = false;

     /**
     * @var boolean
     *
     * @ORM\Column(name="advanceSearchParticular", type="boolean",  nullable = true)
     */
    private $advanceSearchParticular = false;

     /**
     * @var boolean
     *
     * @ORM\Column(name="isMarketingExecutive", type="boolean",  nullable = true)
     */
    private $isMarketingExecutive = false;

     /**
     * @var boolean
     *
     * @ORM\Column(name="appointmentPrescription", type="boolean",  nullable = true)
     */
    private $appointmentPrescription = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="initialDiagnosticShow", type="boolean",  nullable = true)
     */
    private $initialDiagnosticShow = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="barcodePrint", type="boolean",  nullable = true)
     */
    private $barcodePrint = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="customPrint", type="boolean",  nullable = true)
     */
    private $customPrint = false;


    /**
     * @var boolean
     *
     * @ORM\Column(name="printOff", type="boolean",  nullable = true)
     */
    private $printOff = false;


    /**
     * @var boolean
     *
     * @ORM\Column(name="commissionAutoApproved", type="boolean",  nullable = true)
     */
    private $commissionAutoApproved = false;


    /**
     * @var boolean
     *
     * @ORM\Column(name="isBranchInvoice", type="boolean",  nullable=true)
     */
    private $isBranchInvoice = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="vatEnable", type="boolean",  nullable=true)
     */
    private $vatEnable = false;

    /**
     * @var smallint
     *
     * @ORM\Column(name="printMarginTop", type="smallint",  nullable=true)
     */
    private $printTopMargin = 0;


    /**
     * @var smallint
     *
     * @ORM\Column(name="printMarginBottom", type="smallint",  nullable=true)
     */
    private $printMarginBottom = 0;

    /**
     * @var smallint
     *
     * @ORM\Column(name="printMarginReportTop", type="smallint",  nullable=true)
     */
    private $printMarginReportTop = 0;

    /**
     * @var smallint
     *
     * @ORM\Column(name="printMarginReportLeft", type="smallint",  nullable=true)
     */
    private $printMarginReportLeft = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isPrintHeader", type="boolean",  nullable=true)
     */
    private $isPrintHeader = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isInvoiceTitle", type="boolean",  nullable=true)
     */
    private $isInvoiceTitle = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isPrintFooter", type="boolean",  nullable=true)
     */
    private $isPrintFooter = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isPrintReportHeader", type="boolean",  nullable=true)
     */
    private $isPrintReportHeader = true;


    /**
     * @var string
     *
     * @ORM\Column(name="invoicePrefix", type="string", length=10,nullable = true)
     */
    private $invoicePrefix;

    /**
     * @var array
     *
     * @ORM\Column(name="invoiceProcess", type="array", nullable = true)
     */
    private $invoiceProcess;

    /**
     * @var string
     *
     * @ORM\Column(name="customerPrefix", type="string", length=10,nullable = true)
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
     * @ORM\Column(name="messageDiagnostic", type="text",nullable = true)
     */
    private $messageDiagnostic;


     /**
     * @var string
     *
     * @ORM\Column(name="messageAdmission", type="text",nullable = true)
     */
    private $messageAdmission;


     /**
     * @var string
     *
     * @ORM\Column(name="messageVisit", type="text",nullable = true)
     */
    private $messageVisit;


    /**
     * @var smallint
     *
     * @ORM\Column(name="printLeftMargin", type="smallint", nullable = true)
     */
    private $printLeftMargin = 0;


    /**
     * @var integer
     *
     * @ORM\Column(name="invoiceHeight", type="integer", nullable = true)
     */
    private $invoiceHeight = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="reportHeight", type="integer", nullable = true)
     */
    private $reportHeight = 0;



    /**
     * @var boolean
     *
     * @ORM\Column(name="prescriptionBuilder", type="boolean",  nullable=true)
     */
    private $prescriptionBuilder = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="invoicePrintLogo", type="boolean",  nullable=true)
     */
    private $invoicePrintLogo = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="printInstruction", type="boolean",  nullable=true)
     */
    private $printInstruction = true;


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

