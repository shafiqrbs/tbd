<?php

/*
 * This file is part of the Docudex project.
 *
 * (c) Devnet Limited <http://www.devnetlimited.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Modules\Core\App\Entities;

use Appstore\Bundle\DomainUserBundle\Entity\Branches;
use Appstore\Bundle\EcommerceBundle\Entity\DeliveryLocation;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Setting\Bundle\ToolBundle\Entity\Designation;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**

 * @ORM\Table(name="core_user_profiles")
 * @ORM\Entity(repositoryClass="Modules\Core\App\Repositories\ProfileRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Profile
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="profile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", unique=true, onDelete="CASCADE")
     * })
     */

    protected $user;


    /**
     * @ORM\ManyToOne(targetEntity="Setting")
     **/
    protected $location;


    /**
     * @ORM\ManyToOne(targetEntity="Setting")
     **/
    protected $designation;


     /**
     * @ORM\ManyToOne(targetEntity="Setting")
     **/
    protected $employeeGroup;


    /**
     * @ORM\ManyToOne(targetEntity="Setting")
     * @ORM\JoinColumn(name="department_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     */
    protected $department;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\Bank")
     **/
    private  $bank;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="father_name", type="string", nullable=true)
     */
    private $fatherName;

    /**
     * @var string
     *
     * @ORM\Column(name="mother_name", type="string", nullable=true)
     */
    private $motherName;

    /**
     * @var string
     *
     * @ORM\Column(name="user_group", type="string", length = 30, nullable=true)
     */
    private $userGroup;


    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=15, nullable=true)
     */
    private $mobile;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_no", type="string", length=15, nullable=true)
     */
    private $phoneNo;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_id", type="string", nullable=true)
     */
    private $facebookId;


     /**
     * @var string
     *
     * @ORM\Column(name="profession", type="string", length=100, nullable=true)
     */
    private $profession;

     /**
     * @var text
     *
     * @ORM\Column(name="about", type="text", nullable=true)
     */
    private $about;

    /**
     * @var text
     *
     * @ORM\Column(name="address", type="text", nullable=true)
     */
    private $address;


    /**
     * @var text
     *
     * @ORM\Column(name="permanent_address", type="text", nullable=true)
     */
    private $permanentAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_code", type="string", nullable=true)
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="additional_phone", type="string", nullable=true)
     */
    private $additionalPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="occupation", type="string", nullable=true)
     */
    private $occupation;

    /**
     * @var string
     *
     * @ORM\Column(name="nid", type="string", nullable=true)
     */
    private $nid;


    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", nullable=true)
     */
    private $gender;


    /**
     * @var datetime
     *
     * @ORM\Column(name="dob", type="datetime", nullable=true)
     */
    private $dob;

    /**
     * @var string
     *
     * @ORM\Column(name="blood_group", type="string", nullable=true)
     */
    private $bloodGroup;


     /**
     * @var string
     *
     * @ORM\Column(name="religion_status", type="string", nullable=true)
     */
    private $religionStatus;


    /**
     * @var string
     *
     * @ORM\Column(name="marital_status", type="string", nullable=true)
     */
    private $maritalStatus;


      /**
     * @var string
     *
     * @ORM\Column(name="employee_type", type="string", nullable=true)
     */
    private $employeeType;


     /**
     * @var string
     *
     * @ORM\Column(name="interest", type="string", nullable=true)
     */
    private $interest;

    /**
     * @var string
     *
     * @ORM\Column(name="joining_date", type="datetime", nullable=true)
     */
    private $joiningDate;

    /**
     * @var string
     *
     * @ORM\Column(name="leave_date", type="datetime", nullable=true)
     */
    private $leaveDate;

    /**
     * @var string
     *
     * @ORM\Column(name="account_no", type="string", length=255, nullable = true)
     */
    private $accountNo;

    /**
     * @var string
     *
     * @ORM\Column(name="branch", type="string", length=255, nullable = true)
     */
    private $branch;

    /**
     * @var boolean
     *
     * @ORM\Column(name="terms_condition_accept", type="boolean", nullable=true)
     */
    private $termsConditionAccept = true;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @Assert\File(maxSize="5M")
     */
    public $file;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $signaturePath;

    /**
     * @Assert\File(maxSize="8388608")
     */
    protected $signatureFile;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime" , nullable=true)
     */
    private $updatedAt;

    public $temp;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
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
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param mixed $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
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
    public function getUserGroup()
    {
        return $this->userGroup;
    }

    /**
     * @param string $userGroup
     */
    public function setUserGroup($userGroup)
    {
        $this->userGroup = $userGroup;
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
    public function getPhoneNo()
    {
        return $this->phoneNo;
    }

    /**
     * @param string $phoneNo
     */
    public function setPhoneNo($phoneNo)
    {
        $this->phoneNo = $phoneNo;
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
     * @return text
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * @param text $about
     */
    public function setAbout($about)
    {
        $this->about = $about;
    }

    /**
     * @return text
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param text $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return text
     */
    public function getPermanentAddress()
    {
        return $this->permanentAddress;
    }

    /**
     * @param text $permanentAddress
     */
    public function setPermanentAddress($permanentAddress)
    {
        $this->permanentAddress = $permanentAddress;
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
    public function getOccupation()
    {
        return $this->occupation;
    }

    /**
     * @param string $occupation
     */
    public function setOccupation($occupation)
    {
        $this->occupation = $occupation;
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
     * @return datetime
     */
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * @param datetime $dob
     */
    public function setDob($dob)
    {
        $this->dob = $dob;
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
     * @return string
     */
    public function getReligionStatus()
    {
        return $this->religionStatus;
    }

    /**
     * @param string $religionStatus
     */
    public function setReligionStatus($religionStatus)
    {
        $this->religionStatus = $religionStatus;
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
    public function getEmployeeType()
    {
        return $this->employeeType;
    }

    /**
     * @param string $employeeType
     */
    public function setEmployeeType($employeeType)
    {
        $this->employeeType = $employeeType;
    }

    /**
     * @return string
     */
    public function getInterest()
    {
        return $this->interest;
    }

    /**
     * @param string $interest
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;
    }

    /**
     * @return string
     */
    public function getJoiningDate()
    {
        return $this->joiningDate;
    }

    /**
     * @param string $joiningDate
     */
    public function setJoiningDate($joiningDate)
    {
        $this->joiningDate = $joiningDate;
    }

    /**
     * @return string
     */
    public function getLeaveDate()
    {
        return $this->leaveDate;
    }

    /**
     * @param string $leaveDate
     */
    public function setLeaveDate($leaveDate)
    {
        $this->leaveDate = $leaveDate;
    }

    /**
     * @return mixed
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @param mixed $bank
     */
    public function setBank($bank)
    {
        $this->bank = $bank;
    }

    /**
     * @return string
     */
    public function getAccountNo()
    {
        return $this->accountNo;
    }

    /**
     * @param string $accountNo
     */
    public function setAccountNo($accountNo)
    {
        $this->accountNo = $accountNo;
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @param string $branch
     */
    public function setBranch($branch)
    {
        $this->branch = $branch;
    }

    /**
     * @return bool
     */
    public function isTermsConditionAccept()
    {
        return $this->termsConditionAccept;
    }

    /**
     * @param bool $termsConditionAccept
     */
    public function setTermsConditionAccept($termsConditionAccept)
    {
        $this->termsConditionAccept = $termsConditionAccept;
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
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getSignaturePath()
    {
        return $this->signaturePath;
    }

    /**
     * @param mixed $signaturePath
     */
    public function setSignaturePath($signaturePath)
    {
        $this->signaturePath = $signaturePath;
    }

    /**
     * @return mixed
     */
    public function getSignatureFile()
    {
        return $this->signatureFile;
    }

    /**
     * @param mixed $signatureFile
     */
    public function setSignatureFile($signatureFile)
    {
        $this->signatureFile = $signatureFile;
    }

    /**
     * @return mixed
     */
    public function getEmployeeGroup()
    {
        return $this->employeeGroup;
    }

    /**
     * @param mixed $employeeGroup
     */
    public function setEmployeeGroup($employeeGroup)
    {
        $this->employeeGroup = $employeeGroup;
    }

}
