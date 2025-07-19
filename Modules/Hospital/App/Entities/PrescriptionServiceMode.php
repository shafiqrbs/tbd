<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Service
 *
 * @ORM\Table( name ="hms_prescription_service_mode")
 * @ORM\Entity()
 */
class PrescriptionServiceMode
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=200, nullable=true)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="serviceFormat", type="string", length=50, nullable=true)
     */
    private $serviceFormat;


     /**
     * @var int
     *
     * @ORM\Column(name="servicePosition", type="smallint", length =1, nullable=true)
     */
    private $servicePosition;

    /**
     * @var integer
     *
     * @ORM\Column(name="serviceHeight", type="integer", length=3, nullable=true)
     */
    private $serviceHeight;

    /**
     * @var int
     *
     * @ORM\Column(name="serviceSorting", type="smallint",  length=2, nullable=true)
     */
    private $serviceSorting = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="serviceHeaderShow", type="boolean", nullable=true)
     */
    private $serviceHeaderShow= false;


    /**
     * @var boolean
     *
     * @ORM\Column(name="serviceShow", type="boolean", nullable=true)
     */
    private $serviceShow = false;


    /**
     * @Gedmo\Slug(fields={"name"})
     * @Doctrine\ORM\Mapping\Column(length=255)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=10, nullable=true)
     */
    private $code;

    /**
     * @var int
     *
     * @ORM\Column(name="sorting", type="smallint",  length=2, nullable=true)
     */
    private $sorting = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="hasQuantity", type="boolean" )
     */
    private $hasQuantity = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;

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

