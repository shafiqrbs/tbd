<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * PrescriptionMedicine
 *
 * @ORM\Table( name = "hms_patient_prescription_medicine")
 * @ORM\Entity()
 */
class PatientPrescriptionMedicine
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
     * @ORM\ManyToOne(targetEntity="Prescription")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $prescription;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItem")
     * @ORM\JoinColumn(name="medicine_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $medicine;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Hospital\App\Entities\MedicineDosage")
     * @ORM\JoinColumn(name="medicine_dosage_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $medicineDosage;

    /**
     * @var string
     *
     * @ORM\Column(name="duration", type="string", length=100, nullable=true)
     */
    private $duration;

     /**
     * @var string
     *
     * @ORM\Column(name="quantity", type="string", length=100, nullable=true)
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $medicineName;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isStock;


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

