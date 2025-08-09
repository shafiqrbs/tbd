<?php

namespace Modules\Hospital\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * Particular
 *
 * @ORM\Table( name = "hms_master_diagnostic_report")
 * @ORM\Entity()

 */
class InvestigationMasterReport
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
     * @ORM\OneToMany(targetEntity="InvestigationMasterReportFormat", mappedBy="diagnosticReport")
    * @ORM\OrderBy({"parent" = "ASC"})
     **/
    private $diagnosticReportFormats;

   /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\HospitalBundle\Entity\Category")
     **/
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\HospitalBundle\Entity\Category")
     **/
    private $department;

      /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="sepcimen", type="string", length=255, nullable=true)
     */
    private $sepcimen;


    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="instruction", type="text", nullable=true)
     */
    private $instruction;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column( type="string", length=10, nullable=true)
     */
    private $particularCode;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean" , nullable=true)
     */
    private $testDuration = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" , nullable=true)
     */
    private $reportFormat = false;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;


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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Particular
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getHmsParticulars()
    {
        return $this->hmsParticulars;
    }

    /**
     * @return HmsCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param HmsCategory $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return HmsCategory
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param HmsCategory $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }

    /**
     * @return string
     */
    public function getSepcimen()
    {
        return $this->sepcimen;
    }

    /**
     * @param string $sepcimen
     */
    public function setSepcimen($sepcimen)
    {
        $this->sepcimen = $sepcimen;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * @param string $instruction
     */
    public function setInstruction($instruction)
    {
        $this->instruction = $instruction;
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
    public function getParticularCode()
    {
        return $this->particularCode;
    }

    /**
     * @param string $particularCode
     */
    public function setParticularCode($particularCode)
    {
        $this->particularCode = $particularCode;
    }

    /**
     * @return bool
     */
    public function isTestDuration()
    {
        return $this->testDuration;
    }

    /**
     * @param bool $testDuration
     */
    public function setTestDuration($testDuration)
    {
        $this->testDuration = $testDuration;
    }

    /**
     * @return bool
     */
    public function isReportFormat()
    {
        return $this->reportFormat;
    }

    /**
     * @param bool $reportFormat
     */
    public function setReportFormat($reportFormat)
    {
        $this->reportFormat = $reportFormat;
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
    public function setStatus($status)
    {
        $this->status = $status;
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
     * @return InvestigationMasterReportFormat
     */
    public function getDiagnosticReportFormats()
    {
        return $this->diagnosticReportFormats;
    }

    /**
     * @return DpsInvoice
     */
    public function getDpsInvoice()
    {
        return $this->dpsInvoice;
    }


}

