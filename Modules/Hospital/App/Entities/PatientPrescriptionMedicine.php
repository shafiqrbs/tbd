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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItem")
     * @ORM\JoinColumn(name="stock_item_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $stockItem;


     /**
     * @ORM\ManyToOne(targetEntity="MedicineDetails")
     * @ORM\JoinColumn(name="medicine_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $medicine;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Hospital\App\Entities\MedicineDosage")
     * @ORM\JoinColumn(name="medicine_dosage_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $medicineDosage;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Hospital\App\Entities\MedicineDosage")
     * @ORM\JoinColumn(name="medicine_bymeal_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $medicineBymeal;

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
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $byMeal;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $byMealBn;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $doseDetails;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $doseDetailsBn;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $generic;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer",  nullable=true)
     */
    private $genericId;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isStock;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $ipdStatus;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $opdStatus;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isActive;

    /**
     * @var string
     *
     * @ORM\Column(name="continue_mode", type="string", length=20, nullable=true,options={"default"="stat"})
     * Stat|sos
     */
    private $continueMode;


    /**
     * @var \DateTime
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    private $endDate;



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

