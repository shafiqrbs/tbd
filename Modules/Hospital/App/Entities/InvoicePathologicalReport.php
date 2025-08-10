<?php

namespace Modules\Hospital\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoicePathologicalReport
 *
 * @ORM\Table( name = "hms_invoice_pathological_report")
 * @ORM\Entity()
 */
class InvoicePathologicalReport
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
    private $config;


    /**
     * @ORM\ManyToOne(targetEntity="InvestigationReportFormat")
     * @ORM\JoinColumn(name="master_report_format_id", referencedColumnName="id")
     **/
    private $masterReportFormat;

    /**
     * @ORM\ManyToOne(targetEntity="InvoicePathologicalReport", inversedBy="children", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="parent", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="InvoicePathologicalReport" , mappedBy="parent")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $children;


    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(onDelete="CASCADE")
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
     * @ORM\Column(name="parentName", type="string", length=200, nullable=true)
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
     * @ORM\Column(name="referenceValue", type="text", nullable=true)
     */
    private $referenceValue;

    /**
     * @var string
     *
     * @ORM\Column(name="sampleValue", type="text", nullable=true)
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
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;


}

