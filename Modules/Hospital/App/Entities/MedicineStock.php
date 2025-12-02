<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Particular
 *
 * @ORM\Table( name ="hms_medicine_stock")
 * @ORM\Entity()
 */
class MedicineStock
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
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $dosesForm;


     /**
     * @var integer
     *
     * @ORM\Column(type="integer",  nullable=true)
     */
    private $durationDay;

    /**
     * @var integer
     *
     * @ORM\Column(name="duration", type="integer", options={"default"="1"})
     */
    private $duration;

    /**
     * @var float
     *
     * @ORM\Column(name="duration_mode", type="string", options={"default"="Day"})
     */
    private $durationMode;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", length=5, nullable=true)
     */
    private $opdQuantity;

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
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="0"})
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

