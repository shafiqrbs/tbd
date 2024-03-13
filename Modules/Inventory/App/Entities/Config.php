<?php

namespace Modules\Inventory\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BusinessConfig
 *
 * @ORM\Table( name ="inv_config")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\BusinessConfigRepository")
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
     * @ORM\OneToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption", inversedBy="businessConfig" , cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $globalOption;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\Setting")
     **/
    private $businessModel;


     /**
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\Currency")
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

    /**
     * @return GlobalOption
     */
    public function getGlobalOption()
    {
        return $this->globalOption;
    }

    /**
     * @param GlobalOption $globalOption
     */
    public function setGlobalOption($globalOption)
    {
        $this->globalOption = $globalOption;
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
     * @return bool
     */
    public function isIsPrintFooter()
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
    public function isIsPrintHeader()
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
     * @return smallint
     */
    public function getVatPercentage()
    {
        return $this->vatPercentage;
    }

    /**
     * @param smallint $vatPercentage
     */
    public function setVatPercentage($vatPercentage)
    {
        $this->vatPercentage = $vatPercentage;
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
    public function getVatEnable()
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
     * @return boolean
     */
    public function isInvoicePrintLogo()
    {
        return $this->invoicePrintLogo;
    }

    /**
     * @param boolean $invoicePrintLogo
     */
    public function setInvoicePrintLogo($invoicePrintLogo)
    {
        $this->invoicePrintLogo = $invoicePrintLogo;
    }

    /**
     * @return bool
     */
    public function getPrintInstruction()
    {
        return $this->printInstruction;
    }

    /**
     * @param bool $printInstruction
     */
    public function setPrintInstruction($printInstruction)
    {
        $this->printInstruction = $printInstruction;
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
    public function getIsInvoiceTitle()
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
     * @return array
     */
    public function getInvoiceProcess()
    {
        return $this->invoiceProcess;
    }

    /**
     * @param array $invoiceProcess
     */
    public function setInvoiceProcess($invoiceProcess)
    {
        $this->invoiceProcess = $invoiceProcess;
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
     * @return smallint
     */
    public function getPrintMarginReportLeft()
    {
        return $this->printMarginReportLeft;
    }

    /**
     * @param smallint $printMarginReportLeft
     */
    public function setPrintMarginReportLeft($printMarginReportLeft)
    {
        $this->printMarginReportLeft = $printMarginReportLeft;
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
     * @return BusinessVendor
     */
    public function getBusinessVendors()
    {
        return $this->businessVendors;
    }

    /**
     * @return BusinessPurchase
     */
    public function getBusinessPurchases()
    {
        return $this->businessPurchases;
    }

    /**
     * @return Product
     */
    public function getBusinessParticulars()
    {
        return $this->businessParticulars;
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
     * @return BusinessDamage
     */
    public function getBusinessDamages()
    {
        return $this->businessDamages;
    }

    /**
     * @return BusinessPurchaseReturn
     */
    public function getBusinessPurchasesReturns()
    {
        return $this->businessPurchasesReturns;
    }

    /**
     * @return bool
     */
    public function isCustomInvoicePrint(): bool
    {
        return $this->customInvoicePrint;
    }

    /**
     * @param bool $customInvoicePrint
     */
    public function setCustomInvoicePrint(bool $customInvoicePrint)
    {
        $this->customInvoicePrint = $customInvoicePrint;
    }



    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/' . $this->path;
    }



    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'uploads/domain/'.$this->getGlobalOption()->getId().'/business';
    }

    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
            $this->path = null ;
        }
    }

    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $filename = date('YmdHmi') . "_" . $this->getFile()->getClientOriginalName();
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $filename
        );

        // set the path property to the filename where you've saved the file
        $this->path = $filename ;

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    /**
     * @return boolean
     */
    public function getRemoveImage()
    {
        return $this->removeImage;
    }

    /**
     * @param boolean $removeImage
     */
    public function setRemoveImage($removeImage)
    {
        $this->removeImage = $removeImage;
    }



    /**
     * Set address
     *
     * @param mixed $address
     * @return Config
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
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
    public function setProductionType(string $productionType)
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
    public function setInvoiceType(string $invoiceType)
    {
        $this->invoiceType = $invoiceType;
    }

	/**
	 * @return WearHouse
	 */
	public function getWearHouses() {
		return $this->wearHouses;
	}

	/**
	 * @return BusinessInvoice
	 */
	public function getBusinessInvoices() {
		return $this->businessInvoices;
	}

	/**
	 * @return Category
	 */
	public function getCategories() {
		return $this->categories;
	}

	/**
	 * @return array
	 */
	public function getStockFormat(){
		return $this->stockFormat;
	}

	/**
	 * @param array $stockFormat
	 */
	public function setStockFormat( array $stockFormat ) {
		$this->stockFormat = $stockFormat;
	}

	/**
	 * @return bool
	 */
	public function isPowered(){
		return $this->isPowered;
	}

	/**
	 * @param bool $isPowered
	 */
	public function setIsPowered( bool $isPowered ) {
		$this->isPowered = $isPowered;
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
     * @return BusinessAndroidProcess
     */
    public function getAndroidProcess()
    {
        return $this->androidProcess;
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
     * @return bool
     */
    public function isSkuBarnd()
    {
        return $this->skuBarnd;
    }

    /**
     * @param bool $skuBarnd
     */
    public function setSkuBarnd($skuBarnd)
    {
        $this->skuBarnd = $skuBarnd;
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
    public function isSkuWearhouse()
    {
        return $this->skuWearhouse;
    }

    /**
     * @param bool $skuWearhouse
     */
    public function setSkuWearhouse($skuWearhouse)
    {
        $this->skuWearhouse = $skuWearhouse;
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





}

