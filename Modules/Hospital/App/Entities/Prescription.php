<?php

namespace Modules\Hospital\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * HmsCommission
 *
 * @ORM\Table( name ="hms_prescription")
 * @ORM\Entity()
 */
class Prescription
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
     * @ORM\OneToOne(targetEntity="Invoice")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $hmsInvoice;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
     private $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $doctor;


    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $followUpId;

    /**
     * @var string
     *
     * @ORM\Column( type="string",length=20, nullable = true)
     */
    private $bloodPressure;

     /**
     * @var string
     *
     * @ORM\Column( type="text",nullable = true)
     */
    private $jsonContent;

    /**
     * @var string
     *
     * @ORM\Column(name="diabetes", type="string",length=30, nullable = true)
     */
    private $diabetes;

    /**
     * @var string
     *
     * @ORM\Column(type="string",length=10 , nullable = true)
     */
    private $ageGroup;

    /**
     * @var integer
     *
     * @ORM\Column(name="age", type="smallint",length=3, nullable = true)
     */
    private $age;

    /**
     * @var string
     *
     * @ORM\Column(name="weight", type="string",length=50, nullable = true)
     */
    private $weight;

    /**
     * @var string
     *
     * @ORM\Column(name="height", type="string",length=50, nullable = true)
     */
    private $height;

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

