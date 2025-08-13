<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * PathologicalReport
 *
 * @ORM\Table( name ="hms_investigation_report_format")
 * @ORM\Entity()
 */
class InvestigationReportFormat
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
     * @ORM\ManyToOne(targetEntity="Config", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="config_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $config;

    /**
     * @ORM\ManyToOne(targetEntity="InvestigationReportFormat", inversedBy="children", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="parent", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;


    /**
     * @ORM\OneToMany(targetEntity="InvestigationReportFormat" , mappedBy="parent")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $children;



    /**
     * @ORM\ManyToOne(targetEntity="InvestigationMasterReportFormat")
     **/
    private $masterReportFormat;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="particular_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $particular;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=200, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="parent_name", type="string", length=200, nullable=true)
     */
    private $parentName;

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
     * @ORM\Column(name="reference_value", type="text", nullable=true)
     */
    private $referenceValue;

     /**
     * @var string
     *
     * @ORM\Column(name="sample_value", type="text", nullable=true)
     */
    private $sampleValue;

    /**
     * @var string
     *
     * @ORM\Column(name="unit", type="string", length=50, nullable=true)
     */
    private $unit;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $status;

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


}

