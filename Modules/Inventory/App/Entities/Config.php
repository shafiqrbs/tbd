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
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Country")
     **/
    private $country;


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
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $multiCompany;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $skuCategory;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $skuBrand;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $skuModel;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $skuColor;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $skuSize;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $skuWearhouse;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $barcodePrint;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $barcodePriceHide;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $barcodeColor;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $barcodeSize;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $barcodeBrand;

      /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $vatMode;

    /**
     * @var bool
     * @ORM\Column(type="boolean",options={"default"="0"})
     */
    private $isActiveSms;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default"="0"})
     */
    private $isZeroReceiveAllow;


    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default"="0"})
     */
    private $isPurchaseByPurchasePrice;

     /**
     * @var string
     *
     * @ORM\Column(type="string",  nullable=true)
     */
    private $shopName;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $vatEnable;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $stockItem;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isDescription;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $dueSalesWithoutCustomer;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $aitEnable;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $zakatEnable;

    /**
     * @var smallint
     *
     * @ORM\Column(type="smallint",  nullable=true)
     */
    private $zakatPercent;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $bonusFromStock;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $conditionSales;

      /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",options={"default"="false"})
     */
    private $isMarketingExecutive;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $posPrint;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $fuelStation;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $zeroStock;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $systemReset;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $tloCommission;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $srCommission;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $salesReturn;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $storeLedger;

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
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $isPrintHeader;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $isInvoiceTitle;


     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $printOutstanding;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $isPrintFooter;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isStockHistory;

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
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isUnitPrice;

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
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $invoicePrintLogo;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",options={"default"="false"})
     */
    private $customInvoice;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean",options={"default"="false"})
     */
    private $customInvoicePrint;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
     private $showStock;

	/**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
     private $isPowered;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" ,options={"default"="false"})
     */
    private $removeImage;

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

