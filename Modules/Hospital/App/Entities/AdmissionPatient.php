<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoiceParticular
 *
 * @ORM\Table( name = "hms_admission_patient_details")
 * @ORM\Entity()
 */
class AdmissionPatient
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
     * @ORM\OneToOne(targetEntity="Invoice")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $hmsInvoice;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $country;


     /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Setting")
     * @ORM\JoinColumn(name="religion_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $religion;


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
     * @var string
     *
     * @ORM\Column(name="profession", type="string", length=100, nullable =true)
     */
    private $profession;


    /**
     * @var string
     *
     * @ORM\Column( type="string",length=100 , nullable = true)
     */
    private $maritalStatus;

    /**
     * @var string
     *
     * @ORM\Column( type="string",length=100 , nullable = true)
     */
    private $patientRelation;



    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=30, nullable=true)
     */
    private $process ='In-progress';

     /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create_at")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update_at")
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;


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

