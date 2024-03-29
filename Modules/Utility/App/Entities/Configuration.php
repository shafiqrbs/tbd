<?php

namespace Modules\Utility\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;


/**
 * BusinessConfig
 *
 * @ORM\Table( name ="uti_configuration")
 * @ORM\Entity(repositoryClass="Modules\Utility\App\Repositories\ConfigurationRepository")
 */
class Configuration
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
     * @ORM\OneToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption",cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $domain;


    /**
     * @var array
     *
     * @ORM\Column(name="stockFormat", type="array", length=50,nullable = true)
     */
    private $stockFormat;

    /**
     * @var string
     *
     * @ORM\Column(name="printer", type="string", length=50,nullable = true)
     */
    private $printer;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text", nullable = true)
     */
    private $address;

     /**
     * @var string
     *
     * @ORM\Column(name="printFooterText", type="text", nullable = true)
     */
    private $printFooterText;

    /**
     * @var string
     *
     * @ORM\Column(name="invoiceComment", type="text", nullable = true)
     */
    private $invoiceComment;

    /**
     * @var smallint
     *
     * @ORM\Column(name="vatPercentage", type="smallint",  nullable=true)
     */
    private $vatPercentage;

    /**
     * @var smallint
     *
     * @ORM\Column(name="vatPercent", type="smallint",  nullable=true)
     */
    private $vatPercent;

    /**
     * @var smallint
     *
     * @ORM\Column(name="aitPercent", type="smallint",  nullable=true)
     */
    private $aitPercent;

    /**
     * @var smallint
     *
     * @ORM\Column(name="fontSizeLabel", type="smallint",  nullable=true)
     */
    private $fontSizeLabel;

    /**
     * @var smallint
     *
     * @ORM\Column(name="fontSizeValue", type="smallint",  nullable=true)
     */
    private $fontSizeValue;

    /**
     * @var string
     *
     * @ORM\Column(name="vatRegNo", type="string",  nullable=true)
     */
    private $vatRegNo;


    /**
     * @var string
     *
     * @ORM\Column(name="businessModel", length=100, type="string",  nullable=true)
     */
    private $businessModel='general';


     /**
     * @var float
     *
     * @ORM\Column(name="unitCommission", type="float",  nullable=true)
     */
     private $unitCommission = 0;


    /**
     * @var boolean
     *
     * @ORM\Column(name="vatEnable", type="boolean",  nullable=true)
     */
    private $vatEnable = false;

     /**
     * @var boolean
     *
     * @ORM\Column(name="bonusFromStock", type="boolean",  nullable=true)
     */
    private $bonusFromStock = false;

     /**
     * @var boolean
     *
     * @ORM\Column(name="conditionSales", type="boolean",  nullable=true)
     */
    private $conditionSales = false;

      /**
     * @var boolean
     *
     * @ORM\Column(name="isMarketingExecutive", type="boolean",  nullable=true)
     */
    private $isMarketingExecutive = false;

     /**
     * @var boolean
     *
     * @ORM\Column(name="isDescription", type="boolean",  nullable=true)
     */
    private $isDescription = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="posPrint", type="boolean",  nullable=true)
     */
    private $posPrint = false;

     /**
     * @var boolean
     *
     * @ORM\Column(name="fuelStation", type="boolean",  nullable=true)
     */
    private $fuelStation = false;

     /**
     * @var boolean
     *
     * @ORM\Column(name="zeroStock", type="boolean",  nullable=true)
     */
    private $zeroStock = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="systemReset", type="boolean",  nullable=true)
     */
    private $systemReset = false;


    /**
     * @var boolean
     *
     * @ORM\Column(name="tloCommission", type="boolean",  nullable=true)
     */
    private $tloCommission = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="srCommission", type="boolean",  nullable=true)
     */
    private $srCommission = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="salesReturn", type="boolean",  nullable=true)
     */
    private $salesReturn = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="storeLedger", type="boolean",  nullable=true)
     */
    private $storeLedger = false;

    /**
     * @var smallint
     *
     * @ORM\Column(name="invoiceWidth", type="smallint",  nullable=true)
     */
    private $invoiceWidth = 0;


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
     * @var string
     *
     * @ORM\Column(name="headerLeftWidth", type="string",  nullable=true)
     */
    private $headerLeftWidth = 0;


    /**
     * @var string
     *
     * @ORM\Column(name="headerRightWidth", type="string",  nullable=true)
     */
    private $headerRightWidth = 0;

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
     * @ORM\Column(name="printOutstanding", type="boolean",  nullable=true)
     */
    private $printOutstanding = false;


    /**
     * @var boolean
     *
     * @ORM\Column(name="isPrintFooter", type="boolean",  nullable=true)
     */
    private $isPrintFooter = true;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $isStockHistory = false;

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
     * @ORM\Column(name="productionType", type="string", length=30,nullable = true)
     */
    private $productionType;

    /**
     * @var string
     *
     * @ORM\Column(name="invoiceType", type="string", length=30,nullable = true)
     */
    private $invoiceType;


    /**
     * @var string
     *
     * @ORM\Column(name="borderWidth", type="smallint", length=2, nullable = true)
     */
    private $borderWidth = 0;


    /**
     * @var string
     *
     * @ORM\Column(name="borderColor", type="string", length=25,nullable = true)
     */
    private $borderColor;


    /**
     * @var string
     *
     * @ORM\Column(name="bodyFontSize", type="string", length=10,nullable = true)
     */
    private $bodyFontSize;

    /**
     * @var string
     *
     * @ORM\Column(name="sidebarFontSize", type="string", length=10,nullable = true)
     */
    private $sidebarFontSize;

    /**
     * @var string
     *
     * @ORM\Column(name="invoiceFontSize", type="string", length=10,nullable = true)
     */
    private $invoiceFontSize;


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
     * @ORM\Column(name="leftTopMargin", type="integer", nullable = true)
     */
    private $leftTopMargin = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isUnitPrice", type="boolean", nullable = true)
     */
    private $isUnitPrice = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="bodyTopMargin", type="integer", nullable = true)
     */
    private $bodyTopMargin = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="sidebarWidth", type="string", nullable = true)
     */
    private $sidebarWidth = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="bodyWidth", type="string", nullable = true)
     */
    private $bodyWidth = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="invoicePrintLogo", type="boolean",  nullable=true)
     */
    private $invoicePrintLogo = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="barcodePrint", type="boolean",  nullable=true)
     */
    private $barcodePrint = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="customInvoice", type="boolean",  nullable=true)
     */
    private $customInvoice = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="customInvoicePrint", type="boolean")
     */
    private $customInvoicePrint = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="showStock", type="boolean",  nullable=true)
     */
     private $showStock = false;

	/**
     * @var boolean
     *
     * @ORM\Column(name="isPowered", type="boolean",  nullable=true)
     */
     private $isPowered = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="removeImage", type="boolean")
     */
    private $removeImage = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;


}

