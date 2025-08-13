<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Service
 *
 * @ORM\Table( name ="hms_particular_mode_matrix")
 * @ORM\Entity()
 */
class ParticularModeMatrix
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
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $createdBy;

    /**
     * @var string
     *
     * @ORM\Column(name="module", type="string",length=100,  nullable=true)
     */
    private $module;

    /**
     * @var string
     *
     * @ORM\Column(name="module_mode", type="string", length=100, nullable=true)
     */
    private $moduleMode;


    /**
     * @var string
     *
     * @ORM\Column(name="particular_types", type="string",  nullable=true)
     */
    private $particularTypes;


    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string",nullable=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="short_code", type="string", length=10, nullable=true)
     */
    private $shortCode;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=10, nullable=true)
     */
    private $code;

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

