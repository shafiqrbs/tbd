<?php

namespace Modules\Core\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Customer
 * @ORM\Table(name="cor_customers" , indexes={
 *     @ORM\Index(name="allowIndex", columns={"name"}),
 *     @ORM\Index(name="customerIdIndex", columns={"customerId"}),
 *     @ORM\Index(name="mobileIndex", columns={"mobile"}),
 *     @ORM\Index(name="createdIndex", columns={"created_at"}),
 *     @ORM\Index(name="updatedIndex", columns={"updated_at"}),
 *     @ORM\Index(name="statusIndex", columns={"status"})
 * })
 * @ORM\Entity()
 */
class Customer
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
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption", inversedBy="customers")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/

    protected $domain;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Country")
     */
    protected $country;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Location")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    protected $location;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="marketing_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    protected $marketing;


    /**
     * @var integer
     *
     * @ORM\Column(name="user", type="integer",  nullable=true)
     */
    private $user;

     /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;

     /**
     * @var \DateTime
     *
     * @ORM\Column(name="paymentMonth", type="date",  nullable=true)
     */
    private $paymentMonth;

    /**
     * @var string
     *
     * @ORM\Column(name="customerId", type="string",  nullable=true)
     */
    private $customerId;

     /**
     * @var string
     *
     * @ORM\Column(name="reference_id", type="string",  nullable=true)
     */
    private $referenceId;

    /**
     * @var string
     *
     * @ORM\Column(name="patientId", type="string",  nullable=true)
     */
    private $patientId;

    /**
     * @var string
     *
     * @ORM\Column(name="postalCode", type="string", length=30, nullable =true)
     */
    private $postalCode;

     /**
     * @var array
     *
     * @ORM\Column(name="namePrefix", type="array", nullable =true)
     */
    private $namePrefix;

     /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable =true)
     */
    private $name;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @Doctrine\ORM\Mapping\Column(length=255)
     */
    private $slug;


    /**
     * Generated column
     * @ORM\Column(type="string", name="customer_unique_name", insertable=false, updatable=false, nullable=true)
     * MySQL example: full_name char(41) GENERATED ALWAYS AS (concat(domain_id,'-'concat(name,'-',mobile)),
     */
    protected $customerUniqueName;

    /**
     * @var string
     *
     * @ORM\Column(name="nid", type="string", length=100, nullable =true)
     */
    private $nid;

     /**
     * @var string
     *
     * @ORM\Column(name="unique_id", type="string", unique=true, nullable =true)
     */
    private $uniqueId;

    /**
     * @var string
     *
     * @ORM\Column(name="about", type="text", nullable =true)
     */
    private $about;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=100, nullable =true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=100, nullable =true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255, nullable =true)
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=50, nullable =true)
     */
    private $process= 'Pending';

    /**
     * @var string
     *
     * @ORM\Column(name="permanentAddress", type="text",  nullable =true)
     */
    private $permanentAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="fatherName", type="string", length=100, nullable =true)
     */
    private $fatherName;

    /**
     * @var string
     *
     * @ORM\Column(name="motherName", type="string", length=100, nullable =true)
     */
    private $motherName;

     /**
     * @var string
     *
     * @ORM\Column(name="religion", type="string", length=100, nullable =true)
     */
    private $religion ="" ;

    /**
     * @var string
     *
     * @ORM\Column(name="profession", type="string", length=100, nullable =true)
     */
    private $profession;

    /**
     * @var string
     *
     * @ORM\Column(name="nationality", type="string", length=100, nullable =true)
     */
    private $nationality = 'Bangladeshi';

    /**
     * @var string
     *
     * @ORM\Column(name="customerType", type="string", length=15, nullable =true)
     */
    private $customerType;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=50, nullable =true)
     */
    private $mobile;

     /**
     * @var string
     *
     * @ORM\Column(name="alternative_mobile", type="string", length=50, nullable =true)
     */
    private $alternativeMobile;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable =true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="facebookId", type="string", length=100, nullable =true)
     */
    private $facebookId;

    /**
     * @var string
     *
     * @ORM\Column(name="remark", type="text", nullable =true)
     */
    private $remark;

     /**
     * @var string
     *
     * @ORM\Column(name="address", type="text", nullable =true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_group", type="string", length=100, nullable =true)
     */
    private $customerGroup;

    /**
     * @var string
     *
     * @ORM\Column(name="bloodGroup", type="string", length=20, nullable =true)
     */
    private $bloodGroup;

    /**
     * @var Date
     *
     * @ORM\Column(name="dob", type="datetime", nullable=true)
     */
    private $dob;

    /**
     * @var string
     *
     * @ORM\Column(name="ageGroup", type="string",length=10 , nullable = true)
     */
    private $ageGroup;

    /**
     * @var string
     *
     * @ORM\Column(name="maritalStatus", type="string",length=30 , nullable = true)
     */
    private $maritalStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="alternativeContactPerson", type="string",length=200 , nullable = true)
     */
    private $alternativeContactPerson;

    /**
     * @var string
     *
     * @ORM\Column(name="alternativeContactMobile", type="string",length=50 , nullable = true)
     */
    private $alternativeContactMobile;


    /**
     * @var string
     *
     * @ORM\Column(name="alternativeRelation", type="string",length=100 , nullable = true)
     */
    private $alternativeRelation;


    /**
     * @var integer
     *
     * @ORM\Column(name="age", type="smallint",length=3, nullable = true)
     */
    private $age;

    /**
     * @var string
     *
     * @ORM\Column(name="weight", type="string",length=50, nullable = true)
     */
    private $weight;

	/**
     * @var string
     *
     * @ORM\Column(name="ssc", type="string",length=50, nullable = true)
     */
    private $ssc;

	/**
     * @var string
     *
     * @ORM\Column(name="hsc", type="string",length=50, nullable = true)
     */
    private $hsc;

	/**
     * @var string
     *
     * @ORM\Column(name="fatherDesignation", type="string",length=150, nullable = true)
     */
    private $fatherDesignation;

	/**
     * @var string
     *
     * @ORM\Column(name="motherDesignation", type="string",length=150, nullable = true)
     */
    private $motherDesignation;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="bloodPressure", type="string",length=20, nullable = true)
	 */
	private $bloodPressure;

    /**
	 * @var string
	 *
	 * @ORM\Column(name="diabetes", type="string",length=30, nullable = true)
	 */
	private $diabetes;

    /**
	 * @var string
	 *
	 * @ORM\Column(name="height", type="string",length=20, nullable = true)
	 */
	private $height;

    /**
     * @var string
     *
     * @ORM\Column(name="ageType", type="string", length=20, nullable = true)
     */
    private $ageType;


    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=10 , nullable = true)
     */
    private $gender;


    /**
     * @var string
     *
     * @ORM\Column(name="higherEducation", type="string", nullable=true)
     */
    private $higherEducation;

    /**
     * @var string
     *
     * @ORM\Column(name="spouseName", type="string", nullable=true)
     */
    private $spouseName;

    /**
     * @var string
     *
     * @ORM\Column(name="spouseOccupation", type="string", nullable=true)
     */
    private $spouseOccupation;

    /**
     * @var string
     *
     * @ORM\Column(name="spouseDesignation", type="string", nullable=true)
     */
    private $spouseDesignation;

    /**
     * @var string
     *
     * @ORM\Column(name="memberDesignation", type="string", nullable=true)
     */
    private $memberDesignation;


    /**
     * @var string
     *
     * @ORM\Column(name="studentBatch", type="string", nullable=true)
     */
    private $studentBatch;


    /**
     * @var string
     *
     * @ORM\Column(name="batchYear", type="string", nullable=true)
     */
    private $batchYear;


    /**
     * @var float
     *
     * @ORM\Column(name="opening_balance", type="float", nullable=true)
     */
    private $openingBalance;

    /**
     * @var string
     *
     * @ORM\Column(name="additionalPhone", type="string", nullable=true)
     */
    private $additionalPhone;


      /**
     * @var string
     *
     * @ORM\Column(name="unique_key", type="string", nullable=true)
     */
    private $uniqueKey;


    /**
     * @var float
     *
     * @ORM\Column(name="credit_Limit", type="float", nullable=true)
     */
    private $creditLimit;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    private $status = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isNew", type="boolean", nullable=true)
     */
    private $isNew = true;

     /**
     * @var boolean
     *
     * @ORM\Column(name="is_delete", type="boolean", nullable=true)
     */
    private $isDelete = false;

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
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;



}

