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
     * @ORM\ManyToOne(targetEntity="ParticularType", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="particular_type_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $particularType;

   /**
     * @ORM\ManyToOne(targetEntity="ParticularMode", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="particular_mode_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $operationModeId;

    /**
     * @var string
     *
     * @ORM\Column(name="operation_mode", type="string",  nullable=true)
     */
    private $operationMode;


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

