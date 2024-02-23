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
 * @ORM\Entity(repositoryClass="Modules\Core\App\Repositories\CustomerRepository")
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

    protected $globalOption;

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
     * MySQL example: full_name char(41) GENERATED ALWAYS AS (concat(global_option_id,'-'concat(name,'-',mobile)),
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

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Customer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     *
     * @return Customer
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    public function getNameMobile()
    {

	    $nameMobile = $this->getMobile().' - '.$this->getName();
    	return $nameMobile;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Customer
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return string
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set bloodGroup
     *
     * @param string $bloodGroup
     *
     * @return Customer
     */
    public function setBloodGroup($bloodGroup)
    {
        $this->bloodGroup = $bloodGroup;

        return $this;
    }

    /**
     * Get bloodGroup
     *
     * @return string
     */
    public function getBloodGroup()
    {
        return $this->bloodGroup;
    }



    /**
     * @return GlobalOption
     */
    public function getGlobalOption()
    {
        return $this->globalOption;
    }

    /**
     * @param GlobalOption $globalOption
     */
    public function setGlobalOption($globalOption)
    {
        $this->globalOption = $globalOption;
    }

    /**
     * @return mixed
     */
    public function getAccountSales()
    {
        return $this->accountSales;
    }

    /**
     * @param mixed $accountSales
     */
    public function setAccountSales($accountSales)
    {
        $this->accountSales = $accountSales;
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
     *
     * offline
     * online
     * wholesale
     * distributor
     * pos
     * representative
     * sms
     * contact
     * email
     * billing
     * studentParent
     * apartment
     * appointment
     * hospital
     */
    public function setCustomerType($customerType)
    {
        $this->customerType = $customerType;
    }

    /**
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param boolean $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return boolean
     */
    public function getIsNew()
    {
        return $this->isNew;
    }

    /**
     * @param boolean $isNew
     */
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;
    }

    /**
     * @return mixed
     */
    public function getAccountSalesReturn()
    {
        return $this->accountSalesReturn;
    }

    /**
     * @return mixed
     */
    public function getServiceSales()
    {
        return $this->serviceSales;
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
     * @return Order
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param Location $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return Sales
     */
    public function getSales()
    {
        return $this->sales;
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
     * @return AccountOnlineOrder
     */
    public function getAccountOnlineOrders()
    {
        return $this->accountOnlineOrders;
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
     * @return Invoice
     */
    public function getHmsInvoices()
    {
        return $this->hmsInvoices;
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
     * @return DmsInvoice
     */
    public function getDmsInvoices()
    {
        return $this->dmsInvoices;
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
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return MedicineSales
     */
    public function getMedicineSales()
    {
        return $this->medicineSales;
    }

    /**
     * @return DpsInvoice
     */
    public function getDpsInvoices()
    {
        return $this->dpsInvoices;
    }

    /**
     * @return BusinessInvoice
     */
    public function getBusinessInvoices()
    {
        return $this->businessInvoices;
    }

	/**
	 * @return string
	 */
	public function getBloodPressure() {
		return $this->bloodPressure;
	}

	/**
	 * @param string $bloodPressure
	 */
	public function setBloodPressure( $bloodPressure ) {
		$this->bloodPressure = $bloodPressure;
	}

	/**
	 * @return string
	 */
	public function getHeight(){
		return $this->height;
	}

	/**
	 * @param string $height
	 */
	public function setHeight( string $height ) {
		$this->height = $height;
	}

	/**
	 * @return string
	 */
	public function getDiabetes(){
		return $this->diabetes;
	}

	/**
	 * @param string $diabetes
	 */
	public function setDiabetes( string $diabetes ) {
		$this->diabetes = $diabetes;
	}

	/**
	 * @return HotelInvoice
	 */
	public function getHotelInvoices() {
		return $this->hotelInvoices;
	}

	/**
	 * @return string
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/**
	 * @param string $firstName
	 */
	public function setFirstName( string $firstName ) {
		$this->firstName = $firstName;
	}

	/**
	 * @return string
	 */
	public function getLastName(){
		return $this->lastName;
	}

	/**
	 * @param string $lastName
	 */
	public function setLastName( string $lastName ) {
		$this->lastName = $lastName;
	}

	/**
	 * @return string
	 */
	public function getPostalCode() {
		return $this->postalCode;
	}

	/**
	 * @param string $postalCode
	 */
	public function setPostalCode( string $postalCode ) {
		$this->postalCode = $postalCode;
	}

	/**
	 * @return string
	 */
	public function getRemark() {
		return $this->remark;
	}

	/**
	 * @param string $remark
	 */
	public function setRemark( string $remark ) {
		$this->remark = $remark;
	}

	/**
	 * @return array
	 */
	public function getNamePrefix() {
		return $this->namePrefix;
	}

	/**
	 * @param array $namePrefix
	 */
	public function setNamePrefix( array $namePrefix ) {
		$this->namePrefix = $namePrefix;
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
     * Sets file.
     *
     * @param Page $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return Page
     */
    public function getFile()
    {
        return $this->file;
    }

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/' . $this->path;
    }



    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'uploads/domain/'.$this->getGlobalOption()->getId().'/customer';
    }

    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
            $this->path = null ;
        }
    }

    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $filename = date('YmdHmi') . "_" . $this->getFile()->getClientOriginalName();
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $filename
        );

        // set the path property to the filename where you've saved the file
        $this->path = $filename ;

        // clean up the file property as you won't need it anymore
        $this->file = null;
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
     * @return User
     */
    public function getApprovedBy()
    {
        return $this->approvedBy;
    }

    /**
     * @param User $approvedBy
     */
    public function setApprovedBy($approvedBy)
    {
        $this->approvedBy = $approvedBy;
    }

    /**
     * @return User
     */
    public function getCheckedBy()
    {
        return $this->checkedBy;
    }

    /**
     * @param User $checkedBy
     */
    public function setCheckedBy($checkedBy)
    {
        $this->checkedBy = $checkedBy;
    }

    /**
     * @return string
     */
    public function getSsc()
    {
        return $this->ssc;
    }

    /**
     * @param string $ssc
     */
    public function setSsc($ssc)
    {
        $this->ssc = $ssc;
    }

    /**
     * @return string
     */
    public function getHsc()
    {
        return $this->hsc;
    }

    /**
     * @param string $hsc
     */
    public function setHsc($hsc)
    {
        $this->hsc = $hsc;
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
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param Country $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
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
     * @return CustomerAddress
     */
    public function getCustomerAddresses()
    {
        return $this->customerAddresses;
    }

    /**
     * @return mixed
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * @param mixed $designation
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;
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
     * @return string
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @param string $customerGroup
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;
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
     * @return string
     */
    public function getOpeningBalance()
    {
        return $this->openingBalance;
    }

    /**
     * @param string $openingBalance
     */
    public function setOpeningBalance($openingBalance)
    {
        $this->openingBalance = $openingBalance;
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


}

