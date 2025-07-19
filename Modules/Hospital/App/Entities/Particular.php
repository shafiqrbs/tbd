<?php

namespace Appstore\Bundle\HospitalBundle\Entity;


use Core\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Setting\Bundle\LocationBundle\Entity\Location;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Particular
 *
 * @ORM\Table( name = "hms_particular")
 * @UniqueEntity(fields={"assignOperator"},message="Doctor already existing,Please try again.")
 * @ORM\Entity()
 */
class Particular
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
     * @ORM\ManyToOne(targetEntity="Config", inversedBy="particulars"  , cascade={"detach","merge"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $hospitalConfig;


    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\HospitalBundle\Entity\Service", inversedBy="particulars" )
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $service;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Category", inversedBy="particulars")
     **/
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Particular", inversedBy="particularDepartments")
     **/
    private $department;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="assign_operator_id", referencedColumnName="id", nullable=true, onDelete="cascade")

     **/
    private  $assignOperator;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="marketing_executive_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private  $marketingExecutive;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="reportMachineName", type="string", length=255, nullable=true)
     */
    private $reportMachineName;


    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="smallint", length=3, nullable=true)
     */
    private $quantity = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="oldReportId", type="smallint", length=5, nullable=true)
     */
    private $oldReportId;

    /**
     * @var integer
     *
     * @ORM\Column(name="openingQuantity", type="integer", nullable=true)
     */
    private $openingQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="minQuantity", type="integer", nullable=true)
     */
    private $minQuantity;


    /**
     * @var integer
     *
     * @ORM\Column(name="purchaseQuantity", type="integer", nullable=true)
     */
    private $purchaseQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="salesQuantity", type="integer", nullable=true)
     */
    private $salesQuantity;

    /**
     * @var string
     *
     * @ORM\Column(name="purchaseAverage", type="decimal", nullable=true)
     */
    private $purchaseAverage;

    /**
     * @var string
     *
     * @ORM\Column(name="purchasePrice", type="decimal", nullable=true)
     */
    private $purchasePrice;


     /**
     * @var string
     *
     * @ORM\Column(name="ipdVisitCharge", type="decimal", nullable=true)
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
     * @ORM\Column(name="reportContent", type="text", nullable=true)
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
     * @ORM\Column(name="overHeadCost", type="decimal", nullable=true)
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
     * @ORM\Column(name="minimumPrice", type="decimal", nullable=true)
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
     * @ORM\Column(name="phoneNo", type="string", length=128, nullable=true)
     */
    private $phoneNo;

    /**
     * @var string
     *
     * @ORM\Column(name="startHour", type="string", length=10, nullable=true)
     */
    private $startHour;

    /**
     * @var string
     *
     * @ORM\Column(name="endHour", type="string", length=10, nullable=true)
     */
    private $endHour;

    /**
     * @var array
     *
     * @ORM\Column(name="weeklyOffDay", type="array", nullable=true)
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
     * @ORM\Column(name="educationalDegree", type="string", length=255, nullable=true)
     */
    private $educationalDegree;

    /**
     * @var string
     *
     * @ORM\Column(name="doctorSignature", type="string", length=255, nullable=true)
     */
    private $doctorSignature;

     /**
     * @var string
     *
     * @ORM\Column(name="doctorSignatureBangla", type="string", length=255, nullable=true)
     */
    private $doctorSignatureBangla;

    /**
     * @var string
     *
     * @ORM\Column(name="pathologistSignature", type="string", length=255, nullable=true)
     */
    private $pathologistSignature;

    /**
     * @var string
     *
     * @ORM\Column(name="currentJob", type="string", length=256, nullable=true)
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
     * @ORM\Column(name="visitTime", type="string", length=256, nullable=true)
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

