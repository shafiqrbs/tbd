<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AdmissionPatientMedicine
 *
 * @ORM\Table(name = "hms_admission_patient_mediciner")
 * @ORM\Entity()
 */
class AdmissionPatientMedicine
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
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $uid;


    /**
     * @ORM\ManyToOne(targetEntity="Invoice")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $hmsInvoice;


    /**
     * @ORM\ManyToOne(targetEntity="Prescription")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $prescription;


    /**
     * @ORM\ManyToOne(targetEntity="MedicineDetails")
     * @ORM\JoinColumn(name="medicine_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $medicine;

     /**
     * @ORM\ManyToOne(targetEntity="MedicineDosage")
     * @ORM\JoinColumn(name="medicine_dosage_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $medicineDosage;

    /**
     * @var string
     *
     * @ORM\Column(name="medicine_name", type="string", length=50, nullable=true)
     */
    private $medicineName;

    /**
     * @var integer
     *
     * @ORM\Column(name="daily_quantity", type="smallint", nullable=true)
     */
    private $dailyQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="smallint", nullable=true)
     */
    private $quantity;


    /**
     * @var string
     *
     * @ORM\Column(type="process" , type="string", length=30,options={"default"="new"})
     */
    private $process;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=true, options={"default"="false"})
     */
    private $status;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true, options={"default"="false"})
     */
     private $isWaver;

     /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
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

