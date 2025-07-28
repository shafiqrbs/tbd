<?php

namespace Modules\Hospital\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Patient
 *
 * @ORM\Table( name ="hms_patient")
 * @ORM\Entity()
 */
class Patient
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
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column( type="string",length=20, nullable = true)
     */
    private $bloodPressure;

    /**
     * @var string
     *
     * @ORM\Column(name="diabetes", type="string",length=30, nullable = true)
     */
    private $diabetes;

    /**
     * @var string
     *
     * @ORM\Column(name="height", type="string",length=20, nullable = true)
     */
    private $height;

    /**
     * @var string
     *
     * @ORM\Column( type="string", length=20, nullable = true)
     */
    private $ageType;


    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=10 , nullable = true)
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable =true)
     */
    private $bloodGroup;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dob", type="datetime", nullable=true)
     */
    private $dob;

    /**
     * @var string
     *
     * @ORM\Column(type="string",length=10 , nullable = true)
     */
    private $ageGroup;


    /**
     * @var string
     *
     * @ORM\Column( type="string",length=30 , nullable = true)
     */
    private $maritalStatus;

    /**
     * @var string
     *
     * @ORM\Column(type="string",length=200 , nullable = true)
     */
    private $alternativeContactPerson;

    /**
     * @var string
     *
     * @ORM\Column(type="string",length=50 , nullable = true)
     */
    private $alternativeContactMobile;


    /**
     * @var string
     *
     * @ORM\Column(type="string",length=100 , nullable = true)
     */
    private $alternativeRelation;


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
     * @ORM\Column(type="text",  nullable =true)
     */
    private $permanentAddress;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable =true)
     */
    private $fatherName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable =true)
     */
    private $motherName;

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

