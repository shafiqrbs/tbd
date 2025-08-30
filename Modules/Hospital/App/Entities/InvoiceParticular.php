<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoiceParticular
 *
 * @ORM\Table(name = "hms_invoice_particular")
 * @ORM\Entity()
 */
class InvoiceParticular
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
     * @ORM\ManyToOne(targetEntity="Invoice")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $hmsInvoice;

    /**
     * @ORM\ManyToOne(targetEntity="InvoicePathologicalGroup")
     * @ORM\JoinColumn(nullable=true,onDelete="SET NULL")
     **/
    private $invoicePathologicalGroup;

     /**
     * @ORM\ManyToOne(targetEntity="AdmissionPatientParticular")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $admissionPatientParticular;


    /**
     * @ORM\ManyToOne(targetEntity="Particular", inversedBy="invoiceParticular")
     * @ORM\JoinColumn(name="particular_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $particular;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private $assignDoctor;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private $assignLabuser;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="particular_delivered_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $particularDeliveredBy;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="particular_prepared_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $particularPreparedBy;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="sample_collected_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $sampleCollectedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="smallint")
     */
    private $quantity = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="reportCode", type="string",  nullable=true)
     */
    private $reportCode;


    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $discountPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="commission", type="float", nullable=true)
     */
    private $commission;

    /**
     * @var string
     *
     * @ORM\Column( type="decimal", nullable=true)
     */
    private $estimatePrice;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",nullable=true)
     */
    private $customPrice = false;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $subTotal;

    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=30, nullable=true)
     */
    private $process ='In-progress';

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;


     /**
     * @var string
     *
     * @ORM\Column(name="reportContent", type="text", nullable=true)
     */
    private $reportContent;


    /**
     * @var \DateTime
     * @ORM\Column(name="collectionDate", type="datetime", nullable=true)
     */
    private $collectionDate;

     /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updated;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $path;


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
     * @return Particular
     */
    public function getParticular()
    {
        return $this->particular;
    }

    /**
     * @param Particular $particular
     */
    public function setParticular($particular)
    {
        $this->particular = $particular;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return float
     */
    public function getSalesPrice()
    {
        return $this->salesPrice;
    }

    /**
     * @param float $salesPrice
     */
    public function setSalesPrice($salesPrice)
    {
        $this->salesPrice = $salesPrice;
    }

    /**
     * @return string
     */
    public function getEstimatePrice()
    {
        return $this->estimatePrice;
    }

    /**
     * @param string $estimatePrice
     */
    public function setEstimatePrice($estimatePrice)
    {
        $this->estimatePrice = $estimatePrice;
    }

    /**
     * @return bool
     */
    public function isCustomPrice()
    {
        return $this->customPrice;
    }

    /**
     * @param bool $customPrice
     */
    public function setCustomPrice($customPrice)
    {
        $this->customPrice = $customPrice;
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
     * @return User
     */
    public function getParticularPreparedBy()
    {
        return $this->particularPreparedBy;
    }

    /**
     * @param User $particularPreparedBy
     */
    public function setParticularPreparedBy($particularPreparedBy)
    {
        $this->particularPreparedBy = $particularPreparedBy;
    }

    /**
     * @return User
     */
    public function getParticularDeliveredBy()
    {
        return $this->particularDeliveredBy;
    }

    /**
     * @param User $particularDeliveredBy
     */
    public function setParticularDeliveredBy($particularDeliveredBy)
    {
        $this->particularDeliveredBy = $particularDeliveredBy;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
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
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
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
     */
    public function setProcess($process)
    {
        $this->process = $process;
    }

    /**
     * @return Invoice
     */
    public function getHmsInvoice()
    {
        return $this->hmsInvoice;
    }

    /**
     * @param Invoice $hmsInvoice
     */
    public function setHmsInvoice($hmsInvoice)
    {
        $this->hmsInvoice = $hmsInvoice;
    }


    /**
     * @return Particular
     */
    public function getAssignDoctor()
    {
        return $this->assignDoctor;
    }

    /**
     * @param Particular $assignDoctor
     */
    public function setAssignDoctor($assignDoctor)
    {
        $this->assignDoctor = $assignDoctor;
    }

    /**
     * @return InvoicePathologicalReport
     */
    public function getInvoicePathologicalReports()
    {
        return $this->invoicePathologicalReports;
    }

    /**
     * @return User
     */
    public function getSampleCollectedBy()
    {
        return $this->sampleCollectedBy;
    }

    /**
     * @param User $sampleCollectedBy
     */
    public function setSampleCollectedBy($sampleCollectedBy)
    {
        $this->sampleCollectedBy = $sampleCollectedBy;
    }

    /**
     * @return \DateTime
     */
    public function getCollectionDate()
    {
        return $this->collectionDate;
    }

    /**
     * @param \DateTime $collectionDate
     */
    public function setCollectionDate($collectionDate)
    {
        $this->collectionDate = $collectionDate;
    }

    /**
     * @return float
     */
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * @param float $commission
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getReportCode()
    {
        return $this->reportCode;
    }

    /**
     * @param string $reportCode
     */
    public function setReportCode($reportCode)
    {
        $this->reportCode = $reportCode;
    }

	/**
	 * Sets file.
	 *
	 * @param WebTheme $file
	 */
	public function setFile(UploadedFile $file = null)
	{
		$this->file = $file;
	}

	/**
	 * Get file.
	 *
	 * @return WebTheme
	 */
	public function getFile()
	{
		return $this->file;
	}

	public function getAbsolutePath()
	{
		return null === $this->path
			? null
			: $this->getUploadRootDir(). $this->path;
	}

	public function getWebPath()
	{
		return null === $this->path
			? null
			: $this->getUploadDir().'/'.$this->path;
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

	protected function getUploadDir()
	{
		return 'uploads/domain/'.$this->getHmsInvoice()->getHospitalConfig()->getGlobalOption()->getId().'/hms/report/';
	}

	public function upload()
	{
		// the file property can be empty if the field is not required
		if (null === $this->getFile()) {
			return;
		}

		$filename = date('YmdHmi') . "_" . $this->getFile()->getClientOriginalName();

		$this->getFile()->move(
			$this->getUploadRootDir(),
			$filename
		);
		// set the path property to the filename where you've saved the file
		$this->path = $filename;

		// clean up the file property as you won't need it anymore
		$this->file = null;
	}

    /**
     * @return mixed
     */
    public function getAssignLabuser()
    {
        return $this->assignLabuser;
    }

    /**
     * @param mixed $assignLabuser
     */
    public function setAssignLabuser($assignLabuser)
    {
        $this->assignLabuser = $assignLabuser;
    }

    /**
     * @return string
     */
    public function getReportContent()
    {
        return $this->reportContent;
    }

    /**
     * @param string $reportContent
     */
    public function setReportContent($reportContent)
    {
        $this->reportContent = $reportContent;
    }

    /**
     * @return mixed
     */
    public function getAdmissionPatientParticular()
    {
        return $this->admissionPatientParticular;
    }

    /**
     * @param mixed $admissionPatientParticular
     */
    public function setAdmissionPatientParticular($admissionPatientParticular)
    {
        $this->admissionPatientParticular = $admissionPatientParticular;
    }

    /**
     * @return float
     */
    public function getDiscountPrice()
    {
        return $this->discountPrice;
    }

    /**
     * @param float $discountPrice
     */
    public function setDiscountPrice($discountPrice)
    {
        $this->discountPrice = $discountPrice;
    }

    /**
     * @return mixed
     */
    public function getInvoicePathologicalGroup()
    {
        return $this->invoicePathologicalGroup;
    }

    /**
     * @param mixed $invoicePathologicalGroup
     */
    public function setInvoicePathologicalGroup($invoicePathologicalGroup)
    {
        $this->invoicePathologicalGroup = $invoicePathologicalGroup;
    }

    /**
     * @return float
     */
    public function getSalesDiscountPrice()
    {
        return $this->salesDiscountPrice;
    }

    /**
     * @param float $salesDiscountPrice
     */
    public function setSalesDiscountPrice($salesDiscountPrice)
    {
        $this->salesDiscountPrice = $salesDiscountPrice;
    }





}

