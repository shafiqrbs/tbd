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
    private $skuWarehouse;

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
     * @ORM\Column(type="boolean",options={"default"="0"})
     */
    private $isPos;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\Setting")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     **/
    private $posInvoiceMode = null;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isCategoryItemQuantity;

    /**
     * @var bool
     * @ORM\Column(type="boolean",options={"default"="0"})
     */
    private $isPayFirst;

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
    private $vatIntegration;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $hsCodeEnable;

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
    private $sdEnable;

    /**
     * @var float
     *
     * @ORM\Column(type="float",  nullable=true)
     */
    private $sdPercent;

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
     * @ORM\Column(type="boolean",options={"default"="false"}, nullable=true)
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
     * @ORM\Column(type="boolean",options={"default"="false"}, nullable=true)
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
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isBrand;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isColor;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isSize;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isGrade;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isSku;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isModel;


     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isMultiPrice;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isMeasurement;


     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isProductGallery;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isBatchInvoice;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isProvision;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $posInvoicePosition;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $multiKitchen;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $paymentSplit;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $itemAddons;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $cashOnDelivery;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isOnline;

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

    /**
     * @var boolean
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $isSalesAutoApproved;

    /**
     * @var boolean
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $isPurchaseAutoApproved;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param mixed $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return mixed
     */
    public function getBusinessModel()
    {
        return $this->businessModel;
    }

    /**
     * @param mixed $businessModel
     */
    public function setBusinessModel($businessModel)
    {
        $this->businessModel = $businessModel;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getPrinter()
    {
        return $this->printer;
    }

    /**
     * @param string $printer
     */
    public function setPrinter($printer)
    {
        $this->printer = $printer;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getPrintFooterText()
    {
        return $this->printFooterText;
    }

    /**
     * @param string $printFooterText
     */
    public function setPrintFooterText($printFooterText)
    {
        $this->printFooterText = $printFooterText;
    }

    /**
     * @return string
     */
    public function getInvoiceComment()
    {
        return $this->invoiceComment;
    }

    /**
     * @param string $invoiceComment
     */
    public function setInvoiceComment($invoiceComment)
    {
        $this->invoiceComment = $invoiceComment;
    }

    /**
     * @return smallint
     */
    public function getVatPercent()
    {
        return $this->vatPercent;
    }

    /**
     * @param smallint $vatPercent
     */
    public function setVatPercent($vatPercent)
    {
        $this->vatPercent = $vatPercent;
    }

    /**
     * @return smallint
     */
    public function getAitPercent()
    {
        return $this->aitPercent;
    }

    /**
     * @param smallint $aitPercent
     */
    public function setAitPercent($aitPercent)
    {
        $this->aitPercent = $aitPercent;
    }

    /**
     * @return smallint
     */
    public function getFontSizeLabel()
    {
        return $this->fontSizeLabel;
    }

    /**
     * @param smallint $fontSizeLabel
     */
    public function setFontSizeLabel($fontSizeLabel)
    {
        $this->fontSizeLabel = $fontSizeLabel;
    }

    /**
     * @return smallint
     */
    public function getFontSizeValue()
    {
        return $this->fontSizeValue;
    }

    /**
     * @param smallint $fontSizeValue
     */
    public function setFontSizeValue($fontSizeValue)
    {
        $this->fontSizeValue = $fontSizeValue;
    }

    /**
     * @return string
     */
    public function getVatRegNo()
    {
        return $this->vatRegNo;
    }

    /**
     * @param string $vatRegNo
     */
    public function setVatRegNo($vatRegNo)
    {
        $this->vatRegNo = $vatRegNo;
    }

    /**
     * @return float
     */
    public function getUnitCommission()
    {
        return $this->unitCommission;
    }

    /**
     * @param float $unitCommission
     */
    public function setUnitCommission($unitCommission)
    {
        $this->unitCommission = $unitCommission;
    }

    /**
     * @return bool
     */
    public function isMultiCompany()
    {
        return $this->multiCompany;
    }

    /**
     * @param bool $multiCompany
     */
    public function setMultiCompany($multiCompany)
    {
        $this->multiCompany = $multiCompany;
    }

    /**
     * @return bool
     */
    public function isSkuCategory()
    {
        return $this->skuCategory;
    }

    /**
     * @param bool $skuCategory
     */
    public function setSkuCategory($skuCategory)
    {
        $this->skuCategory = $skuCategory;
    }

    /**
     * @return bool
     */
    public function isSkuBrand()
    {
        return $this->skuBrand;
    }

    /**
     * @param bool $skuBrand
     */
    public function setSkuBrand($skuBrand)
    {
        $this->skuBrand = $skuBrand;
    }

    /**
     * @return bool
     */
    public function isSkuModel()
    {
        return $this->skuModel;
    }

    /**
     * @param bool $skuModel
     */
    public function setSkuModel($skuModel)
    {
        $this->skuModel = $skuModel;
    }

    /**
     * @return bool
     */
    public function isSkuColor()
    {
        return $this->skuColor;
    }

    /**
     * @param bool $skuColor
     */
    public function setSkuColor($skuColor)
    {
        $this->skuColor = $skuColor;
    }

    /**
     * @return bool
     */
    public function isSkuSize()
    {
        return $this->skuSize;
    }

    /**
     * @param bool $skuSize
     */
    public function setSkuSize($skuSize)
    {
        $this->skuSize = $skuSize;
    }


    /**
     * @return bool
     */
    public function isBarcodePrint()
    {
        return $this->barcodePrint;
    }

    /**
     * @param bool $barcodePrint
     */
    public function setBarcodePrint($barcodePrint)
    {
        $this->barcodePrint = $barcodePrint;
    }

    /**
     * @return bool
     */
    public function isBarcodePriceHide()
    {
        return $this->barcodePriceHide;
    }

    /**
     * @param bool $barcodePriceHide
     */
    public function setBarcodePriceHide($barcodePriceHide)
    {
        $this->barcodePriceHide = $barcodePriceHide;
    }

    /**
     * @return bool
     */
    public function isBarcodeColor()
    {
        return $this->barcodeColor;
    }

    /**
     * @param bool $barcodeColor
     */
    public function setBarcodeColor($barcodeColor)
    {
        $this->barcodeColor = $barcodeColor;
    }

    /**
     * @return bool
     */
    public function isBarcodeSize()
    {
        return $this->barcodeSize;
    }

    /**
     * @param bool $barcodeSize
     */
    public function setBarcodeSize($barcodeSize)
    {
        $this->barcodeSize = $barcodeSize;
    }

    /**
     * @return bool
     */
    public function isBarcodeBrand()
    {
        return $this->barcodeBrand;
    }

    /**
     * @param bool $barcodeBrand
     */
    public function setBarcodeBrand($barcodeBrand)
    {
        $this->barcodeBrand = $barcodeBrand;
    }

    /**
     * @return bool
     */
    public function isVatMode()
    {
        return $this->vatMode;
    }

    /**
     * @param bool $vatMode
     */
    public function setVatMode($vatMode)
    {
        $this->vatMode = $vatMode;
    }

    /**
     * @return bool
     */
    public function isActiveSms()
    {
        return $this->isActiveSms;
    }

    /**
     * @param bool $isActiveSms
     */
    public function setIsActiveSms($isActiveSms)
    {
        $this->isActiveSms = $isActiveSms;
    }

    /**
     * @return bool
     */
    public function isZeroReceiveAllow()
    {
        return $this->isZeroReceiveAllow;
    }

    /**
     * @param bool $isZeroReceiveAllow
     */
    public function setIsZeroReceiveAllow($isZeroReceiveAllow)
    {
        $this->isZeroReceiveAllow = $isZeroReceiveAllow;
    }

    /**
     * @return bool
     */
    public function isPurchaseByPurchasePrice()
    {
        return $this->isPurchaseByPurchasePrice;
    }

    /**
     * @param bool $isPurchaseByPurchasePrice
     */
    public function setIsPurchaseByPurchasePrice($isPurchaseByPurchasePrice)
    {
        $this->isPurchaseByPurchasePrice = $isPurchaseByPurchasePrice;
    }

    /**
     * @return string
     */
    public function getShopName()
    {
        return $this->shopName;
    }

    /**
     * @param string $shopName
     */
    public function setShopName($shopName)
    {
        $this->shopName = $shopName;
    }

    /**
     * @return bool
     */
    public function isVatIntegration()
    {
        return $this->vatIntegration;
    }

    /**
     * @param bool $vatIntegration
     */
    public function setVatIntegration($vatIntegration)
    {
        $this->vatIntegration = $vatIntegration;
    }

    /**
     * @return bool
     */
    public function isVatEnable()
    {
        return $this->vatEnable;
    }

    /**
     * @param bool $vatEnable
     */
    public function setVatEnable($vatEnable)
    {
        $this->vatEnable = $vatEnable;
    }

    /**
     * @return bool
     */
    public function isStockItem()
    {
        return $this->stockItem;
    }

    /**
     * @param bool $stockItem
     */
    public function setStockItem($stockItem)
    {
        $this->stockItem = $stockItem;
    }

    /**
     * @return bool
     */
    public function isDescription()
    {
        return $this->isDescription;
    }

    /**
     * @param bool $isDescription
     */
    public function setIsDescription($isDescription)
    {
        $this->isDescription = $isDescription;
    }

    /**
     * @return bool
     */
    public function isDueSalesWithoutCustomer()
    {
        return $this->dueSalesWithoutCustomer;
    }

    /**
     * @param bool $dueSalesWithoutCustomer
     */
    public function setDueSalesWithoutCustomer($dueSalesWithoutCustomer)
    {
        $this->dueSalesWithoutCustomer = $dueSalesWithoutCustomer;
    }

    /**
     * @return bool
     */
    public function isAitEnable()
    {
        return $this->aitEnable;
    }

    /**
     * @param bool $aitEnable
     */
    public function setAitEnable($aitEnable)
    {
        $this->aitEnable = $aitEnable;
    }

    /**
     * @return bool
     */
    public function isZakatEnable()
    {
        return $this->zakatEnable;
    }

    /**
     * @param bool $zakatEnable
     */
    public function setZakatEnable($zakatEnable)
    {
        $this->zakatEnable = $zakatEnable;
    }

    /**
     * @return smallint
     */
    public function getZakatPercent()
    {
        return $this->zakatPercent;
    }

    /**
     * @param smallint $zakatPercent
     */
    public function setZakatPercent($zakatPercent)
    {
        $this->zakatPercent = $zakatPercent;
    }

    /**
     * @return bool
     */
    public function isBonusFromStock()
    {
        return $this->bonusFromStock;
    }

    /**
     * @param bool $bonusFromStock
     */
    public function setBonusFromStock($bonusFromStock)
    {
        $this->bonusFromStock = $bonusFromStock;
    }

    /**
     * @return bool
     */
    public function isConditionSales()
    {
        return $this->conditionSales;
    }

    /**
     * @param bool $conditionSales
     */
    public function setConditionSales($conditionSales)
    {
        $this->conditionSales = $conditionSales;
    }

    /**
     * @return bool
     */
    public function isMarketingExecutive()
    {
        return $this->isMarketingExecutive;
    }

    /**
     * @param bool $isMarketingExecutive
     */
    public function setIsMarketingExecutive($isMarketingExecutive)
    {
        $this->isMarketingExecutive = $isMarketingExecutive;
    }

    /**
     * @return bool
     */
    public function isPosPrint()
    {
        return $this->posPrint;
    }

    /**
     * @param bool $posPrint
     */
    public function setPosPrint($posPrint)
    {
        $this->posPrint = $posPrint;
    }

    /**
     * @return bool
     */
    public function isFuelStation()
    {
        return $this->fuelStation;
    }

    /**
     * @param bool $fuelStation
     */
    public function setFuelStation($fuelStation)
    {
        $this->fuelStation = $fuelStation;
    }

    /**
     * @return bool
     */
    public function isZeroStock()
    {
        return $this->zeroStock;
    }

    /**
     * @param bool $zeroStock
     */
    public function setZeroStock($zeroStock)
    {
        $this->zeroStock = $zeroStock;
    }

    /**
     * @return bool
     */
    public function isSystemReset()
    {
        return $this->systemReset;
    }

    /**
     * @param bool $systemReset
     */
    public function setSystemReset($systemReset)
    {
        $this->systemReset = $systemReset;
    }

    /**
     * @return bool
     */
    public function isTloCommission()
    {
        return $this->tloCommission;
    }

    /**
     * @param bool $tloCommission
     */
    public function setTloCommission($tloCommission)
    {
        $this->tloCommission = $tloCommission;
    }

    /**
     * @return bool
     */
    public function isSrCommission()
    {
        return $this->srCommission;
    }

    /**
     * @param bool $srCommission
     */
    public function setSrCommission($srCommission)
    {
        $this->srCommission = $srCommission;
    }

    /**
     * @return bool
     */
    public function isSalesReturn()
    {
        return $this->salesReturn;
    }

    /**
     * @param bool $salesReturn
     */
    public function setSalesReturn($salesReturn)
    {
        $this->salesReturn = $salesReturn;
    }

    /**
     * @return bool
     */
    public function isStoreLedger()
    {
        return $this->storeLedger;
    }

    /**
     * @param bool $storeLedger
     */
    public function setStoreLedger($storeLedger)
    {
        $this->storeLedger = $storeLedger;
    }

    /**
     * @return smallint
     */
    public function getInvoiceWidth()
    {
        return $this->invoiceWidth;
    }

    /**
     * @param smallint $invoiceWidth
     */
    public function setInvoiceWidth($invoiceWidth)
    {
        $this->invoiceWidth = $invoiceWidth;
    }

    /**
     * @return smallint
     */
    public function getPrintTopMargin()
    {
        return $this->printTopMargin;
    }

    /**
     * @param smallint $printTopMargin
     */
    public function setPrintTopMargin($printTopMargin)
    {
        $this->printTopMargin = $printTopMargin;
    }

    /**
     * @return smallint
     */
    public function getPrintMarginBottom()
    {
        return $this->printMarginBottom;
    }

    /**
     * @param smallint $printMarginBottom
     */
    public function setPrintMarginBottom($printMarginBottom)
    {
        $this->printMarginBottom = $printMarginBottom;
    }

    /**
     * @return string
     */
    public function getHeaderLeftWidth()
    {
        return $this->headerLeftWidth;
    }

    /**
     * @param string $headerLeftWidth
     */
    public function setHeaderLeftWidth($headerLeftWidth)
    {
        $this->headerLeftWidth = $headerLeftWidth;
    }

    /**
     * @return string
     */
    public function getHeaderRightWidth()
    {
        return $this->headerRightWidth;
    }

    /**
     * @param string $headerRightWidth
     */
    public function setHeaderRightWidth($headerRightWidth)
    {
        $this->headerRightWidth = $headerRightWidth;
    }

    /**
     * @return smallint
     */
    public function getPrintMarginReportTop()
    {
        return $this->printMarginReportTop;
    }

    /**
     * @param smallint $printMarginReportTop
     */
    public function setPrintMarginReportTop($printMarginReportTop)
    {
        $this->printMarginReportTop = $printMarginReportTop;
    }

    /**
     * @return bool
     */
    public function isPrintHeader()
    {
        return $this->isPrintHeader;
    }

    /**
     * @param bool $isPrintHeader
     */
    public function setIsPrintHeader($isPrintHeader)
    {
        $this->isPrintHeader = $isPrintHeader;
    }

    /**
     * @return bool
     */
    public function isInvoiceTitle()
    {
        return $this->isInvoiceTitle;
    }

    /**
     * @param bool $isInvoiceTitle
     */
    public function setIsInvoiceTitle($isInvoiceTitle)
    {
        $this->isInvoiceTitle = $isInvoiceTitle;
    }

    /**
     * @return bool
     */
    public function isPrintOutstanding()
    {
        return $this->printOutstanding;
    }

    /**
     * @param bool $printOutstanding
     */
    public function setPrintOutstanding($printOutstanding)
    {
        $this->printOutstanding = $printOutstanding;
    }

    /**
     * @return bool
     */
    public function isPrintFooter()
    {
        return $this->isPrintFooter;
    }

    /**
     * @param bool $isPrintFooter
     */
    public function setIsPrintFooter($isPrintFooter)
    {
        $this->isPrintFooter = $isPrintFooter;
    }

    /**
     * @return bool
     */
    public function isStockHistory()
    {
        return $this->isStockHistory;
    }

    /**
     * @param bool $isStockHistory
     */
    public function setIsStockHistory($isStockHistory)
    {
        $this->isStockHistory = $isStockHistory;
    }

    /**
     * @return string
     */
    public function getInvoicePrefix()
    {
        return $this->invoicePrefix;
    }

    /**
     * @param string $invoicePrefix
     */
    public function setInvoicePrefix($invoicePrefix)
    {
        $this->invoicePrefix = $invoicePrefix;
    }

    /**
     * @return string
     */
    public function getInvoiceProcess()
    {
        return $this->invoiceProcess;
    }

    /**
     * @param string $invoiceProcess
     */
    public function setInvoiceProcess($invoiceProcess)
    {
        $this->invoiceProcess = $invoiceProcess;
    }

    /**
     * @return string
     */
    public function getCustomerPrefix()
    {
        return $this->customerPrefix;
    }

    /**
     * @param string $customerPrefix
     */
    public function setCustomerPrefix($customerPrefix)
    {
        $this->customerPrefix = $customerPrefix;
    }

    /**
     * @return string
     */
    public function getProductionType()
    {
        return $this->productionType;
    }

    /**
     * @param string $productionType
     */
    public function setProductionType($productionType)
    {
        $this->productionType = $productionType;
    }

    /**
     * @return string
     */
    public function getInvoiceType()
    {
        return $this->invoiceType;
    }

    /**
     * @param string $invoiceType
     */
    public function setInvoiceType($invoiceType)
    {
        $this->invoiceType = $invoiceType;
    }

    /**
     * @return string
     */
    public function getBorderWidth()
    {
        return $this->borderWidth;
    }

    /**
     * @param string $borderWidth
     */
    public function setBorderWidth($borderWidth)
    {
        $this->borderWidth = $borderWidth;
    }

    /**
     * @return string
     */
    public function getBorderColor()
    {
        return $this->borderColor;
    }

    /**
     * @param string $borderColor
     */
    public function setBorderColor($borderColor)
    {
        $this->borderColor = $borderColor;
    }

    /**
     * @return string
     */
    public function getBodyFontSize()
    {
        return $this->bodyFontSize;
    }

    /**
     * @param string $bodyFontSize
     */
    public function setBodyFontSize($bodyFontSize)
    {
        $this->bodyFontSize = $bodyFontSize;
    }

    /**
     * @return string
     */
    public function getSidebarFontSize()
    {
        return $this->sidebarFontSize;
    }

    /**
     * @param string $sidebarFontSize
     */
    public function setSidebarFontSize($sidebarFontSize)
    {
        $this->sidebarFontSize = $sidebarFontSize;
    }

    /**
     * @return string
     */
    public function getInvoiceFontSize()
    {
        return $this->invoiceFontSize;
    }

    /**
     * @param string $invoiceFontSize
     */
    public function setInvoiceFontSize($invoiceFontSize)
    {
        $this->invoiceFontSize = $invoiceFontSize;
    }

    /**
     * @return smallint
     */
    public function getPrintLeftMargin()
    {
        return $this->printLeftMargin;
    }

    /**
     * @param smallint $printLeftMargin
     */
    public function setPrintLeftMargin($printLeftMargin)
    {
        $this->printLeftMargin = $printLeftMargin;
    }

    /**
     * @return int
     */
    public function getInvoiceHeight()
    {
        return $this->invoiceHeight;
    }

    /**
     * @param int $invoiceHeight
     */
    public function setInvoiceHeight($invoiceHeight)
    {
        $this->invoiceHeight = $invoiceHeight;
    }

    /**
     * @return int
     */
    public function getLeftTopMargin()
    {
        return $this->leftTopMargin;
    }

    /**
     * @param int $leftTopMargin
     */
    public function setLeftTopMargin($leftTopMargin)
    {
        $this->leftTopMargin = $leftTopMargin;
    }

    /**
     * @return bool
     */
    public function isUnitPrice()
    {
        return $this->isUnitPrice;
    }

    /**
     * @param bool $isUnitPrice
     */
    public function setIsUnitPrice($isUnitPrice)
    {
        $this->isUnitPrice = $isUnitPrice;
    }

    /**
     * @return int
     */
    public function getBodyTopMargin()
    {
        return $this->bodyTopMargin;
    }

    /**
     * @param int $bodyTopMargin
     */
    public function setBodyTopMargin($bodyTopMargin)
    {
        $this->bodyTopMargin = $bodyTopMargin;
    }

    /**
     * @return string
     */
    public function getSidebarWidth()
    {
        return $this->sidebarWidth;
    }

    /**
     * @param string $sidebarWidth
     */
    public function setSidebarWidth($sidebarWidth)
    {
        $this->sidebarWidth = $sidebarWidth;
    }

    /**
     * @return string
     */
    public function getBodyWidth()
    {
        return $this->bodyWidth;
    }

    /**
     * @param string $bodyWidth
     */
    public function setBodyWidth($bodyWidth)
    {
        $this->bodyWidth = $bodyWidth;
    }

    /**
     * @return bool
     */
    public function isInvoicePrintLogo()
    {
        return $this->invoicePrintLogo;
    }

    /**
     * @param bool $invoicePrintLogo
     */
    public function setInvoicePrintLogo($invoicePrintLogo)
    {
        $this->invoicePrintLogo = $invoicePrintLogo;
    }

    /**
     * @return bool
     */
    public function isCustomInvoice()
    {
        return $this->customInvoice;
    }

    /**
     * @param bool $customInvoice
     */
    public function setCustomInvoice($customInvoice)
    {
        $this->customInvoice = $customInvoice;
    }

    /**
     * @return bool
     */
    public function isCustomInvoicePrint()
    {
        return $this->customInvoicePrint;
    }

    /**
     * @param bool $customInvoicePrint
     */
    public function setCustomInvoicePrint($customInvoicePrint)
    {
        $this->customInvoicePrint = $customInvoicePrint;
    }

    /**
     * @return bool
     */
    public function isShowStock()
    {
        return $this->showStock;
    }

    /**
     * @param bool $showStock
     */
    public function setShowStock($showStock)
    {
        $this->showStock = $showStock;
    }

    /**
     * @return bool
     */
    public function isPowered()
    {
        return $this->isPowered;
    }

    /**
     * @param bool $isPowered
     */
    public function setIsPowered($isPowered)
    {
        $this->isPowered = $isPowered;
    }

    /**
     * @return bool
     */
    public function isRemoveImage()
    {
        return $this->removeImage;
    }

    /**
     * @param bool $removeImage
     */
    public function setRemoveImage($removeImage)
    {
        $this->removeImage = $removeImage;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return bool
     */
    public function isBrand()
    {
        return $this->isBrand;
    }

    /**
     * @param bool $isBrand
     */
    public function setIsBrand($isBrand)
    {
        $this->isBrand = $isBrand;
    }

    /**
     * @return bool
     */
    public function isColor()
    {
        return $this->isColor;
    }

    /**
     * @param bool $isColor
     */
    public function setIsColor($isColor)
    {
        $this->isColor = $isColor;
    }

    /**
     * @return bool
     */
    public function isSize()
    {
        return $this->isSize;
    }

    /**
     * @param bool $isSize
     */
    public function setIsSize($isSize)
    {
        $this->isSize = $isSize;
    }

    /**
     * @return bool
     */
    public function isGrade()
    {
        return $this->isGrade;
    }

    /**
     * @param bool $isGrade
     */
    public function setIsGrade($isGrade)
    {
        $this->isGrade = $isGrade;
    }

    /**
     * @return bool
     */
    public function isModel()
    {
        return $this->isModel;
    }

    /**
     * @param bool $isModel
     */
    public function setIsModel($isModel)
    {
        $this->isModel = $isModel;
    }

    /**
     * @return bool
     */
    public function isMultiPrice()
    {
        return $this->isMultiPrice;
    }

    /**
     * @param bool $isMultiPrice
     */
    public function setIsMultiPrice($isMultiPrice)
    {
        $this->isMultiPrice = $isMultiPrice;
    }

    /**
     * @return bool
     */
    public function isMeasurement()
    {
        return $this->isMeasurement;
    }

    /**
     * @param bool $isMeasurement
     */
    public function setIsMeasurement($isMeasurement)
    {
        $this->isMeasurement = $isMeasurement;
    }

    /**
     * @return bool
     */
    public function isProductGallery()
    {
        return $this->isProductGallery;
    }

    /**
     * @param bool $isProductGallery
     */
    public function setIsProductGallery($isProductGallery)
    {
        $this->isProductGallery = $isProductGallery;
    }

    /**
     * @return bool
     */
    public function isBatchInvoice()
    {
        return $this->isBatchInvoice;
    }

    /**
     * @param bool $isBatchInvoice
     */
    public function setIsBatchInvoice($isBatchInvoice)
    {
        $this->isBatchInvoice = $isBatchInvoice;
    }

    /**
     * @return bool
     */
    public function isProvision()
    {
        return $this->isProvision;
    }

    /**
     * @param bool $isProvision
     */
    public function setIsProvision($isProvision)
    {
        $this->isProvision = $isProvision;
    }

    /**
     * @return bool
     */
    public function isSku()
    {
        return $this->isSku;
    }

    /**
     * @param bool $isSku
     */
    public function setIsSku($isSku)
    {
        $this->isSku = $isSku;
    }

    /**
     * @return bool
     */
    public function isPos()
    {
        return $this->isPos;
    }

    /**
     * @param bool $isPos
     */
    public function setIsPos($isPos)
    {
        $this->isPos = $isPos;
    }


    /**
     * @return bool
     */
    public function isPayFirst()
    {
        return $this->isPayFirst;
    }

    /**
     * @param bool $isPayFirst
     */
    public function setIsPayFirst($isPayFirst)
    {
        $this->isPayFirst = $isPayFirst;
    }

    /**
     * @return bool
     */
    public function isSalesAutoApproved()
    {
        return $this->isSalesAutoApproved;
    }

    /**
     * @param bool $isSalesAutoApproved
     */
    public function setIsSalesAutoApproved($isSalesAutoApproved)
    {
        $this->isSalesAutoApproved = $isSalesAutoApproved;
    }

    /**
     * @return bool
     */
    public function isPurchaseAutoApproved()
    {
        return $this->isPurchaseAutoApproved;
    }

    /**
     * @param bool $isPurchaseAutoApproved
     */
    public function setIsPurchaseAutoApproved($isPurchaseAutoApproved)
    {
        $this->isPurchaseAutoApproved = $isPurchaseAutoApproved;
    }

    /**
     * @return bool
     */
    public function isSkuWarehouse()
    {
        return $this->skuWarehouse;
    }

    /**
     * @param bool $skuWarehouse
     */
    public function setSkuWarehouse($skuWarehouse)
    {
        $this->skuWarehouse = $skuWarehouse;
    }

    /**
     * @return bool
     */
    public function isPosInvoicePosition()
    {
        return $this->posInvoicePosition;
    }

    /**
     * @param bool $posInvoicePosition
     */
    public function setPosInvoicePosition($posInvoicePosition)
    {
        $this->posInvoicePosition = $posInvoicePosition;
    }

    /**
     * @return bool
     */
    public function isMultiKitchen()
    {
        return $this->multiKitchen;
    }

    /**
     * @param bool $multiKitchen
     */
    public function setMultiKitchen($multiKitchen)
    {
        $this->multiKitchen = $multiKitchen;
    }

    /**
     * @return bool
     */
    public function isPaymentSplit()
    {
        return $this->paymentSplit;
    }

    /**
     * @param bool $paymentSplit
     */
    public function setPaymentSplit($paymentSplit)
    {
        $this->paymentSplit = $paymentSplit;
    }

    /**
     * @return bool
     */
    public function isItemAddons()
    {
        return $this->itemAddons;
    }

    /**
     * @param bool $itemAddons
     */
    public function setItemAddons($itemAddons)
    {
        $this->itemAddons = $itemAddons;
    }

    /**
     * @return bool
     */
    public function isCashOnDelivery()
    {
        return $this->cashOnDelivery;
    }

    /**
     * @param bool $cashOnDelivery
     */
    public function setCashOnDelivery($cashOnDelivery)
    {
        $this->cashOnDelivery = $cashOnDelivery;
    }

    /**
     * @return bool
     */
    public function isOnline()
    {
        return $this->isOnline;
    }

    /**
     * @param bool $isOnline
     */
    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;
    }

    /**
     * @return bool
     */
    public function isHsCodeEnable()
    {
        return $this->hsCodeEnable;
    }

    /**
     * @param bool $hsCodeEnable
     */
    public function setHsCodeEnable($hsCodeEnable)
    {
        $this->hsCodeEnable = $hsCodeEnable;
    }

    /**
     * @return Setting|null
     */
    public function getPosInvoiceMode()
    {
        return $this->posInvoiceMode;
    }

    /**
     * @param Setting|null $posInvoiceMode
     */
    public function setPosInvoiceMode($posInvoiceMode)
    {
        $this->posInvoiceMode = $posInvoiceMode;
    }

    /**
     * @return bool
     */
    public function isSdEnable()
    {
        return $this->sdEnable;
    }

    /**
     * @param bool $sdEnable
     */
    public function setSdEnable($sdEnable)
    {
        $this->sdEnable = $sdEnable;
    }

    /**
     * @return float
     */
    public function getSdPercent()
    {
        return $this->sdPercent;
    }

    /**
     * @param float $sdPercent
     */
    public function setSdPercent($sdPercent)
    {
        $this->sdPercent = $sdPercent;
    }










}

