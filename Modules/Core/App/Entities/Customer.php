<?php

namespace Modules\Core\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Customer
 * @ORM\Table(name="cor_customers" , indexes={
 *     @ORM\Index(name="allowIndex", columns={"name"}),
 *     @ORM\Index(name="customerIdIndex", columns={"customer_id"}),
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
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected $domain;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected $subDomain;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $country;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Setting")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    protected $location;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Setting")
     * @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    protected $customerGroup;

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
     * @ORM\Column(type="date",  nullable=true)
     */
    private $paymentMonth;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_id", type="string",  nullable=true)
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
     * @ORM\Column( type="string",  nullable=true)
     */
    private $patientId;

    /**
     * @var string
     *
     * @ORM\Column( type="string", length=30, nullable =true)
     */
    private $postalCode;

     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable =true)
     */
    private $namePrefix;

     /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable =true)
     */
    private $name;

     /**
     * @var string
     *
     * @ORM\Column(name="proprietor_name", type="string", length=100, nullable =true)
     */
    private $proprietorName;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @Doctrine\ORM\Mapping\Column(length=255,nullable=true)
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
     * @ORM\Column(type="string", length=100, nullable =true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable =true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255, nullable =true)
     */
    private $company;

    /**
     * @ORM\OneToOne(targetEntity="Modules\Core\App\Entities\Vendor", mappedBy="customer")
     */
    protected $vendor;

    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=50, nullable =true)
     */
    private $process= 'Pending';

    /**
     * @var string
     *
     * @ORM\Column(type="text",  nullable =true)
     */
    private $permanentAddress;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable =true)
     */
    private $fatherName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable =true)
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
     * @ORM\Column(type="string", length=15, nullable =true)
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
     * @ORM\Column( type="string", length=100, nullable =true)
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
     * B2B,Corporate,Official
     * @ORM\Column(type="string", length=20, nullable =true)
     */
     private $customerMode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable =true)
     */
    private $bloodGroup;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dob", type="datetime", nullable=true)
     */
    private $dob;

    /**
     * @var string
     *
     * @ORM\Column(type="string",length=10 , nullable = true)
     */
    private $ageGroup;


    /**
     * @var string
     *
     * @ORM\Column( type="string",length=30 , nullable = true)
     */
    private $maritalStatus;

    /**
     * @var string
     *
     * @ORM\Column(type="string",length=200 , nullable = true)
     */
    private $alternativeContactPerson;

    /**
     * @var string
     *
     * @ORM\Column(type="string",length=50 , nullable = true)
     */
    private $alternativeContactMobile;


    /**
     * @var string
     *
     * @ORM\Column(type="string",length=100 , nullable = true)
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
     * @ORM\Column( type="string",length=150, nullable = true)
     */
    private $fatherDesignation;

	/**
     * @var string
     *
     * @ORM\Column( type="string",length=150, nullable = true)
     */
    private $motherDesignation;

	/**
	 * @var string
	 *
	 * @ORM\Column( type="string",length=20, nullable = true)
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
     * @ORM\Column( type="string", length=20, nullable = true)
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
     * @ORM\Column( type="string", nullable=true)
     */
    private $higherEducation;

    /**
     * @var string
     *
     * @ORM\Column( type="string", nullable=true)
     */
    private $spouseName;

    /**
     * @var string
     *
     * @ORM\Column( type="string", nullable=true)
     */
    private $spouseOccupation;

    /**
     * @var string
     *
     * @ORM\Column( type="string", nullable=true)
     */
    private $spouseDesignation;

    /**
     * @var string
     *
     * @ORM\Column( type="string", nullable=true)
     */
    private $memberDesignation;


    /**
     * @var float
     *
     * @ORM\Column(name="opening_balance", type="float", nullable=true)
     */
    private $openingBalance;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $balance;


    /**
     * @var string
     *
     * @ORM\Column( type="string", nullable=true)
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
     * @ORM\Column(name="credit_limit", type="float", nullable=true)
     */
    private $creditLimit;

    /**
     * @var float
     *
     * @ORM\Column(name="discount_percent", type="float",nullable=true)
     */
    private $discountPercent;

    /**
     * @var float
     *
     * @ORM\Column(name="bonus_percent", type="float",nullable=true)
     */
    private $bonusPercent;

    /**
     * @var float
     *
     * @ORM\Column(name="monthly_target_amount", type="float",nullable=true)
     */
    private $monthlyTargetAmount;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    private $status = true;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean", nullable=true)
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

    /**
     * @var boolean
     * @ORM\Column(type="boolean",options={"default"="0"})
     */
    private $isDefaultCustomer;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param mixed $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return mixed
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @param mixed $customerGroup
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;
    }

    /**
     * @return mixed
     */
    public function getMarketing()
    {
        return $this->marketing;
    }

    /**
     * @param mixed $marketing
     */
    public function setMarketing($marketing)
    {
        $this->marketing = $marketing;
    }

    /**
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return \DateTime
     */
    public function getPaymentMonth()
    {
        return $this->paymentMonth;
    }

    /**
     * @param \DateTime $paymentMonth
     */
    public function setPaymentMonth($paymentMonth)
    {
        $this->paymentMonth = $paymentMonth;
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return string
     */
    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * @param string $referenceId
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;
    }

    /**
     * @return string
     */
    public function getPatientId()
    {
        return $this->patientId;
    }

    /**
     * @param string $patientId
     */
    public function setPatientId($patientId)
    {
        $this->patientId = $patientId;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string
     */
    public function getNamePrefix()
    {
        return $this->namePrefix;
    }

    /**
     * @param string $namePrefix
     */
    public function setNamePrefix($namePrefix)
    {
        $this->namePrefix = $namePrefix;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return mixed
     */
    public function getCustomerUniqueName()
    {
        return $this->customerUniqueName;
    }

    /**
     * @param mixed $customerUniqueName
     */
    public function setCustomerUniqueName($customerUniqueName)
    {
        $this->customerUniqueName = $customerUniqueName;
    }

    /**
     * @return string
     */
    public function getNid()
    {
        return $this->nid;
    }

    /**
     * @param string $nid
     */
    public function setNid($nid)
    {
        $this->nid = $nid;
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @param string $uniqueId
     */
    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;
    }

    /**
     * @return string
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * @param string $about
     */
    public function setAbout($about)
    {
        $this->about = $about;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return string
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param string $process
     */
    public function setProcess($process)
    {
        $this->process = $process;
    }

    /**
     * @return string
     */
    public function getPermanentAddress()
    {
        return $this->permanentAddress;
    }

    /**
     * @param string $permanentAddress
     */
    public function setPermanentAddress($permanentAddress)
    {
        $this->permanentAddress = $permanentAddress;
    }

    /**
     * @return string
     */
    public function getFatherName()
    {
        return $this->fatherName;
    }

    /**
     * @param string $fatherName
     */
    public function setFatherName($fatherName)
    {
        $this->fatherName = $fatherName;
    }

    /**
     * @return string
     */
    public function getMotherName()
    {
        return $this->motherName;
    }

    /**
     * @param string $motherName
     */
    public function setMotherName($motherName)
    {
        $this->motherName = $motherName;
    }

    /**
     * @return string
     */
    public function getReligion()
    {
        return $this->religion;
    }

    /**
     * @param string $religion
     */
    public function setReligion($religion)
    {
        $this->religion = $religion;
    }

    /**
     * @return string
     */
    public function getProfession()
    {
        return $this->profession;
    }

    /**
     * @param string $profession
     */
    public function setProfession($profession)
    {
        $this->profession = $profession;
    }

    /**
     * @return string
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * @param string $nationality
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
    }

    /**
     * @return string
     */
    public function getCustomerType()
    {
        return $this->customerType;
    }

    /**
     * @param string $customerType
     */
    public function setCustomerType($customerType)
    {
        $this->customerType = $customerType;
    }

    /**
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param string $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    /**
     * @return string
     */
    public function getAlternativeMobile()
    {
        return $this->alternativeMobile;
    }

    /**
     * @param string $alternativeMobile
     */
    public function setAlternativeMobile($alternativeMobile)
    {
        $this->alternativeMobile = $alternativeMobile;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * @param string $facebookId
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
    }

    /**
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param string $remark
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getBloodGroup()
    {
        return $this->bloodGroup;
    }

    /**
     * @param string $bloodGroup
     */
    public function setBloodGroup($bloodGroup)
    {
        $this->bloodGroup = $bloodGroup;
    }

    /**
     * @return Date
     */
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * @param Date $dob
     */
    public function setDob($dob)
    {
        $this->dob = $dob;
    }

    /**
     * @return string
     */
    public function getAgeGroup()
    {
        return $this->ageGroup;
    }

    /**
     * @param string $ageGroup
     */
    public function setAgeGroup($ageGroup)
    {
        $this->ageGroup = $ageGroup;
    }

    /**
     * @return string
     */
    public function getMaritalStatus()
    {
        return $this->maritalStatus;
    }

    /**
     * @param string $maritalStatus
     */
    public function setMaritalStatus($maritalStatus)
    {
        $this->maritalStatus = $maritalStatus;
    }

    /**
     * @return string
     */
    public function getAlternativeContactPerson()
    {
        return $this->alternativeContactPerson;
    }

    /**
     * @param string $alternativeContactPerson
     */
    public function setAlternativeContactPerson($alternativeContactPerson)
    {
        $this->alternativeContactPerson = $alternativeContactPerson;
    }

    /**
     * @return string
     */
    public function getAlternativeContactMobile()
    {
        return $this->alternativeContactMobile;
    }

    /**
     * @param string $alternativeContactMobile
     */
    public function setAlternativeContactMobile($alternativeContactMobile)
    {
        $this->alternativeContactMobile = $alternativeContactMobile;
    }

    /**
     * @return string
     */
    public function getAlternativeRelation()
    {
        return $this->alternativeRelation;
    }

    /**
     * @param string $alternativeRelation
     */
    public function setAlternativeRelation($alternativeRelation)
    {
        $this->alternativeRelation = $alternativeRelation;
    }

    /**
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param int $age
     */
    public function setAge($age)
    {
        $this->age = $age;
    }

    /**
     * @return string
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param string $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return string
     */
    public function getFatherDesignation()
    {
        return $this->fatherDesignation;
    }

    /**
     * @param string $fatherDesignation
     */
    public function setFatherDesignation($fatherDesignation)
    {
        $this->fatherDesignation = $fatherDesignation;
    }

    /**
     * @return string
     */
    public function getMotherDesignation()
    {
        return $this->motherDesignation;
    }

    /**
     * @param string $motherDesignation
     */
    public function setMotherDesignation($motherDesignation)
    {
        $this->motherDesignation = $motherDesignation;
    }

    /**
     * @return string
     */
    public function getBloodPressure()
    {
        return $this->bloodPressure;
    }

    /**
     * @param string $bloodPressure
     */
    public function setBloodPressure($bloodPressure)
    {
        $this->bloodPressure = $bloodPressure;
    }

    /**
     * @return string
     */
    public function getDiabetes()
    {
        return $this->diabetes;
    }

    /**
     * @param string $diabetes
     */
    public function setDiabetes($diabetes)
    {
        $this->diabetes = $diabetes;
    }

    /**
     * @return string
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param string $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return string
     */
    public function getAgeType()
    {
        return $this->ageType;
    }

    /**
     * @param string $ageType
     */
    public function setAgeType($ageType)
    {
        $this->ageType = $ageType;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getHigherEducation()
    {
        return $this->higherEducation;
    }

    /**
     * @param string $higherEducation
     */
    public function setHigherEducation($higherEducation)
    {
        $this->higherEducation = $higherEducation;
    }

    /**
     * @return string
     */
    public function getSpouseName()
    {
        return $this->spouseName;
    }

    /**
     * @param string $spouseName
     */
    public function setSpouseName($spouseName)
    {
        $this->spouseName = $spouseName;
    }

    /**
     * @return string
     */
    public function getSpouseOccupation()
    {
        return $this->spouseOccupation;
    }

    /**
     * @param string $spouseOccupation
     */
    public function setSpouseOccupation($spouseOccupation)
    {
        $this->spouseOccupation = $spouseOccupation;
    }

    /**
     * @return string
     */
    public function getSpouseDesignation()
    {
        return $this->spouseDesignation;
    }

    /**
     * @param string $spouseDesignation
     */
    public function setSpouseDesignation($spouseDesignation)
    {
        $this->spouseDesignation = $spouseDesignation;
    }

    /**
     * @return string
     */
    public function getMemberDesignation()
    {
        return $this->memberDesignation;
    }

    /**
     * @param string $memberDesignation
     */
    public function setMemberDesignation($memberDesignation)
    {
        $this->memberDesignation = $memberDesignation;
    }

    /**
     * @return string
     */
    public function getStudentBatch()
    {
        return $this->studentBatch;
    }

    /**
     * @param string $studentBatch
     */
    public function setStudentBatch($studentBatch)
    {
        $this->studentBatch = $studentBatch;
    }

    /**
     * @return string
     */
    public function getBatchYear()
    {
        return $this->batchYear;
    }

    /**
     * @param string $batchYear
     */
    public function setBatchYear($batchYear)
    {
        $this->batchYear = $batchYear;
    }

    /**
     * @return float
     */
    public function getOpeningBalance()
    {
        return $this->openingBalance;
    }

    /**
     * @param float $openingBalance
     */
    public function setOpeningBalance($openingBalance)
    {
        $this->openingBalance = $openingBalance;
    }

    /**
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param float $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }

    /**
     * @return string
     */
    public function getAdditionalPhone()
    {
        return $this->additionalPhone;
    }

    /**
     * @param string $additionalPhone
     */
    public function setAdditionalPhone($additionalPhone)
    {
        $this->additionalPhone = $additionalPhone;
    }

    /**
     * @return string
     */
    public function getUniqueKey()
    {
        return $this->uniqueKey;
    }

    /**
     * @param string $uniqueKey
     */
    public function setUniqueKey($uniqueKey)
    {
        $this->uniqueKey = $uniqueKey;
    }

    /**
     * @return float
     */
    public function getCreditLimit()
    {
        return $this->creditLimit;
    }

    /**
     * @param float $creditLimit
     */
    public function setCreditLimit($creditLimit)
    {
        $this->creditLimit = $creditLimit;
    }

    /**
     * @return bool
     */
    public function isStatus()
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * @param bool $isNew
     */
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        return $this->isDelete;
    }

    /**
     * @param bool $isDelete
     */
    public function setIsDelete($isDelete)
    {
        $this->isDelete = $isDelete;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return bool
     */
    public function isDefaultCustomer()
    {
        return $this->isDefaultCustomer;
    }

    /**
     * @param bool $isDefaultCustomer
     */
    public function setIsDefaultCustomer($isDefaultCustomer)
    {
        $this->isDefaultCustomer = $isDefaultCustomer;
    }

    /**
     * @return mixed
     */
    public function getSubDomain()
    {
        return $this->subDomain;
    }

    /**
     * @param mixed $subDomain
     */
    public function setSubDomain($subDomain)
    {
        $this->subDomain = $subDomain;
    }

    /**
     * @return float
     */
    public function getDiscountPercent()
    {
        return $this->discountPercent;
    }

    /**
     * @param float $discountPercent
     */
    public function setDiscountPercent($discountPercent)
    {
        $this->discountPercent = $discountPercent;
    }

    /**
     * @return float
     */
    public function getBonusPercent()
    {
        return $this->bonusPercent;
    }

    /**
     * @param float $bonusPercent
     */
    public function setBonusPercent($bonusPercent)
    {
        $this->bonusPercent = $bonusPercent;
    }

    /**
     * @return float
     */
    public function getMonthlyTargetAmount()
    {
        return $this->monthlyTargetAmount;
    }

    /**
     * @param float $monthlyTargetAmount
     */
    public function setMonthlyTargetAmount($monthlyTargetAmount)
    {
        $this->monthlyTargetAmount = $monthlyTargetAmount;
    }

}

