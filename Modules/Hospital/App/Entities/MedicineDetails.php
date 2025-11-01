<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Particular
 *
 * @ORM\Table( name ="hms_medicine_details")
 * @ORM\Entity()
 */
class MedicineDetails
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
     private $product;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItem")
     * @ORM\JoinColumn(name="stock_item_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
     private $stockItem;

    /**
     * @ORM\ManyToOne(targetEntity="MedicineStock")
     * @ORM\JoinColumn(name="medicine_stock_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
     private $medicineStock;

    /**
     * @ORM\ManyToOne(targetEntity="ParticularMode")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
     private $medicineGroup;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Medicine\App\Entities\MedicineCompany")
     * @ORM\JoinColumn(name="medicine_company_id",referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
     private $medicineCompany;

     /**
     * @ORM\ManyToOne(targetEntity="MedicineDosage")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $medicineDosage;

    /**
     * @ORM\ManyToOne(targetEntity="MedicineDosage")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $medicineBymeal;


    /**
     * @var string
     *
     * @ORM\Column(name="generic", type="string", length=255, nullable=true)
     */
    private $generic;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="display_name", type="string", length=255, nullable=true)
     */
    private $displayName;


    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     */
    private $company;


     /**
     * @var string
     *
     * @ORM\Column(name="formulation", type="string", length=100, nullable=true)
     */
    private $formulation;


     /**
     * @var string
     *
     * @ORM\Column(name="dose_details", type="string", length=100, nullable=true)
     */
    private $doseDetails;


    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, nullable=true)
     */
    private $slug;

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $genericId;

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $dosesForm;

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $dosesDetails;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $byMeal;

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $durationMonth;

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $durationDay;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", length=5, nullable=true)
     */
    private $priority;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", length=5, nullable=true)
     */
    private $opdQuantity;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="decimal", nullable=true)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="instruction", type="text", nullable=true)
     */
    private $instruction;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

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
     * @ORM\Column(type="boolean",options={"default"="0"})
     */
    private $status;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" ,options={"default":0})
     */
    private $isDelete;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $opdStatus;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $ipdStatus;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $adminStatus;






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

