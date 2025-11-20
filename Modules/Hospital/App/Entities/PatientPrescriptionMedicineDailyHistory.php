<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * PrescriptionMedicine
 *
 * @ORM\Table( name = "hms_patient_prescription_medicine_daily_history")
 * @ORM\Entity()
 */
class PatientPrescriptionMedicineDailyHistory
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
     * @ORM\JoinColumn(name="hms_invoice_id",onDelete="CASCADE")
     **/
    private $invoice;


    /**
     * @ORM\ManyToOne(targetEntity="PatientPrescriptionMedicine")
     * @ORM\JoinColumn(name="prescription_medicine_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $prescriptionMedicine;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItem")
     * @ORM\JoinColumn(name="stock_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $stock;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Warehouse")
     * @ORM\JoinColumn(name="warehouse_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $warehouse;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Sales")
     * @ORM\JoinColumn(name="sale_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $sale;

    /**
     * @ORM\OneToOne(targetEntity="Modules\Inventory\App\Entities\SalesItem")
     * @ORM\JoinColumn(name="sale_item_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $saleItem;


     /**
     * @var string
     *
     * @ORM\Column(name="day_date", type="string", length=40, nullable=true)
     */
    private $dayDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer", length=4, nullable=true)
     */
    private $quantity;


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

