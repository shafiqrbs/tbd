<?php

namespace Modules\Accounting\App\Entities;
use App\Entity\Domain\Vendor;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Terminalbd\GenericBundle\Entity\Particular;


/**
 * AccountHead
 *
 * @ORM\Table(name="acc_head")
 * @ORM\Entity(repositoryClass="Modules\Accounting\App\Repositories\AccountHeadRepository")
 *
 */
class AccountHead
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
     * @ORM\ManyToOne(targetEntity="Config", inversedBy="children", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="config_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $config;


    /**
     * @ORM\ManyToOne(targetEntity="AccountMasterHead")
     * @ORM\JoinColumn(name="account_master_head_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $accountMasterHead;


    /**
     * @var AccountHead
     * @ORM\ManyToOne(targetEntity="AccountHead", inversedBy="children", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;


    /**
     * @ORM\OneToMany(targetEntity="AccountHead" , mappedBy="parent")
     * @ORM\OrderBy({"name" = "ASC"})
     **/
    private $children;

    /**
     * @ORM\OneToOne(targetEntity="TransactionMode")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $transaction;

     /**
     * @ORM\OneToOne(targetEntity="AccountHeadDetails", mappedBy="accountHead", cascade={"remove"})
     **/
    private $headDetail;

    /**
     * @ORM\OneToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private  $user;

    /**
     * @ORM\OneToOne(targetEntity="Modules\Core\App\Entities\Vendor")
     * @ORM\JoinColumn(name="vendor_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private  $vendor;

     /**
     * @ORM\OneToOne(targetEntity="Modules\Core\App\Entities\Customer")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private  $customer;

    /**
     * @ORM\OneToOne(targetEntity="Modules\Inventory\App\Entities\Category")
     * @ORM\JoinColumn(name="product_group_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private  $productGroup;

    /**
     * @ORM\OneToOne(targetEntity="Modules\Inventory\App\Entities\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private  $category;

    /**
     * @ORM\ManyToOne(targetEntity="Setting")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $motherAccount;

	/**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=20, nullable= true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="display_name", type="string", length=255)
     */
    private $displayName;

     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $openingBalance = 0;

     /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", nullable=true)
     */
    private $amount = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="credit",type="float", nullable=true)
     */
    private $credit = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="debit",type="float", nullable=true)
     */
    private $debit = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $creditLimit = 0;

    /**
     * @var integer
     * @ORM\Column(type="integer", options={"default":0})
     */
    private $creditPeriod=0;


    /**
     * @var integer
     * @ORM\Column(type="integer", options={"default":0})
     */
    private $earnPoint=0;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", options={"default"="false"})
     */
    private $balanceBillByBill= false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isCreditDateCheckVoucherEntry= false;

     /**
     * @var integer
     *
     * @ORM\Column(name="level", type="integer", nullable=true)
     */
    private $level = 3;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $headGroup;

    /**
     * @var string
     * debit|credit
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $mode;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @Doctrine\ORM\Mapping\Column(length=255,nullable=true)
     */
    private $slug;


	/**
	 * @var integer
	 *
	 * @ORM\Column(name="sorting", type="integer", length=10, nullable=true)
	 */
	private $sorting;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_parent",type="boolean" , nullable=true)
     */
    private $isParent = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    private $status = true;



    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" ,nullable=true)
     */
    private $showAmount = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" ,nullable=true)
     */
    private $provideBankDetails = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isPrivate = false;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

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
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }


    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @return mixed
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @param mixed $transaction
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;
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
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return mixed
     */
    public function getProductGroup()
    {
        return $this->productGroup;
    }

    /**
     * @param mixed $productGroup
     */
    public function setProductGroup($productGroup)
    {
        $this->productGroup = $productGroup;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getMotherAccount()
    {
        return $this->motherAccount;
    }

    /**
     * @param mixed $motherAccount
     */
    public function setMotherAccount($motherAccount)
    {
        $this->motherAccount = $motherAccount;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
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
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * @param float $credit
     */
    public function setCredit($credit)
    {
        $this->credit = $credit;
    }

    /**
     * @return float
     */
    public function getDebit()
    {
        return $this->debit;
    }

    /**
     * @param float $debit
     */
    public function setDebit($debit)
    {
        $this->debit = $debit;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getHeadGroup()
    {
        return $this->headGroup;
    }

    /**
     * @param string $headGroup
     */
    public function setHeadGroup($headGroup)
    {
        $this->headGroup = $headGroup;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getToIncrease()
    {
        return $this->toIncrease;
    }

    /**
     * @param string $toIncrease
     */
    public function setToIncrease($toIncrease)
    {
        $this->toIncrease = $toIncrease;
    }

    /**
     * @return int
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * @param int $sorting
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;
    }

    /**
     * @return bool
     */
    public function isParent()
    {
        return $this->isParent;
    }

    /**
     * @param bool $isParent
     */
    public function setIsParent($isParent)
    {
        $this->isParent = $isParent;
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
    public function isShowAmount()
    {
        return $this->showAmount;
    }

    /**
     * @param bool $showAmount
     */
    public function setShowAmount($showAmount)
    {
        $this->showAmount = $showAmount;
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
     * @return mixed
     */
    public function getAccountMasterHead()
    {
        return $this->accountMasterHead;
    }

    /**
     * @param mixed $accountMasterHead
     */
    public function setAccountMasterHead($accountMasterHead)
    {
        $this->accountMasterHead = $accountMasterHead;
    }

    /**
     * @return mixed
     */
    public function getChequeBooks()
    {
        return $this->chequeBooks;
    }

    /**
     * @param mixed $chequeBooks
     */
    public function setChequeBooks($chequeBooks)
    {
        $this->chequeBooks = $chequeBooks;
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
     * @return int
     */
    public function getCreditPeriod()
    {
        return $this->creditPeriod;
    }

    /**
     * @param int $creditPeriod
     */
    public function setCreditPeriod($creditPeriod)
    {
        $this->creditPeriod = $creditPeriod;
    }

    /**
     * @return bool
     */
    public function isBalanceBillByBill()
    {
        return $this->balanceBillByBill;
    }

    /**
     * @param bool $balanceBillByBill
     */
    public function setBalanceBillByBill($balanceBillByBill)
    {
        $this->balanceBillByBill = $balanceBillByBill;
    }

    /**
     * @return bool
     */
    public function isCreditDateCheckVoucherEntry()
    {
        return $this->isCreditDateCheckVoucherEntry;
    }

    /**
     * @param bool $isCreditDateCheckVoucherEntry
     */
    public function setIsCreditDateCheckVoucherEntry($isCreditDateCheckVoucherEntry)
    {
        $this->isCreditDateCheckVoucherEntry = $isCreditDateCheckVoucherEntry;
    }

    /**
     * @return bool
     */
    public function isProvideBankDetails()
    {
        return $this->provideBankDetails;
    }

    /**
     * @param bool $provideBankDetails
     */
    public function setProvideBankDetails($provideBankDetails)
    {
        $this->provideBankDetails = $provideBankDetails;
    }

    /**
     * @return mixed
     */
    public function getHeadDetail()
    {
        return $this->headDetail;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return int
     */
    public function getEarnPoint()
    {
        return $this->earnPoint;
    }

    /**
     * @param int $earnPoint
     */
    public function setEarnPoint($earnPoint)
    {
        $this->earnPoint = $earnPoint;
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return $this->isPrivate;
    }

    /**
     * @param bool $isPrivate
     */
    public function setIsPrivate($isPrivate)
    {
        $this->isPrivate = $isPrivate;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    /**
     * @return AccountHead
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param AccountHead $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }







}

