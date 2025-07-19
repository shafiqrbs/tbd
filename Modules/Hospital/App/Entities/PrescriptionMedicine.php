<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * PrescriptionMedicine
 *
 * @ORM\Table( name = "hms_prescription_medicine")
 * @ORM\Entity()
 */
class PrescriptionMedicine
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
     * @ORM\ManyToOne(targetEntity="Modules\Medicine\App\Entities\Medicine")
     * @ORM\JoinColumn(name="medicine_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $medicine;


     /**
     * @var string
     *
     * @ORM\Column(name="medicineQuantity", type="string", length=100, nullable=true)
     */
    private $medicineQuantity;

    /**
     * @var string
     *
     * @ORM\Column(name="medicineDose", type="string", length=100, nullable=true)
     */
    private $medicineDose;

     /**
     * @var string
     *
     * @ORM\Column(name="totalQuantity", type="string", length=100, nullable=true)
     */
    private $totalQuantity;

    /**
     * @var string
     *
     * @ORM\Column(name="medicineDoseTime", type="string", length=100, nullable=true)
     */
    private $medicineDoseTime;

    /**
     * @var string
     *
     * @ORM\Column(name="medicineName", type="string", length=255, nullable=true)
     */
    private $medicineName;

    /**
     * @var string
     *
     * @ORM\Column(name="medicineDuration", type="string", length=100, nullable=true)
     */
    private $medicineDuration;


    /**
     * @var string
     *
     * @ORM\Column(name="unit", type="string", length=50, nullable=true)
     */
    private $unit;


    /**
     * @var string
     *
     * @ORM\Column(name="medicineDurationType", type="string", length=20, nullable=true)
     */
    private $medicineDurationType;


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

