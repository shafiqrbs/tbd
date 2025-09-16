<?php

namespace Modules\Hospital\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * HealthShare
 *
 * @ORM\Table( name ="hms_health_share")
 * @ORM\Entity()
 */
class HealthShare
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
     * @ORM\OneToOne(targetEntity="Config", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="config_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $config;

    /**
     * @var string
     *
     * @ORM\Column(name="x_auth_token", type="string", nullable=true)
     */
    private $xAuthToken;

    /**
     * @var string
     *
     * @ORM\Column(name="client_id", type="string", nullable=true)
     */
    private $clientId;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    private $email;


    /**
     * @var string
     *
     * @ORM\Column(name="from", type="string", nullable=true)
     */
    private $from;


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

