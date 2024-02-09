<?php

namespace Modules\Domain\App\Entities;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;




/**
 * GlobalOption
 * @ORM\Table(name="dom_global_option")
 * @ORM\Entity(repositoryClass="Modules\Domain\App\Repositories\GlobalOptionRepository")
 */
class GlobalOption
{

    /**
     * @ORM\OneToMany(targetEntity="Modules\Core\App\Entities\User", mappedBy="globalOption" , cascade={"persist", "remove"} )
     **/
    protected $users;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\AppModule", inversedBy="appDomains")
     */
    protected $mainApp;


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
     * @ORM\Column(name="hotline", type="string", length=15, nullable = true )
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
     * @ORM\Column(name="billingAddress", type="string", nullable = true )
     */
    private $billingAddress;

    /**
     * @var float
     *
     * @ORM\Column(name="monthlyAmount", type="float", length=100, nullable = true )
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
     * @ORM\Column(name="description", type="text", length=255  , nullable=true )
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="binNo", type="text", length=255  , nullable=true )
     */
    private $binNo;

    /**
     * @var string
     *
     * @ORM\Column(name="vatPercent", type="text", length=255  , nullable=true )
     */
    private $vatPercent;

    /**
     * @var boolean
     *
     * @ORM\Column(name="vatEnable", type="boolean", nullable=true )
     */
    private $vatEnable;

    /**
     * @var string
     *
     * @ORM\Column(name="printMessage", type="text", length=255  , nullable=true )
     */
    private $printMessage;

    /**
     * @var string
     *
     * @ORM\Column(name="mobileName", type="string", length=50  , nullable=true )
     */
    private $mobileName;


    /**
     * @var string
     *
     * @ORM\Column(name="organizationName", type="string", length=255  , nullable=true )
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
     * @ORM\Column(name="subDomain", type="string", length=255 , unique=true, nullable=true)
     */
    private $subDomain;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isMobile", type="boolean" , nullable=true)
     */
    private $isMobile;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isBranch", type="boolean" , nullable=true)
     */
    private $isBranch = false;

     /**
     * @var boolean
     *
     * @ORM\Column(name="isSidebar", type="boolean" , nullable=true)
     */
    private $isSidebar = false;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\Syndicate", inversedBy="globalOption")
     **/

    private $syndicate;
    /**
     * @ORM\OneToOne(targetEntity="Modules\Domain\App\Entities\SiteSetting", mappedBy="globalOption" , cascade={"persist", "remove"} )
     **/

    private $siteSetting;


    /**
     * @var boolean
     *
     * @ORM\Column(name="customizeDesign", type="boolean" , nullable=true)
     */
    private $customizeDesign;

    /* App store relation for application work this domain */
    /**
     * @var boolean
     *
     * @ORM\Column(name="facebookAds", type="boolean" , nullable=true)
     */
    private $facebookAds;
    /**
     * @var boolean
     *
     * @ORM\Column(name="facebookApps", type="boolean" , nullable=true)
     */
    private $facebookApps;

