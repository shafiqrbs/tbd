<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Particular
 *
 * @ORM\Table( name ="hms_particular_details")
 * @ORM\Entity()
 */
class ParticularDetails
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
     * @ORM\OneToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="particular_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $particular;

    /**
     * @ORM\ManyToOne(targetEntity="ParticularMode")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $unit;

    /**
     * @ORM\ManyToOne(targetEntity="ParticularMode")
     * @ORM\JoinColumn(name="patient_mode_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $patientMode;

     /**
     * @ORM\ManyToOne(targetEntity="ParticularMode")
     * @ORM\JoinColumn(name="patient_type_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $patientType;

    /**
     * @ORM\ManyToOne(targetEntity="ParticularMode")
     * @ORM\JoinColumn(name="gender_mode_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $genderMode;

    /**
     * @ORM\ManyToOne(targetEntity="ParticularMode")
     * @ORM\JoinColumn(name="payment_mode_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $paymentMode;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $roomId;

      /**
     * @ORM\ManyToOne(targetEntity="ParticularMode")
     * @ORM\JoinColumn(name="cabin_mode_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $cabinMode;


    /**
     * @var string
     *
     * @ORM\Column(name="display_name", type="string", length=255, nullable=true)
     */
    private $displayName;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reportMachineName;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $investigationRoom;


    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="smallint", length=3, nullable=true)
     */
    private $quantity = 1;


    /**
     * @var integer
     *
     * @ORM\Column( type="integer", nullable=true)
     */
    private $openingQuantity;

    /**
     * @var integer
     *
     * @ORM\Column( type="integer", nullable=true)
     */
    private $minQuantity;


    /**
     * @var integer
     *
     * @ORM\Column( type="integer", nullable=true)
     */
    private $purchaseQuantity;

    /**
     * @var integer
     *
     * @ORM\Column( type="integer", nullable=true)
     */
    private $salesQuantity;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", nullable=true)
     */
    private $purchaseAverage;

    /**
     * @var string
     *
     * @ORM\Column( type="decimal", nullable=true)
     */
    private $purchasePrice;


     /**
     * @var string
     *
     * @ORM\Column(type="decimal", nullable=true)
     */
    private $ipdVisitCharge;


    /**
     * @var string
     *
     * @ORM\Column(name="room", type="string", length=10, nullable=true)
     */
    private $room;


    /**
     * @var string
     *
     * @ORM\Column(name="sepcimen", type="string", length=255, nullable=true)
     */
    private $sepcimen;


    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column( type="text", nullable=true)
     */
    private $reportContent;

    /**
     * @var string
     *
     * @ORM\Column(name="instruction", type="text", nullable=true)
     */
    private $instruction;


    /**
     * @var string
     *
     * @ORM\Column(type="decimal", nullable=true)
     */
    private $overHeadCost;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", nullable=true)
     */
    private $price;


    /**
     * @var \string
     *
     * @ORM\Column(type="decimal", nullable=true)
     */
    private $minimumPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="commission", type="decimal" , nullable=true)
     */
    private $commission;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $phoneNo;

    /**
     * @var string
     *
     * @ORM\Column( type="string", length=10, nullable=true)
     */
    private $startHour;

    /**
     * @var string
     *
     * @ORM\Column( type="string", length=10, nullable=true)
     */
    private $endHour;

    /**
     * @var array
     *
     * @ORM\Column( type="array", nullable=true)
     */
    private $weeklyOffDay;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="specialist", type="string", length=255, nullable=true)
     */
    private $specialist;

    /**
     * @var string
     *
     * @ORM\Column( type="string", length=255, nullable=true)
     */
    private $educationalDegree;

    /**
     * @var string
     *
     * @ORM\Column( type="string", length=255, nullable=true)
     */
    private $doctorSignature;

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $doctorSignatureBangla;

    /**
     * @var string
     *
     * @ORM\Column( type="string", length=255, nullable=true)
     */
    private $pathologistSignature;

    /**
     * @var string
     *
     * @ORM\Column( type="string", length=256, nullable=true)
     */
    private $currentJob;

     /**
     * @var string
     *
     * @ORM\Column(name="remark", type="string", length=256, nullable=true)
     */
    private $remark;

     /**
     * @var string
     *
     * @ORM\Column( type="string", length=50, nullable=true)
     */
    private $visitTime;

    /**
     * @var string
     *
     * @ORM\Column(name="designation", type="string", length=256, nullable=true)
     */
    private $designation;


    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;

    /**
     * @var integer
     *
     * @ORM\Column( type="integer",  nullable=true)
     */
    private $reportHeight = 8;

    /**
     * @var string
     *
     * @ORM\Column( type="string", length=10, nullable=true)
     */
    private $particularCode;


    /**
     * @var string
     *
     * @ORM\Column( type="string", length=50, nullable=true)
     */
    private $identityCardNo;


    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=15, nullable=true)
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
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $testDuration;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $reportFormat;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $discountValid;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isDoctor;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $admissionDefault;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $reportUnitHide;


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
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $isReportContent;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $isMachineFormat;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $sendToAccount;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $additionalField;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isAttachment;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $signaturePath;



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

