<?php

namespace Modules\Production\App\Entities;

use App\Entity\Application\Production;
use App\Entity\Core\Vendor;
use Modules\Core\App\Entities\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
/**
 * Damage
 *
 * @ORM\Table("pro_receive_batch")
 * @ORM\Entity(repositoryClass="Modules\Production\App\Repositories\ProductionReceiveBatchRepository")
 * @Gedmo\Uploadable(filenameGenerator="SHA1", allowOverwrite=true, appendNumber=true)
 */
class ProductionReceiveBatch
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
     * @ORM\ManyToOne(targetEntity="Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;



     /**
     * @ORM\ManyToOne(targetEntity="ProductionBatch")
      * @ORM\JoinColumn(onDelete="CASCADE")
      **/
     private  $batch;



    /**
     * @var string
     *
     * @ORM\Column(name="challanNo", type="string", length = 50, nullable=true)
     */
    private $challanNo;


    /**
     * @var integer
     *
     * @ORM\Column(type="integer" , nullable=true)
     */
    private $code;


    /**
     * @var string
     *
     * @ORM\Column(type="string" , nullable=true)
     */
    private $invoice;


     /**
     * @var string
     *
     * @ORM\Column(type="string" , nullable=true)
     */
     private $remark;


    /**
     * @var string
     *
     * @ORM\Column(type="string" , nullable=true)
     */
     private $requsitionNo;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $checkedBy;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $approvedBy;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=50, nullable = true)
     */
    private $process="created";

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable = true)
     */
    private $status = false;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="issueDate", type="datetime")
     */
    private $issueDate;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="receiveDate", type="datetime", nullable=true)
     */
    private $receiveDate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $receiveTime;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     * @ORM\Column(name="updated", type="datetime", nullable = true)
     */
    private $updated;

    /**
     * @ORM\Column(name="path", type="string", nullable=true)
     * @Gedmo\UploadableFilePath
     */
    protected $path;

    /**
     * @Assert\File(maxSize="8388608")
     */
    protected $file;


    /**
     * Get id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return Production
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Production $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getInvoice(): ? string
    {
        return $this->invoice;
    }

    /**
     * @param string $invoice
     */
    public function setInvoice(string $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return string
     */
    public function getRemark(): ? string
    {
        return $this->remark;
    }

    /**
     * @param string $remark
     */
    public function setRemark(string $remark)
    {
        $this->remark = $remark;
    }

    /**
     * @return string
     */
    public function getRequsitionNo(): ? string
    {
        return $this->requsitionNo;
    }

    /**
     * @param string $requsitionNo
     */
    public function setRequsitionNo(string $requsitionNo)
    {
        $this->requsitionNo = $requsitionNo;
    }

    /**
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return User
     */
    public function getCheckedBy()
    {
        return $this->checkedBy;
    }

    /**
     * @param User $checkedBy
     */
    public function setCheckedBy($checkedBy)
    {
        $this->checkedBy = $checkedBy;
    }

    /**
     * @return User
     */
    public function getApprovedBy()
    {
        return $this->approvedBy;
    }

    /**
     * @param User $approvedBy
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
     */
    public function setProcess( $process)
    {
        $this->process = $process;
    }

    /**
     * @return \DateTime
     */
    public function getIssueDate()
    {
        return $this->issueDate;
    }

    /**
     * @param \DateTime $issueDate
     */
    public function setIssueDate($issueDate)
    {
        $this->issueDate = $issueDate;
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
    public function setUpdated( $updated)
    {
        $this->updated = $updated;
    }

    /**
     * Sets file.
     *
     * @param User $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return User
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
        return WEB_ROOT .'/uploads/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'work-order/';
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
     * @return \DateTime
     */
    public function getReceiveDate()
    {
        return $this->receiveDate;
    }

    /**
     * @param \DateTime $receiveDate
     */
    public function setReceiveDate(\DateTime $receiveDate)
    {
        $this->receiveDate = $receiveDate;
    }

    /**
     * @return int
     */
    public function getCode(): ? int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code)
    {
        $this->code = $code;
    }


    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode(string $mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return ProductionReceiveBatchItem
     */
    public function getBatchItems()
    {
        return $this->batchItems;
    }


    /**
     * @return bool
     */
    public function isStatus()
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus(bool $status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getBatch()
    {
        return $this->batch;
    }

    /**
     * @param mixed $batch
     */
    public function setBatch($batch)
    {
        $this->batch = $batch;
    }

    /**
     * @return ProductionReceiveBatchItem
     */
    public function getReceiveItems()
    {
        return $this->receiveItems;
    }

    /**
     * @return string
     */
    public function getChallanNo()
    {
        return $this->challanNo;
    }

    /**
     * @param string $challanNo
     */
    public function setChallanNo(string $challanNo)
    {
        $this->challanNo = $challanNo;
    }

    /**
     * @return \DateTime
     */
    public function getChallanDate()
    {
        return $this->challanDate;
    }

    /**
     * @param \DateTime $challanDate
     */
    public function setChallanDate($challanDate)
    {
        $this->challanDate = $challanDate;
    }

    /**
     * @return string
     */
    public function getReceiveTime()
    {
        return $this->receiveTime;
    }

    /**
     * @param string $receiveTime
     */
    public function setReceiveTime($receiveTime)
    {
        $this->receiveTime = $receiveTime;
    }



}