    /**
     * @var string
     *
     * @ORM\Column(name="instagramPageUrl", type="string", length=255 , nullable=true)
     */
    private $instagramPageUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="facebookPageUrl", type="string", length=255 , nullable=true)
     */
    private $facebookPageUrl;
     /**
     * @var string
     *
     * @ORM\Column(name="twitterUrl", type="string", length=255 , nullable=true)
     */
    private $twitterUrl;
     /**
     * @var string
     *
     * @ORM\Column(name="googlePlus", type="string", length=255 , nullable=true)
     */
    private $googlePlus;

    /**
     * @var string
     *
     * @ORM\Column(name="youtube", type="string", length=255 , nullable=true)
     */
    private $youtube;

    /**
     * @var string
     *
     * @ORM\Column(name="skype", type="string", length=255 , nullable=true)
     */
    private $skype;

    /**
     * @var string
     *
     * @ORM\Column(name="linkedin", type="string", length=255 , nullable=true)
     */
    private $linkedin;

    /**
     * @var array
     *
     * @ORM\Column(name="stockFormat", type="array", length=50 , nullable=true)
     */
    private $stockFormat;

    /**
     * @var boolean
     *
     * @ORM\Column(name="promotion", type="boolean" , nullable=true)
     */
    private $promotion;

    /**
     * @var boolean
     *
     * @ORM\Column(name="googleAds", type="boolean" , nullable=true)
     */
    private $googleAds;

    /**
     * @var boolean
     *
     * @ORM\Column(name="userMode", type="boolean" , nullable=true)
     */
    private $userMode;

    /**
     * @var boolean
     *
     * @ORM\Column(name="customApp", type="boolean" , nullable=true)
     */
    private $customApp;

    /**
     * @var boolean
     *
     * @ORM\Column(name="smsIntegration", type="boolean" , nullable=true)
     */
    private $smsIntegration;
    /**
     * @var boolean
     *
     * @ORM\Column(name="emailIntegration", type="boolean" , nullable=true)
     */
    private $emailIntegration;
    /**
     * @var boolean
     *
     * @ORM\Column(name="isIntro", type="boolean" , nullable=true)
     */
    private $isIntro;

     /**
     * @var boolean
     *
     * @ORM\Column(name="isPortalStore", type="boolean" , nullable=true)
     */
    private $isPortalStore;

    /**
     * @var string
     *
     * @ORM\Column(name="callBackEmail", type="string", length=255 , nullable=true)
     */
    private $callBackEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="domainType", type="string", length=30 , nullable=true)
     */
    private $domainType;


    /**
     * @var text
     *
     * @ORM\Column(name="callBackContent", type="text", nullable=true)
     */
    private $callBackContent;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="status", type="smallint", nullable=true)
	 */
	private $status = 0;


	/*---------------------- Manage Domain Pricing-------------------------------------*/
    /**
     * @var boolean
     *
     * @ORM\Column(name="callBackNotify", type="boolean", nullable=true)
     */
    private $callBackNotify;
    /**
     * @var boolean
     *
     * @ORM\Column(name="primaryNumber", type="boolean", nullable=true)
     */
    private $primaryNumber = true;

    /*---------------------- Manage Education Portal-------------------------------------*/
    /**
     * @var string
     *
     * @ORM\Column(name="leaveEmail", type="string", length=255 , nullable=true)
     */
    private $leaveEmail;

    /*========================= Ecommerce & Payment Method Integration ================================*/
    /**
     * @var string
     *
     * @ORM\Column(name="webMail", type="string", length=255 , nullable=true)
     */
    private $webMail;
    /**
     * @var text
     *
     * @ORM\Column(name="leaveContent", type="text", nullable=true)
     */
    private $leaveContent;


    /* ===================      Fixed Assets      =======================================  */

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
     * @var string
     *
     * @ORM\Column(name="uniqueCode", type="string", length=255, nullable=true)
     */
    private $uniqueCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;



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
     * @return Syndicate
     */
    public function getSyndicate()
    {
        return $this->syndicate;
    }

    /**
     * @param Syndicate $syndicate
     */
    public function setSyndicate($syndicate)
    {
        $this->syndicate = $syndicate;
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
    public function getSiteSetting()
    {
        return $this->siteSetting;
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
     * @return Location
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
     * @return TemplateCustomize
     */
    public function getTemplateCustomize()
    {
        return $this->templateCustomize;
    }

    /**
     * @return HomePage
     */
    public function getHomePage()
    {
        return $this->homePage;
    }

    /**
     * @return ContactPage
     */
    public function getContactPage()
    {
        return $this->contactPage;
    }

    /**
     * @return mixed
     */
    public function getFooterSetting()
    {
        return $this->footerSetting;
    }

    /**
     * @return mixed
     */
    public function getMobileIcon()
    {
        return $this->mobileIcon;
    }

    /**
     * @return string
     */
    public function getCallBackEmail()
    {
        return $this->callBackEmail;
    }

    /**
     * @param string $callBackEmail
     */
    public function setCallBackEmail($callBackEmail)
    {
        $this->callBackEmail = $callBackEmail;
    }

    /**
     * @return text
     */
    public function getCallBackContent()
    {
        return $this->callBackContent;
    }

    /**
     * @param text $callBackContent
     */
    public function setCallBackContent($callBackContent)
    {
        $this->callBackContent = $callBackContent;
    }

    /**
     * @return string
     */
    public function getLeaveEmail()
    {
        return $this->leaveEmail;
    }

    /**
     * @param string $leaveEmail
     */
    public function setLeaveEmail($leaveEmail)
    {
        $this->leaveEmail = $leaveEmail;
    }

    /**
     * @return text
     */
    public function getLeaveContent()
    {
        return $this->leaveContent;
    }

    /**
     * @param text $leaveContent
     */
    public function setLeaveContent($leaveContent)
    {
        $this->leaveContent = $leaveContent;
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

    /**
     * @return DomainUser
     */
    public function getDomainUser()
    {
        return $this->domainUser;
    }

    /**
     * @return mixed
     */
    public function getAdsTool()
    {
        return $this->adsTool;
    }

    /**
     * @return mixed
     */
    public function getItemTypeGrouping()
    {
        return $this->itemTypeGrouping;
    }

    /**
     * @return InventoryConfig
     */
    public function getInventoryConfig()
    {
        return $this->inventoryConfig;
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return mixed
     */
    public function getMenuGrouping()
    {
        return $this->menuGrouping;
    }


    /**
     * @return Page
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @return mixed
     */
    public function getAccountPurchase()
    {
        return $this->accountPurchase;
    }

    /**
     * @return mixed
     */
    public function getPettyCash()
    {
        return $this->pettyCash;
    }

    /**
     * @return HomeSlider
     */
    public function getHomeSliders()
    {
        return $this->homeSliders;
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
     * @return PortalBankAccount
     */
    public function getPortalBankAccount()
    {
        return $this->portalBankAccount;
    }

    /**
     * @return mixed
     */
    public function getInvoiceSmsEmails()
    {
        return $this->invoiceSmsEmails;
    }

    /**
     * @return mixed
     */
    public function getInvoiceModules()
    {
        return $this->invoiceModules;
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
     * @return mixed
     */
    public function getExpenseCategory()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('level', 1));
        return $this->expenseCategory->matching($criteria);
    }

    /**
     * @return Transaction
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @return EcommerceConfig
     */
    public function getEcommerceConfig()
    {
        return $this->ecommerceConfig;
    }


    /**
     * @return PhotoGallery
     */
    public function getPhotoGalleries()
    {
        return $this->photoGalleries;
    }


    /**
     * @return ModuleCategory
     */
    public function getModuleCategories()
    {
        return $this->moduleCategories;
    }


    /**
     * @return PageFile
     */
    public function getPageFiles()
    {
        return $this->pageFiles;
    }

   /**
    * @return AccountCash
    */
   public function getAccountCashes()
   {
    return $this->accountCashes;
   }


 /**
  * @return mixed
  */
 public function getAccountSalesReturn()
 {
  return $this->accountSalesReturn;
 }

 /**
  * @return AccountPurchaseReturn
  */
 public function getAccountPurchaseReturn()
 {
  return $this->accountPurchaseReturn;
 }

 /**
  * @return AccountingConfig
  */
  public function getAccountingConfig()
  {
   return $this->accountingConfig;
  }

    /**
     * @return Branches
     */
    public function getBranches()
    {
        return $this->branches;
    }

    /**
     * @return Order
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @return FeatureWidget
     */
    public function getFeatureWidgets()
    {
        return $this->featureWidgets;
    }

    /**
     * @return Feature
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * @return SmsSender
     */
    public function getSmsSenders()
    {
        return $this->smsSenders;
    }

    /**
     * @return SmsSenderTotal
     */
    public function getSmsSenderTotal()
    {
        return $this->smsSenderTotal;
    }

    /**
     * @return PreOrder
     */
    public function getPreOrders()
    {
        return $this->preOrders;
    }

    /**
     * @return NotificationConfig
     */
    public function getNotificationConfig()
    {
        return $this->notificationConfig;
    }

    /**
     * @return SidebarWidgetPanel
     */
    public function getSidebarWidgetPanels()
    {
        return $this->sidebarWidgetPanels;
    }

    /**
     * @return User
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param User $agent
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
    }

    /**
     * @return Customer
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    /**
     * @return FeatureBrand
     */
    public function getFeatureBrands()
    {
        return $this->featureBrands;
    }

    /**
     * @return FeatureCategory
     */
    public function getFeatureCategories()
    {
        return $this->featureCategories;
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
     * @return HospitalConfig
     */
    public function getHospitalConfig()
    {
        return $this->hospitalConfig;
    }

    /**
     * @return Menu
     */
    public function getMenus()
    {
        return $this->menus;
    }

    /**
     * @return AccountMobileBank
     */
    public function getAccountMobileBank()
    {
        return $this->accountMobileBank;
    }

    /**
     * @return DmsConfig
     */
    public function getDmsConfig()
    {
        return $this->dmsConfig;
    }

    /**
     * @return RestaurantConfig
     */
    public function getRestaurantConfig()
    {
        return $this->restaurantConfig;
    }

    /**
     * @return DailyAttendance
     */
    public function getDailyAttendance()
    {
        return $this->dailyAttendance;
    }



    /**
     * @return BusinessConfig
     */
    public function getBusinessConfig()
    {
        return $this->businessConfig;
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
     * @return DpsConfig
     */
    public function getDpsConfig()
    {
        return $this->dpsConfig;
    }

    /**
     * @return MedicineConfig
     */
    public function getMedicineConfig()
    {
        return $this->medicineConfig;
    }

    /**
     * @return AccountVendor
     */
    public function getVendors()
    {
        return $this->vendors;
    }

    /**
     * @return MedicineBrand
     */
    public function getMedicineBrands()
    {
        return $this->medicineBrands;
    }

	/**
	 * @return HotelConfig
	 */
	public function getHotelConfig() {
		return $this->hotelConfig;
	}

	/**
	 * @return ElectionConfig
	 */
	public function getElectionConfig() {
		return $this->electionConfig;
	}

	/**
	 * @return AccountBalanceTransfer
	 */
	public function getBalanceTransfer() {
		return $this->balanceTransfer;
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
	 * @return EducationConfig
	 */

	public function getEducationConfig() {
		return $this->educationConfig;
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
     * @return AccountSalesAdjustment
     */
    public function getAccountSalesAdjustment()
    {
        return $this->accountSalesAdjustment;
    }

    /**
     * @return AccountPurchaseCommission
     */
    public function getAccountPurchaseCommission()
    {
        return $this->accountPurchaseCommission;
    }

    /**
     * @return AndroidDeviceSetup
     */
    public function getAndroids()
    {
        return $this->androids;
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
     * @return ProcurementConfig
     */
    public function getProcurementConfig()
    {
        return $this->procurementConfig;
    }

    /**
     * @return AssetsConfig
     */
    public function getAssetsConfig()
    {
        return $this->assetsConfig;
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
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
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
     * @return array
     */
    public function getStockFormat()
    {
        return $this->stockFormat;
    }

    /**
     * @param array $stockFormat
     * Category
     * Brand
     * Model
     * Size
     * Color
     * Vendor
     */
    public function setStockFormat($stockFormat)
    {
        $this->stockFormat = $stockFormat;
    }

    /**
     * Sets file.
     *
     * @param GlobalOption $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return GlobalOption
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
        return 'uploads/domain';
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
     * @return mixed
     */
    public function getLinkDomain()
    {
        return $this->linkDomain;
    }

    /**
     * @param mixed $linkDomain
     */
    public function setLinkDomain($linkDomain)
    {
        $this->linkDomain = $linkDomain;
    }

}
