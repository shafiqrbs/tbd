<?php

namespace Modules\Hospital\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * HmsCommission
 *
 * @ORM\Table( name ="hms_patient_history")
 * @ORM\Entity()
 */
class PatientHistory
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
     * @ORM\ManyToOne(targetEntity="Patient", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $patient;


     /**
     * @ORM\ManyToOne(targetEntity="Invoice", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $invoice;


    /**
     * @var string
     *
     * @ORM\Column( type="text", nullable=true)
     */
    private $jsonContent;


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

