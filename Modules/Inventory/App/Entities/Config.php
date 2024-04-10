<?php

namespace Modules\Inventory\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BusinessConfig
 *
 * @ORM\Table( name ="inv_config")
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
     * @ORM\OneToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $domain;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\Setting")
     **/
    private $businessModel;


     /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\Currency")
     **/
    private $currency;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50,nullable = true)
     */
    private $printer;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable = true)
     */
    private $address;

     /**
     * @var string
     *
     * @ORM\Column(type="text", nullable = true)
     */
    private $printFooterText;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable = true)
     */
    private $invoiceComment;


    /**
     * @var smallint
     *
     * @ORM\Column(type="smallint",  nullable=true)
     */
    private $vatPercent;

    /**
     * @var smallint
     *
     * @ORM\Column(type="smallint",  nullable=true)
     */
    private $aitPercent;

    /**
     * @var smallint
     *
     * @ORM\Column(type="smallint",  nullable=true)
     */
    private $fontSizeLabel;

    /**
     * @var smallint
     *
     * @ORM\Column(type="smallint",  nullable=true)
     */
    private $fontSizeValue;

    /**
     * @var string
     *
     * @ORM\Column(type="string",  nullable=true)
     */
    private $vatRegNo;


     /**
     * @var float
     *
     * @ORM\Column(type="float",  nullable=true)
     */
     private $unitCommission = 0;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $multiCompany = false;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $skuCategory = false;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $skuBrand = false;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $skuModel = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $skuColor = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $skuSize = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $skuWearhouse = false;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $barcodePrint = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $barcodePriceHide = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $barcodeColor = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $barcodeSize = false;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $barcodeBrand = false;

      /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $vatMode = false;

     /**
     * @var string
     *
     * @ORM\Column(type="string",  nullable=true)
     */
    private $shopName;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $vatEnable = false;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $bonusFromStock = false;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $conditionSales = false;

      /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable=true)
     */
    private $isMarketingExecutive = false;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $posPrint = false;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $fuelStation = false;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $zeroStock = true;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $systemReset = false;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $tloCommission = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $srCommission = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $salesReturn = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $storeLedger = false;

    /**
     * @var smallint
     *
     * @ORM\Column(type="smallint",  nullable=true)
     */
    private $invoiceWidth = 0;


    /**
     * @var smallint
     *
     * @ORM\Column(type="smallint",  nullable=true)
     */
    private $printTopMargin = 0;


    /**
     * @var smallint
     *
     * @ORM\Column(type="smallint",  nullable=true)
     */
    private $printMarginBottom = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="string",  nullable=true)
     */
    private $headerLeftWidth = 0;


    /**
     * @var string
     *
     * @ORM\Column(type="string",  nullable=true)
     */
    private $headerRightWidth = 0;

    /**
     * @var smallint
     *
     * @ORM\Column(type="smallint",  nullable=true)
     */
    private $printMarginReportTop = 0;



    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $isPrintHeader = true;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $isInvoiceTitle = true;


     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $printOutstanding = false;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
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
     * @ORM\Column(type="string", length=10,nullable = true)
     */
    private $invoicePrefix;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $invoiceProcess;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10,nullable = true)
     */
    private $customerPrefix;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30,nullable = true)
     */
    private $productionType;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30,nullable = true)
     */
    private $invoiceType;


    /**
     * @var string
     *
     * @ORM\Column( type="smallint", length=2, nullable = true)
     */
    private $borderWidth = 0;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=25,nullable = true)
     */
    private $borderColor;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10,nullable = true)
     */
    private $bodyFontSize;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10,nullable = true)
     */
    private $sidebarFontSize;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10,nullable = true)
     */
    private $invoiceFontSize;


    /**
     * @var smallint
     *
     * @ORM\Column(type="smallint", nullable = true)
     */
    private $printLeftMargin = 0;


    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable = true)
     */
    private $invoiceHeight = 0;

    /**
     * @var integer
     *
     * @ORM\Column( type="integer", nullable = true)
     */
    private $leftTopMargin = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable = true)
     */
    private $isUnitPrice = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable = true)
     */
    private $bodyTopMargin = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $sidebarWidth = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $bodyWidth = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $invoicePrintLogo = true;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",  nullable=true)
     */
    private $customInvoice = false;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",nullable=true)
     */
    private $customInvoicePrint = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
     private $showStock = false;

	/**
     * @var boolean
     *
     * @ORM\Column(type="boolean",  nullable=true)
     */
     private $isPowered = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" ,nullable=true)
     */
    private $removeImage = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="updated_at", type="datetime",nullable=true)
     */
    private $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

}

