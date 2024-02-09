<?php

namespace Modules\Core\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;


/**
 * @ORM\Table(name="core_user")
 * @ORM\Entity(repositoryClass="Module\Core\App\Repositories\UserRepository")
 */
class User
{


	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $username;

	protected $role;

	protected $enabled = true;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="isDelete", type="boolean", nullable=true)
	 */
	private $isDelete = 0;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="domainOwner", type="smallint", nullable=true)
	 */
	private $domainOwner = 0;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="userGroup", type="string", length = 30, nullable=true)
	 */
	private $userGroup = "user";

	/**
	 * @var string
	 *
	 * @ORM\Column(name="appPassword", type="string", length = 30, nullable=true)
	 */
	private $appPassword = "@123456";

	/**
	 * @var array
	 *
	 * @ORM\Column(name="appRoles", type="array", nullable=true)
	 */
	private $appRoles;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="agent", type="boolean", nullable=true)
	 */
	private $agent = false;


	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $avatar;

	/**
	 * @ORM\ManyToMany(targetEntity="Group", inversedBy="users")
	 * @ORM\JoinTable(name="user_user_group",
	 *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
	 * )
	 */
	protected $groups;


	/**
     * @var GlobalOption
	 * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption", inversedBy="users" )
	 *  * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="globalOption_id", referencedColumnName="id")
	 * })
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 **/

	protected $globalOption;



	public function isGranted($role)
	{
		$domain = $this->getRole();
		if('ROLE_SUPER_ADMIN' === $domain or 'ROLE_DOMAIN' === $domain) {
			return true;
		}elseif(in_array($role, $this->getRoles())){
			return true;
		}
		return false;
	}

    public function hasRoles($role)
    {
        $array = array_intersect($role, $this->getRoles());
        if(!empty($array)){
            return true;
        }
        return false;
    }

	/**
	 * Set username;
	 *
	 * @param string $username
	 * @return User
	 */
	public function setUsername($username)
	{
		$this->username = $username;

		return $this;
	}

	/**
	 * Get username
	 *
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	public function getUserFullName(){
        if($this->profile){
            return $this->profile->getName();
        }
        return false;
	}

	public function userDoctor(){

		if(!empty($this->profile->getDesignation())){
			$designation = $this->profile->getDesignation()->getName();
		}else{
			$designation ='';
		}

		return $this->profile->getName().' ('.$designation.')';
	}

    public function userMarketingExecutive(){

        if(!empty($this->profile->getDesignation())){
            $designation = $this->profile->getDesignation()->getName();
        }else{
            $designation ='';
        }
        return $this->profile->getName().' ('.$designation.')';
    }

	public function toArray($collection)
	{
		$this->setRoles($collection->toArray());
	}

	public function setRole($role)
	{
		$this->getRoles();
		$this->addRole($role);

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRole()
	{
		$role = $this->getRoles();
		return $role[0];

	}


	/**
	 * @param Profile $profile
	 */
	public function setProfile($profile)
	{
		$profile->setUser($this);
		$this->profile = $profile;
	}

	/**
	 * @return Profile
	 */
	public function getProfile()
	{
		return $this->profile;
	}

	/**
	 * get avatar image file name
	 *
	 * @return string
	 */
	public function getAvatar()
	{
		return $this->avatar;
	}

	/**
	 * set avatar image file name
	 */
	public function setAvatar($avatar)
	{
		$this->avatar = $avatar;
	}

	public function isSuperAdmin()
	{
		$groups = $this->getGroups();
		foreach ($groups as $group) {
			if ($group->hasRole('ROLE_SUPER_ADMIN')) {
				return true;
			}
		}
		return false;
	}

	public function isRoleAdmin()
	{
		$groups = $this->getGroups();
		foreach ($groups as $group) {
			if ($group->hasRole('ROLE_ADMIN')) {
				return true;
			}
		}
		return false;
	}



	/**
	 * @return mixed
	 */
	public function getPages()
	{
		return $this->pages;
	}


	/**
	 * @param mixed $siteSetting
	 */
	public function setSiteSetting($siteSetting)
	{
		$siteSetting->setUser($this);
		$this->siteSetting = $siteSetting;
	}

	/**
	 * @return mixed
	 */
	public function getSiteSetting()
	{
		return $this->siteSetting;
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
	public function getHomePage()
	{
		return $this->homePage;
	}

	/**
	 * @return mixed
	 */
	public function getContactPage()
	{
		return $this->contactPage;
	}

	/**
	 * @return mixed
	 */
	public function getSyndicateContents()
	{
		return $this->syndicateContents;
	}


	/**
	 * @return mixed
	 */
	public function getProducts()
	{
		return $this->products;
	}

	/**
	 * @return mixed
	 */
	public function getVendor()
	{
		return $this->vendor;
	}

	/**
	 * @param mixed $vendor
	 */
	public function setVendor($vendor)
	{
		$this->vendor = $vendor;
	}

	/**
	 * @return mixed
	 */
	public function getCategoryGrouping()
	{
		return $this->categoryGrouping;
	}

	/**
	 * @return mixed
	 */
	public function getHomeSliders()
	{
		return $this->homeSliders;
	}


	/**
	 * @return mixed
	 */
	public function getSalesUser()
	{
		return $this->salesUser;
	}

	/**
	 * @return mixed
	 */
	public function getSales()
	{
		return $this->sales;
	}

	/**
	 * @return mixed
	 */
	public function getPurchaseReturn()
	{
		return $this->purchaseReturn;
	}

	/**
	 * @return mixed
	 */
	public function getPurchasesReturnApprovedBy()
	{
		return $this->purchasesReturnApprovedBy;
	}


	/**
	 * @return boolean
	 */
	public function getIsDelete()
	{
		return $this->isDelete;
	}

	/**
	 * @param boolean $isDelete
	 */
	public function setIsDelete($isDelete)
	{
		$this->isDelete = $isDelete;
	}

	/**
	 * @return mixed
	 */
	public function getSalesReturn()
	{
		return $this->salesReturn;
	}



	/**
	 * @return mixed
	 */
	public function getExpenditure()
	{
		return $this->expenditure;
	}

	/**
	 * @return mixed
	 */
	public function getExpenditureToUser()
	{
		return $this->expenditureToUser;
	}

	/**
	 * @return mixed
	 */
	public function getExpenditureApprove()
	{
		return $this->expenditureApprove;
	}

	/**
	 * @return mixed
	 */
	public function getPaymentSalaries()
	{
		return $this->paymentSalaries;
	}

	/**
	 * @return mixed
	 */
	public function getSalesApprovedBy()
	{
		return $this->salesApprovedBy;
	}

	/**
	 * @return mixed
	 */
	public function getInvoiceSmsEmail()
	{
		return $this->invoiceSmsEmail;
	}

	/**
	 * @return mixed
	 */
	public function getInvoiceSmsEmailReceivedBy()
	{
		return $this->invoiceSmsEmailReceivedBy;
	}

	/**
	 * @return mixed
	 */
	public function getSalesImport()
	{
		return $this->salesImport;
	}

	/**
	 * @return StockItem
	 */
	public function getStockItems()
	{
		return $this->stockItems;
	}


	public function getCheckRoleGlobal($existRole = NULL)
	{
		$result = array_intersect($existRole, $this->getRoles());
		if(empty($result)){
			return false;
		}else{
			return true;
		}

	}


    public function getCheckExistRole($existRole = NULL)
    {
        $result = in_array($existRole, $this->getRoles());
        if(empty($result)){
            return false;
        }else{
            return true;
        }

    }

	/**
	 * @return Damage
	 */
	public function getDamageApprovedBy()
	{
		return $this->damageApprovedBy;
	}

	/**
	 * @return Damage
	 */
	public function getDamage()
	{
		return $this->damage;
	}


	/**
	 * @return Branches
	 */
	public function getBranches()
	{
		return $this->branches;
	}

	/**
	 * @return BranchInvoice
	 */
	public function getBranchInvoice()
	{
		return $this->branchInvoice;
	}

	/**
	 * @return BranchInvoice
	 */
	public function getBranchInvoiceApprovedBy()
	{
		return $this->branchInvoiceApprovedBy;
	}

	/**
	 * @return ExcelImporter
	 */
	public function getExcelImporters()
	{
		return $this->excelImporters;
	}

	/**
	 * @return Delivery
	 */
	public function getDelivery()
	{
		return $this->delivery;
	}

	/**
	 * @return Delivery
	 */
	public function getDeliveryApprovedBy()
	{
		return $this->deliveryApprovedBy;
	}

	/**
	 * @return DeliveryReturn
	 */
	public function getDeliveryReturn()
	{
		return $this->deliveryReturn;
	}

	/**
	 * @return DeliveryReturn
	 */
	public function getDeliveryReturnApprovedBy()
	{
		return $this->deliveryReturnApprovedBy;
	}

	/**
	 * @return GlobalOption
	 */
	public function getGlobalOptionAgents()
	{
		return $this->globalOptionAgents;
	}

	/**
	 * @return mixed
	 */
	public function getAgent()
	{
		return $this->agent;
	}

	/**
	 * @param mixed $agent
	 */
	public function setAgent($agent)
	{
		$this->agent = $agent;
	}





	/**
	 * @return MedicineReverse
	 */
	public function getMedicineReverse()
	{
		return $this->medicineReverse;
	}

	/**
	 * @return DpsParticular
	 */
	public function getDpsParticularOperator()
	{
		return $this->dpsParticularOperator;
	}

	/**
	 * @return MedicinePurchase
	 */
	public function getMedicinePurchasesBy()
	{
		return $this->medicinePurchasesBy;
	}

	/**
	 * @return MedicineSalesTemporary
	 */
	public function getMedicineSalesTemporary()
	{
		return $this->medicineSalesTemporary;
	}

	/**
	 * @return int
	 */
	public function getDomainOwner()
	{
		return $this->domainOwner;
	}

	/**
	 * @param int $domainOwner
	 */
	public function setDomainOwner($domainOwner)
	{
		$this->domainOwner = $domainOwner;
	}

	/**
	 * @return DomainUser
	 */
	public function getDomainUser()
	{
		return $this->domainUser;
	}



	/**
	 * @return bool
	 */
	public function isEnabled(){
		return $this->enabled;
	}



	/**
	 * @return AccountCash
	 */
	public function getAccountCashes() {
		return $this->accountCashes;
	}



    /**
     * @return RestaurantTemporary
     */
    public function getRestaurantTemps()
    {
        return $this->restaurantTemps;
    }

    /**
     * @return AccountSalesAdjustment
     */
    public function getSalesAdjustment()
    {
        return $this->salesAdjustment;
    }

    /**
     * @return AccountSalesAdjustment
     */
    public function getSalesAdjustmentApprove()
    {
        return $this->salesAdjustmentApprove;
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
     * @return AccountHead
     */
    public function getAccountHead()
    {
        return $this->accountHead;
    }


    /**
     * @return EmployeePayroll
     */
    public function getPayrollApproved()
    {
        return $this->payrollApproved;
    }

    /**
     * @param  EmployeePayroll $employeePayroll
     */
    public function setEmployeePayroll($employeePayroll)
    {
        $employeePayroll->setEmployee($this);
        $this->employeePayroll = $employeePayroll;
    }

     /**
     * @return EmployeePayroll
     */
    public function getEmployeePayroll()
    {
        return $this->employeePayroll;
    }

    /**
     * @return array
     */
    public function getAppRoles()
    {
        return $this->appRoles;
    }

    /**
     * @param array $appRoles
     */
    public function setAppRoles($appRoles)
    {
        $this->appRoles = $appRoles;
    }

    /**
     * @return string
     */
    public function getAppPassword()
    {
        return $this->appPassword;
    }

    /**
     * @param string $appPassword
     */
    public function setAppPassword($appPassword)
    {
        $this->appPassword = $appPassword;
    }

    /**
     * @return BusinessAndroidProcess
     */
    public function getBusinessAndroidProcess()
    {
        return $this->businessAndroidProcess;
    }

    /**
     * @return mixed
     */
    public function getHmsInvoiceTemporaryParticulars()
    {
        return $this->hmsInvoiceTemporaryParticulars;
    }





}
