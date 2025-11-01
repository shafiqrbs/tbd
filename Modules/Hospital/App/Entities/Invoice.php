<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Invoice
 *
 * @ORM\Table( name ="hms_invoice")
 * @ORM\Entity()
 */
class Invoice
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
     * @ORM\ManyToOne(targetEntity="Config", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="config_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $config;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Sales", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="sales_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $sales;

    /**
     * @ORM\ManyToOne(targetEntity="ParticularMode")
     * @ORM\JoinColumn(name="patient_mode_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     **/
    private $patientMode;

    /**
     * @ORM\ManyToOne(targetEntity="ParticularMode")
     * @ORM\JoinColumn(name="patient_payment_mode_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     **/
    private $patientPaymentMode;


     /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private $diseasesProfile;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Customer")
     **/
    private $customer;

    /**
     * @ORM\OneToOne(targetEntity="Invoice", inversedBy="children")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * })
     */
    private $parent;

    /**
     * @ORM\OneToOne(targetEntity="Invoice" , mappedBy="parent")
     * @ORM\OrderBy({"invoice" = "ASC"})
     **/
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private  $marketingExecutive;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private  $referredDoctor;

     /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private  $room;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private  $assignDoctor;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private  $anesthesiaDoctor;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private  $assistantDoctor;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItem")
     **/
    private  $cabin;

   /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private  $specialization;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private  $department;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private $admitDoctor;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private $admitConsultant;

    /**
     * @ORM\ManyToOne(targetEntity="ParticularMode")
     **/
    private $admitUnit;


    /**
     * @ORM\ManyToOne(targetEntity="ParticularMode")
     **/
    private $admitDepartment;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="approved_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $approvedBy;


     /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="waiver_created_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $waiverCreatedBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="waiver_approved_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $waiverApprovedBy;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $waiverComment;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="delivered_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $deliveredBy;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="discharge_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $dischargeBy;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="deleted_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $deletedBy;


    /**
     * @var string
     *
     * @ORM\Column(name="weight", type="string",length=50, nullable = true)
     */
    private $weight;

    /**
     * @var string
     *
     * @ORM\Column(name="height", type="string",length=50, nullable = true)
     */
    private $height;

    /**
     * @var string
     *
     * @ORM\Column(name="bp", type="string",length=50, nullable = true)
     */
    private $bp;

    /**
     * @var string
     *
     * @ORM\Column(name="oxygen", type="string",length=50, nullable = true)
     */
    private $oxygen;

    /**
     * @var string
     *
     * @ORM\Column(name="temperature", type="string",length=50, nullable = true)
     */
    private $temperature;

     /**
     * @var string
     *
     * @ORM\Column(name="sat_with_O2", type="string",length=50, nullable = true)
     */
    private $satWithO2;

     /**
     * @var string
     *
     * @ORM\Column(name="sat_without_O2", type="string",length=50, nullable = true)
     */
    private $satWithoutO2;

    /**
     * @var int
     *
     * @ORM\Column(name="sat_liter", type="integer",length=4, nullable = true)
     */
    private $satLiter;

     /**
     * @var string
     *
     * @ORM\Column(name="respiration", type="string",length=50, nullable = true)
     */
    private $respiration;

    /**
     * @var string
     *
     * @ORM\Column(name="pulse", type="string",length=50, nullable = true)
     */
    private $pulse;

    /**
     * @var string
     *
     * @ORM\Column(name="blood_sugar", type="string",length=50, nullable = true)
     */
    private $bloodSugar;

    /**
     * @var string
     *
     * @ORM\Column(name="blood_group", type="string",length=50, nullable = true)
     */
    private $bloodGroup;


    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $deletedContent;


     /**
     * @var string
     *
     * @ORM\Column(name="discount_requested_by", type="string", nullable=true)
     */
    private $discountRequestedBy;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $discountRequestedComment;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $guardianName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $guardianMobile;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $patientRelation;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $freeIdentification;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $prescriptionMode = "new";

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $barcode;


     /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $referredMode;

    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=50, nullable=true)
     */
    private $process ='Created';


    /**
     * @var string
     *
     * @ORM\Column(name="invoice", type="string", length=50, nullable=true)
     */
    private $invoice;

    /**
     * @var integer
     *
     * @ORM\Column(name="admission_day", type="integer",  nullable=true)
     */
    private $admissionDay;

    /**
     * @var integer
     *
     * @ORM\Column(name="consume_day", type="integer",  nullable=true)
     */
    private $consumeDay;

    /**
     * @var integer
     *
     * @ORM\Column(name="remaining_day", type="integer",  nullable=true)
     */
    private $remainingDay;


    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;


    /**
     * @var integer
     *
     * @ORM\Column(name="patientToken", type="integer",  nullable=true)
     */
    private $patientToken;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $paymentStatus = "Pending";

    /**
     * @var string
     *
     * @ORM\Column(name="cabin_no", type="string", length=50, nullable=true)
     */
    private $cabinNo;


    /**
     * @var string
     *
     * @ORM\Column(name="referredCommission", type="decimal", nullable=true)
     */
    private $referredCommission;


    /**
     * @var float
     *
     * @ORM\Column(type="float" , nullable=true)
     */
    private $estimateCommission;

    /**
     * @var string
     *
     * @ORM\Column(name="commission", type="decimal", nullable=true)
     */
    private $commission;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $commissionApproved;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isAdmission;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isDelete;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isVital;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $caseOfDeath;

    /**
     * @var string
     *
     * @ORM\Column(name="advice", type="text", nullable=true)
     */
    private $advice;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $doctorComment;

    /**
     * @var string
     *
     * @ORM\Column(name="disease", type="text", nullable=true)
     */
    private $disease;

    /**
     * @var string
     *
     * @ORM\Column(name="due", type="decimal", nullable=true)
     */
    private $due;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", nullable=true)
     */
    private $subTotal;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", nullable=true)
     */
    private $total;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", nullable=true)
     */
    private $amount;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $revised;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $smsAlert;


    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", nullable=true)
     */
    private $mobile;

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
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $admissionDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $releaseDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deliveryDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $appointmentDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="sorting", type="integer", length=10, nullable=true)
     */
    private $sorting = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=3, nullable=true)
     */
    private $day = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=3, nullable=true)
     */
    private $month = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=4, nullable=true)
     */
    private $year = 0;

    /**
     * @var string
     *
     * @ORM\Column( type="string", length=20, nullable=true)
     */
    private $deliveryTime;

    /**
     * @var string
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deliveryDateTime;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true, options={"default"="false"})
     */
    private $isMedicineDelivered;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $medicineDeliveredDate;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="medicine_delivered_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $medicineDeliveredBy;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $medicineDeliveredComment;

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

