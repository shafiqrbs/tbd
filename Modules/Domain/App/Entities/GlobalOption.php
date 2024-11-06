<?php

namespace Modules\Domain\App\Entities;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * GlobalOption
 * @ORM\Table(name="dom_domain")
 * @ORM\Entity()
 */
class GlobalOption
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
     * @ORM\Column(name="mobile", type="string", length=15, nullable = true )
     */
    private $mobile;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15, nullable = true )
     */
    private $hotline;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable = true )
     */
    private $email;

     /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", nullable = true )
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column( type="string", nullable = true )
     */
    private $billingAddress;

    /**
     * @var float
     *
     * @ORM\Column(type="float", length=100, nullable = true )
     */
    private $monthlyAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255  , nullable=true )
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column( type="text", length=255  , nullable=true )
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(type="text", length=255  , nullable=true )
     */
    private $binNo;

    /**
     * @var string
     *
     * @ORM\Column( type="text", length=255  , nullable=true )
     */
    private $vatPercent;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean", nullable=true )
     */
    private $vatEnable;

    /**
     * @var string
     *
     * @ORM\Column( type="text", length=255  , nullable=true )
     */
    private $printMessage;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50  , nullable=true )
     */
    private $mobileName;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255  , nullable=true )
     */
    private $organizationName;


    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=255, unique=true)
     */
    private $slug;
    /**
     * @var string
     *
     * @ORM\Column(name="domain", type="string", length=255 , unique=true , nullable=true)
     */
    private $domain;
    /**
     * @var string
     *
     * @ORM\Column( type="string", length=255 , unique=true, nullable=true)
     */
    private $subDomain;



    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" , nullable=true)
     */
    private $userMode;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" , nullable=true)
     */
    private $customApp;

    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean" , nullable=true)
     */
    private $smsIntegration;
    /**
     * @var boolean
     *
     * @ORM\Column( type="boolean" , nullable=true)
     */
    private $emailIntegration;
    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" , nullable=true)
     */
    private $isIntro;

     /**
     * @var boolean
     *
     * @ORM\Column( type="boolean" , nullable=true)
     */
    private $isPortalStore;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255 , nullable=true)
     */
    private $callBackEmail;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30 , nullable=true)
     */
    private $domainType;


    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $callBackContent;

	/**
	 * @var integer
	 *
	 * @ORM\Column( type="smallint", nullable=true)
	 */
	private $status = 0;


	/*---------------------- Manage Domain Pricing-------------------------------------*/
    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $callBackNotify;
    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $primaryNumber = true;

    /*---------------------- Manage Education Portal-------------------------------------*/
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255 , nullable=true)
     */
    private $leaveEmail;

    /*========================= Ecommerce & Payment Method Integration ================================*/
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255 , nullable=true)
     */
    private $webMail;
    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $leaveContent;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $uniqueCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\Setting")
     * @ORM\Column(name="business_model_id",type="integer", nullable=true)
     */
    protected $businessModelId;

    /**
     * @ORM\Column(name="modules",type="json", nullable=true)
     */
    protected $modules;



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
     * @return string
     */
    public function getUniqueCode()
    {
        return $this->uniqueCode;
    }

    /**
     * @param string $uniqueCode
     */
    public function setUniqueCode($uniqueCode)
    {
        $this->uniqueCode = $uniqueCode;
    }


    /**
     * Get domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set domain
     *
     * @param string $domain
     * @return GlobalOption
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get subDomain
     *
     * @return string
     */
    public function getSubDomain()
    {
        return $this->subDomain;
    }

    /**
     * Set subDomain
     *
     * @param string $subDomain
     * @return GlobalOption
     */
    public function setSubDomain($subDomain)
    {
        $this->subDomain = $subDomain;

        return $this;
    }

    /**
     * Get isMobile
     *
     * @return boolean
     */
    public function getIsMobile()
    {
        return $this->isMobile;
    }

    /**
     * Set isMobile
     *
     * @param boolean $isMobile
     * @return GlobalOption
     */
    public function setIsMobile($isMobile)
    {
        $this->isMobile = $isMobile;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsBranch()
    {
        return $this->isBranch;
    }

    /**
     * @param bool $isBranch
     */
    public function setIsBranch($isBranch)
    {
        $this->isBranch = $isBranch;
    }




    /**
     * Get customizeDesign
     *
     * @return boolean
     */
    public function getCustomizeDesign()
    {
        return $this->customizeDesign;
    }

    /**
     * Set customizeDesign
     *
     * @param boolean $customizeDesign
     * @return GlobalOption
     */
    public function setCustomizeDesign($customizeDesign)
    {
        $this->customizeDesign = $customizeDesign;

        return $this;
    }

    /**
     * Get facebookAds
     *
     * @return boolean
     */
    public function getFacebookAds()
    {
        return $this->facebookAds;
    }

    /**
     * Set facebookAds
     *
     * @param boolean $facebookAds
     * @return GlobalOption
     */
    public function setFacebookAds($facebookAds)
    {
        $this->facebookAds = $facebookAds;

        return $this;
    }

    /**
     * Get facebookApps
     *
     * @return boolean
     */
    public function getFacebookApps()
    {
        return $this->facebookApps;
    }

    /**
     * Set facebookApps
     *
     * @param boolean $facebookApps
     * @return GlobalOption
     */
    public function setFacebookApps($facebookApps)
    {
        $this->facebookApps = $facebookApps;

        return $this;
    }

    /**
     * Get facebookPageUrl
     *
     * @return string
     */
    public function getFacebookPageUrl()
    {
        return $this->facebookPageUrl;
    }

    /**
     * Set facebookPageUrl
     *
     * @param string $facebookPageUrl
     * @return GlobalOption
     */
    public function setFacebookPageUrl($facebookPageUrl)
    {
        $this->facebookPageUrl = $facebookPageUrl;

        return $this;
    }

    /**
     * Get promotion
     *
     * @return boolean
     */
    public function getPromotion()
    {
        return $this->promotion;
    }

    /**
     * Set promotion
     *
     * @param boolean $promotion
     * @return GlobalOption
     */
    public function setPromotion($promotion)
    {
        $this->promotion = $promotion;

        return $this;
    }

    /**
     * @return smallint
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param smallint $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return boolean
     */
    public function getIsIntro()
    {
        return $this->isIntro;
    }

    /**
     * @param boolean $isIntro
     */
    public function setIsIntro($isIntro)
    {
        $this->isIntro = $isIntro;
    }


    /**
     * @return boolean
     */
    public function getGoogleAds()
    {
        return $this->googleAds;
    }

    /**
     * @param boolean $googleAds
     */
    public function setGoogleAds($googleAds)
    {
        $this->googleAds = $googleAds;
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
     * @return string
     */
    public function getTwitterUrl()
    {
        return $this->twitterUrl;
    }

    /**
     * @param string $twitterUrl
     */
    public function setTwitterUrl($twitterUrl)
    {
        $this->twitterUrl = $twitterUrl;
    }

    /**
     * @return string
     */
    public function getGooglePlus()
    {
        return $this->googlePlus;
    }

    /**
     * @param string $googlePlus
     */
    public function setGooglePlus($googlePlus)
    {
        $this->googlePlus = $googlePlus;
    }

    /**
     * @return boolean
     */
    public function isSmsIntegration()
    {
        return $this->smsIntegration;
    }

    /**
     * @param boolean $smsIntegration
     */
    public function setSmsIntegration($smsIntegration)
    {
        $this->smsIntegration = $smsIntegration;
    }

    /**
     * @return boolean
     */
    public function isEmailIntegration()
    {
        return $this->emailIntegration;
    }

    /**
     * @param boolean $emailIntegration
     */
    public function setEmailIntegration($emailIntegration)
    {
        $this->emailIntegration = $emailIntegration;
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
     * @return boolean
     */
    public function isCallBackNotify()
    {
        return $this->callBackNotify;
    }

    /**
     * @param boolean $callBackNotify
     */
    public function setCallBackNotify($callBackNotify)
    {
        $this->callBackNotify = $callBackNotify;
    }

    /**
     * @return boolean
     */
    public function isPrimaryNumber()
    {
        return $this->primaryNumber;
    }

    /**
     * @param boolean $primaryNumber
     */
    public function setPrimaryNumber($primaryNumber)
    {
        $this->primaryNumber = $primaryNumber;
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


    public function getCurrentStatus()
    {
        $status = '';
        if($this->status == 1){
            $status = 'Active';
        }else if($this->status == 2){
            $status = 'Hold';
        }else if($this->status == 3){
            $status = 'Suspended';
        }
        return $status;
    }

    /**
     * @return string
     */
    public function getWebMail()
    {
        return $this->webMail;
    }

    /**
     * @param string $webMail
     */
    public function setWebMail($webMail)
    {
        $this->webMail = $webMail;
    }



    /**
     * @return string
     */
    public function getInstagramPageUrl()
    {
        return $this->instagramPageUrl;
    }

    /**
     * @param string $instagramPageUrl
     */
    public function setInstagramPageUrl($instagramPageUrl)
    {
        $this->instagramPageUrl = $instagramPageUrl;
    }



    /**
     * @return float
     */
    public function getMonthlyAmount()
    {
        return $this->monthlyAmount;
    }

    /**
     * @param float $monthlyAmount
     */
    public function setMonthlyAmount($monthlyAmount)
    {
        $this->monthlyAmount = $monthlyAmount;
    }



	/**
	 * @return string
	 */
	public function getDomainType(){
		return $this->domainType;
	}

	/**
	 * @param string $domainType
	 */
	public function setDomainType($domainType ) {
		$this->domainType = $domainType;
	}

	/**
	 * @return string
	 */
	public function getYoutube(){
		return $this->youtube;
	}

	/**
	 * @param string $youtube
	 */
	public function setYoutube($youtube ) {
		$this->youtube = $youtube;
	}

	/**
	 * @return string
	 */
	public function getSkype(){
		return $this->skype;
	}

	/**
	 * @param string $skype
	 */
	public function setSkype( $skype ) {
		$this->skype = $skype;
	}

	/**
	 * @return string
	 */
	public function getLinkedin(){
		return $this->linkedin;
	}

	/**
	 * @param string $linkedin
	 */
	public function setLinkedin($linkedin ) {
		$this->linkedin = $linkedin;
	}


    /**
     * @return AppModule
     */
    public function getMainApp()
    {
        return $this->mainApp;
    }

    /**
     * @param AppModule $mainApp
     */
    public function setMainApp($mainApp)
    {
        $this->mainApp = $mainApp;
    }



    /**
     * @return string
     */
    public function getOrganizationName()
    {
        return $this->organizationName;
    }

    /**
     * @param string $organizationName
     */
    public function setOrganizationName($organizationName)
    {
        $this->organizationName = $organizationName;
    }



    /**
     * @return string
     */
    public function getMobileName()
    {
        return $this->mobileName;
    }

    /**
     * @param string $mobileName
     */
    public function setMobileName($mobileName)
    {
        $this->mobileName = $mobileName;
    }

    /**
     * @return string
     */
    public function getHotline()
    {
        return $this->hotline;
    }

    /**
     * @param string $hotline
     */
    public function setHotline($hotline)
    {
        $this->hotline = $hotline;
    }

    /**
     * @return bool
     */
    public function isSidebar()
    {
        return $this->isSidebar;
    }

    /**
     * @param bool $isSidebar
     */
    public function setIsSidebar($isSidebar)
    {
        $this->isSidebar = $isSidebar;
    }

    /**
     * @return bool
     */
    public function isPortalStore()
    {
        return $this->isPortalStore;
    }

    /**
    /**
     * @param bool $isPortalStore
     */
    public function setIsPortalStore($isPortalStore)
    {
        $this->isPortalStore = $isPortalStore;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getBinNo()
    {
        return $this->binNo;
    }

    /**
     * @param string $binNo
     */
    public function setBinNo($binNo)
    {
        $this->binNo = $binNo;
    }

    /**
     * @return string
     */
    public function getVatPercent()
    {
        return $this->vatPercent;
    }

    /**
     * @param string $vatPercent
     */
    public function setVatPercent($vatPercent)
    {
        $this->vatPercent = $vatPercent;
    }

    /**
     * @return bool
     */
    public function isVatEnable()
    {
        return $this->vatEnable;
    }

    /**
     * @param bool $vatEnable
     */
    public function setVatEnable($vatEnable)
    {
        $this->vatEnable = $vatEnable;
    }

    /**
     * @return string
     */
    public function getPrintMessage()
    {
        return $this->printMessage;
    }

    /**
     * @param string $printMessage
     */
    public function setPrintMessage($printMessage)
    {
        $this->printMessage = $printMessage;
    }

    /**
     * @return bool
     */
    public function isUserMode()
    {
        return $this->userMode;
    }

    /**
     * @param bool $userMode
     */
    public function setUserMode($userMode)
    {
        $this->userMode = $userMode;
    }

    /**
     * @return string
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param string $billingAddress
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return bool
     */
    public function isCustomApp()
    {
        return $this->customApp;
    }

    /**
     * @param bool $customApp
     */
    public function setCustomApp($customApp)
    {
        $this->customApp = $customApp;
    }


    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param \DateTime $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param \DateTime $updated_at
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
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



}
