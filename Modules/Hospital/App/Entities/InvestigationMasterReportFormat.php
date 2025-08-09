<?php

namespace Modules\Hospital\App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * DiagnosticReportFormat
 *
 * @ORM\Table( name ="hms_master_diagnostic_report_format")
 * @ORM\Entity()
 */
class InvestigationMasterReportFormat
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
     * @ORM\ManyToOne(targetEntity="InvestigationMasterReportFormat", inversedBy="children", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="parent", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="InvestigationMasterReportFormat" , mappedBy="parent")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="InvestigationMasterReport", inversedBy="diagnosticReportFormats")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $diagnosticReport;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=200, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=10, nullable=true)
     */
    private $code;

    /**
     * @var int
     *
     * @ORM\Column(name="sorting", type="smallint", length=2, nullable=true)
     */
    private $sorting;

    /**
     * @var string
     *
     * @ORM\Column(name="referenceValue", type="text", nullable=true)
     */
    private $referenceValue;

    /**
     * @var string
     *
     * @ORM\Column(name="unit", type="string", length=50, nullable=true)
     */
    private $unit;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;


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
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }


    /**
     * @return bool
     */
    public function getStatus()
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
     * @return InvestigationMasterReportFormat
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param InvestigationMasterReportFormat $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return InvestigationMasterReportFormat
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return string
     */
    public function getReferenceValue()
    {
        return $this->referenceValue;
    }

    /**
     * @param string $referenceValue
     */
    public function setReferenceValue($referenceValue)
    {
        $this->referenceValue = $referenceValue;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }


    /**
     * @return int
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * @param int $sorting
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;
    }

    /**
     * @return mixed
     */
    public function getDiagnosticReport()
    {
        return $this->diagnosticReport;
    }

    /**
     * @param mixed $diagnosticReport
     */
    public function setDiagnosticReport($diagnosticReport)
    {
        $this->diagnosticReport = $diagnosticReport;
    }


}

