<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Particular
 *
 * @ORM\Table( name ="hms_particular")
 * @ORM\Entity()
 */
class Particular
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
     * @ORM\ManyToOne(targetEntity="Config" , cascade={"detach","merge"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;


    /**
     * @ORM\ManyToOne(targetEntity="ParticularType")
     * @ORM\JoinColumn(name="particular_type_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $particularType;

     /**
     * @ORM\ManyToOne(targetEntity="InvestigationMasterReport")
     * @ORM\JoinColumn(name="investigation_report_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $investigation;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Particular", inversedBy="particularDepartments")
     **/
    private $department;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="employee_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
     private  $employee;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="display_name", type="string", length=255, nullable=true)
     */
    private $displayName;


    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", nullable=true)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="instruction", type="text", nullable=true)
     */
    private $instruction;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

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
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $status;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isDelete;


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

