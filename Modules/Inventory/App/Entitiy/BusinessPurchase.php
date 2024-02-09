<?php

namespace Appstore\Bundle\BusinessBundle\Entity;

use Appstore\Bundle\AccountingBundle\Entity\AccountBank;
use Appstore\Bundle\AccountingBundle\Entity\AccountMobileBank;
use Appstore\Bundle\AccountingBundle\Entity\AccountPurchase;
use Appstore\Bundle\AccountingBundle\Entity\AccountVendor;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Setting\Bundle\ToolBundle\Entity\TransactionMethod;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BusinessPurchase
 *
 * @ORM\Table( name ="business_purchase")
 * @ORM\Entity(repositoryClass="Appstore\Bundle\BusinessBundle\Repository\BusinessPurchaseRepository")
 */
class BusinessPurchase
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessConfig", inversedBy="businessPurchases" , cascade={"detach","merge"} )
     **/
    private  $businessConfig;


    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessAndroidProcess", inversedBy="purchase" , cascade={"detach","merge"} )
     **/
    private  $androidProcess;


    /**
     * @ORM\OneToOne(targetEntity="Appstore\Bundle\AccountingBundle\Entity\AccountPurchase", mappedBy="businessPurchase" , cascade={"remove"})
     **/
    private  $accountPurchase;

    /**
     * @ORM\OneToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessReverse", mappedBy="businessPurchase" , cascade={"remove"})
     **/
    private  $businessReverse;


    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\AccountingBundle\Entity\AccountVendor", inversedBy="businessPurchases" , cascade={"detach","merge"} )
     **/
    private  $vendor;


    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User", inversedBy="businessPurchase" )
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User", inversedBy="businessPurchasesApprovedBy" )
     **/
    private  $approvedBy;


    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessPurchaseItem", mappedBy="businessPurchase" , cascade={"remove"})
     * @ORM\OrderBy({"id" = "DESC"})
     **/
    private  $businessPurchaseItems;

    /**
     * @ORM\ManyToOne(targetEntity="Setting\Bundle\ToolBundle\Entity\TransactionMethod", inversedBy="businessPurchase" )
     **/
    private  $transactionMethod;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\AccountingBundle\Entity\AccountBank", inversedBy="businessPurchases" )
     **/
    private  $accountBank;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\AccountingBundle\Entity\AccountMobileBank", inversedBy="businessPurchases" )
     **/
    private  $accountMobileBank;


    /**
     * @var string
     *
     * @ORM\Column(name="invoice", type="string", length=255, nullable=true)
     */
    private $invoice;


    /**
     * @var string
     *
     * @ORM\Column(name="memo", type="string", length=255, nullable=true)
     */
    private $memo;

    /**
     * @var string
     *
     * @ORM\Column(name="mode", type="string", length=30, nullable=true)
     */
    private $mode ='product';


    /**
     * @var \Datetime
     *
     * @ORM\Column(name="receiveDate", type="datetime", nullable=true)
     */
    private $receiveDate;



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
     * @var float
     *
     * @ORM\Column(name="subTotal", type="float", nullable=true)
     */
    private $subTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="netTotal", type="float", nullable=true)
     */
    private $netTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="payment", type="float", nullable=true)
     */
    private $payment;

    /**
     * @var float
     *
     * @ORM\Column(name="due", type="float", nullable=true)
     */
    private $due;

    /**
     * @var float
     *
     * @ORM\Column(name="discount", type="float", nullable=true)
     */
    private $discount;

    /**
     * @var string
     *
     * @ORM\Column(name="discountType", type="string", length=20, nullable=true)
     */
    private $discountType ='flat';

    /**
     * @var float
     *
     * @ORM\Column(name="discountCalculation", type="float" , nullable=true)
     */
    private $discountCalculation;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status=true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="asInvestment", type="boolean")
     */
    private $asInvestment = false;


	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="isReversed", type="boolean")
	 */
	private $isReversed = false;


	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="commissionInvoice", type="boolean")
	 */
	private $commissionInvoice = false;


	/**
     * @var string
     *
     * @ORM\Column(name="process", type="string", nullable=true)
     */
    private $process = "Created";

    /**
     * @var string
     *
     * @ORM\Column(name="grn", type="string", nullable=true)
     */
    private $grn;

    /**
     * @var string
     *
     * @ORM\Column(name="remark", type="text", nullable=true)
     */
    private $remark;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;


    /**
     * @Assert\File(maxSize="8388608")
     */
    protected $file;



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
     * Set invoice
     *
     * @param string $invoice
     *
     * @return BusinessPurchase
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get invoice
     *
     * @return string
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Set memo
     *
     * @param string $memo
     *
     * @return BusinessPurchase
     */
    public function setMemo($memo)
    {
        $this->memo = $memo;

        return $this;
    }

    /**
     * Get memo
     *
     * @return string
     */
    public function getMemo()
    {
        return $this->memo;
    }


    /**
     * Set receiveDate
     *
     * @param string $receiveDate
     *
     * @return BusinessPurchase
     */
    public function setReceiveDate($receiveDate)
    {
        $this->receiveDate = $receiveDate;

        return $this;
    }

    /**
     * Get receiveDate
     *
     * @return string
     */
    public function getReceiveDate()
    {
        return $this->receiveDate;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return BusinessPurchase
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
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
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param boolean $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }


    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param mixed $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return mixed
     */
    public function getApprovedBy()
    {
        return $this->approvedBy;
    }

    /**
     * @param mixed $approvedBy
     */
    public function setApprovedBy($approvedBy)
    {
        $this->approvedBy = $approvedBy;
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
     * @return string
     */
    public function getGrn()
    {
        return $this->grn;
    }

    /**
     * @param string $grn
     */
    public function setGrn($grn)
    {
        $this->grn = $grn;
    }


    /**
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param integer $code
     */
    public function setCode($code)
    {
        $this->code = $code;
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
     * @return TransactionMethod
     */
    public function getTransactionMethod()
    {
        return $this->transactionMethod;
    }

    /**
     * @param TransactionMethod $transactionMethod
     */
    public function setTransactionMethod($transactionMethod)
    {
        $this->transactionMethod = $transactionMethod;
    }

    /**
     * @return AccountBank
     */
    public function getAccountBank()
    {
        return $this->accountBank;
    }

    /**
     * @param AccountBank $accountBank
     */
    public function setAccountBank($accountBank)
    {
        $this->accountBank = $accountBank;
    }

    /**
     * @return AccountMobileBank
     */
    public function getAccountMobileBank()
    {
        return $this->accountMobileBank;
    }

    /**
     * @param AccountMobileBank $accountMobileBank
     */
    public function setAccountMobileBank($accountMobileBank)
    {
        $this->accountMobileBank = $accountMobileBank;
    }


    /**
     * @return bool
     */
    public function getAsInvestment()
    {
        return $this->asInvestment;
    }

    /**
     * @param bool $asInvestment
     */
    public function setAsInvestment($asInvestment)
    {
        $this->asInvestment = $asInvestment;
    }



    /**
     * @return AccountPurchase
     */
    public function getAccountPurchase()
    {
        return $this->accountPurchase;
    }



    /**
     * @return BusinessPurchaseItem
     */
    public function getPurchaseItems()
    {
        return $this->businessPurchaseItems;
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
     * @return float
     */
    public function getNetTotal()
    {
        return $this->netTotal;
    }

    /**
     * @param float $netTotal
     */
    public function setNetTotal($netTotal)
    {
        $this->netTotal = $netTotal;
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
     * @return float
     */
    public function getDue()
    {
        return $this->due;
    }

    /**
     * @param float $due
     */
    public function setDue($due)
    {
        $this->due = $due;
    }

    /**
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param string $remark
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
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
     * @return BusinessConfig
     */
    public function getBusinessConfig()
    {
        return $this->businessConfig;
    }

    /**
     * @param BusinessConfig $businessConfig
     */
    public function setBusinessConfig($businessConfig)
    {
        $this->businessConfig = $businessConfig;
    }


    /**
     * @return BusinessPurchaseItem
     */
    public function getBusinessPurchaseItems()
    {
        return $this->businessPurchaseItems;
    }

    /**
     * @return AccountVendor
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param AccountVendor $vendor
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
    }

    /**
     * @return string
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }

    /**
     * @param string $discountType
     */
    public function setDiscountType($discountType)
    {
        $this->discountType = $discountType;
    }

    /**
     * @return float
     */
    public function getDiscountCalculation()
    {
        return $this->discountCalculation;
    }

    /**
     * @param float $discountCalculation
     */
    public function setDiscountCalculation($discountCalculation)
    {
        $this->discountCalculation = $discountCalculation;
    }

	/**
	 * @return BusinessReverse
	 */
	public function getBusinessReverse() {
		return $this->businessReverse;
	}

	/**
	 * @return bool
	 */
	public function isReversed(){
		return $this->isReversed;
	}

	/**
	 * @param bool $isReversed
	 */
	public function setIsReversed( bool $isReversed ) {
		$this->isReversed = $isReversed;
	}

    /**
     * @return bool
     */
    public function isCommissionInvoice()
    {
        return $this->commissionInvoice;
    }

    /**
     * @param bool $commissionInvoice
     */
    public function setCommissionInvoice($commissionInvoice)
    {
        $this->commissionInvoice = $commissionInvoice;
    }

    /**
     * @return BusinessAndroidProcess
     */
    public function getAndroidProcess()
    {
        return $this->androidProcess;
    }

    /**
     * @param BusinessAndroidProcess $androidProcess
     */
    public function setAndroidProcess($androidProcess)
    {
        $this->androidProcess = $androidProcess;
    }

    /**
     * Sets file.
     *
     * @param BusinessPurchase $file
     */
    public function setFile($file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return BusinessPurchase
     */
    public function getFile()
    {
        return $this->file;
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
            : $this->getUploadDir().'/'.$this->path;
    }

    public function removeFileImage()
    {
        $path = null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;

        if ($file = $path) {
            unlink($file);
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../../web/'.$this->getUploadDir();
    }

    public function getUploadDir()
    {
        return 'uploads/domain/'.$this->getBusinessConfig()->getGlobalOption()->getId().'/business/';
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
        $filename = date('YmdHmi') . "-" . $this->getFile()->getClientOriginalName();
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $filename
        );
        // set the path property to the filename where you've saved the file
        $this->path = $filename;

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }


}

