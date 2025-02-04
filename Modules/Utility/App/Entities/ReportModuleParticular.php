<?php

namespace Modules\Utility\App\Entities;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * Theme
 *
 * @ORM\Table(name="uti_report_module_particular")
 * @ORM\Entity()
 */
class ReportModuleParticular
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
     * @ORM\ManyToOne(targetEntity="ReportModule")
     **/
    private $report;


    /**
     * @var string
     *
     * @ORM\Column(name="label_name",type="string", length=255)
     */
    private $labelName;


     /**
     * @var string
     *
     * @ORM\Column(name="field_name",type="string", length=255)
     */
    private $fieldName;


    /**
     * @var string
     *
     * @ORM\Column(name="field_type",type="string", length=255)
     */
    private $fieldType;

    /**
     * @var string
     *
     * @ORM\Column(name="data_source_name",type="string", length=255)
     */
    private $dataSourceName;


    /**
     * @var boolean
     *
     * @ORM\Column(name="is_required",type="boolean")
     */
    private $isRequired = false;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status",options={"default":1}, type="boolean")
     */
    private $status = true;


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



}
