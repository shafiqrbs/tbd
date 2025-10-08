<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Particular
 *
 * @ORM\Table( name ="hms_treatment_medicine")
 * @ORM\Entity()
 */
class TreatmentMedicine
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
     * @ORM\ManyToOne(targetEntity="Config" , cascade={"detach","merge"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="treatment_template_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $treatmentTemplate;


     /**
     * @ORM\ManyToOne(targetEntity="MedicineDosage")
     * @ORM\JoinColumn(name="medicine_dosage_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $medicineDosage;



     /**
     * @ORM\ManyToOne(targetEntity="MedicineDosage")
     * @ORM\JoinColumn(name="medicine_bymeal_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $medicineBymeal;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItem")
     * @ORM\JoinColumn(name="medicine_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $medicine;


    /**
     * @var string
     *
     * @ORM\Column(name="generic", type="string", length=255, nullable=true)
     */
    private $generic;


    /**
     * @var string
     *
     * @ORM\Column(name="medicine_name", type="string", length=255, nullable=true)
     */
    private $medicineName;


     /**
     * @var string
     *
     * @ORM\Column(name="by_meal", type="string", length=200, nullable=true)
     */
    private $byMeal;

    /**
     * @var string
     *
     * @ORM\Column(name="dosage_form", type="string", length=100, nullable=true)
     */
    private $dosageForm;


     /**
     * @var string
     *
     * @ORM\Column(name="dosage", type="string", length=100, nullable=true)
     */
    private $dosage;

    /**
     * @var string
     *
     * @ORM\Column(name="duration", type="string", length=100, nullable=true)
     */
    private $duration;

    /**
     * @var float
     *
     * @ORM\Column(name="period", type="integer", nullable=true)
     */
    private $period;


    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     */
    private $quantity;


    /**
     * @var float
     *
     * @ORM\Column(name="total_quantity", type="integer", nullable=true)
     */
    private $total_quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="instruction", type="text", nullable=true)
     */
    private $instruction;

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
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $status;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isDelete;


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

