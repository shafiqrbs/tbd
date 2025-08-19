<?php

namespace Modules\Accounting\App\Entities;
use App\Entity\Domain\Vendor;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Terminalbd\GenericBundle\Entity\Particular;


/**
 * AccountHeadDetails
 *
 * @ORM\Table(name="acc_head_details")
 * @ORM\Entity(repositoryClass="Modules\Accounting\App\Repositories\AccountHeadDetailsRepository")
 *
 */
class AccountHeadDetails
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
     * @var AccountHead
     * @ORM\OneToOne(targetEntity="AccountHead" , inversedBy="headDetail", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="account_head_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private $accountHead;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $bankMethod;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $panItNo;

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $ifcCode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $swiftCode;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="0"})
     */
    private $isChequeBook = false;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="0"})
     */
    private $isChequePrint = false;


    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=20, nullable= true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

     /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", nullable=true)
     */
    private $amount = 0;


    /**
	 * @var integer
	 *
	 * @ORM\Column(name="sorting", type="integer", length=10, nullable=true)
	 */
	private $sorting;


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
    public function getLedger()
    {
        return $this->ledger;
    }

    /**
     * @param mixed $ledger
     */
    public function setLedger($ledger)
    {
        $this->ledger = $ledger;
    }

    /**
     * @return string
     */
    public function getBankMethod()
    {
        return $this->bankMethod;
    }

    /**
     * @param string $bankMethod
     */
    public function setBankMethod($bankMethod)
    {
        $this->bankMethod = $bankMethod;
    }

    /**
     * @return string
     */
    public function getPanItNo()
    {
        return $this->panItNo;
    }

    /**
     * @param string $panItNo
     */
    public function setPanItNo($panItNo)
    {
        $this->panItNo = $panItNo;
    }

    /**
     * @return string
     */
    public function getIfcCode()
    {
        return $this->ifcCode;
    }

    /**
     * @param string $ifcCode
     */
    public function setIfcCode($ifcCode)
    {
        $this->ifcCode = $ifcCode;
    }

    /**
     * @return string
     */
    public function getSwiftCode()
    {
        return $this->swiftCode;
    }

    /**
     * @param string $swiftCode
     */
    public function setSwiftCode($swiftCode)
    {
        $this->swiftCode = $swiftCode;
    }

    /**
     * @return bool
     */
    public function isChequeBook()
    {
        return $this->isChequeBook;
    }

    /**
     * @param bool $isChequeBook
     */
    public function setIsChequeBook($isChequeBook)
    {
        $this->isChequeBook = $isChequeBook;
    }

    /**
     * @return bool
     */
    public function isChequePrint()
    {
        return $this->isChequePrint;
    }

    /**
     * @param bool $isChequePrint
     */
    public function setIsChequePrint($isChequePrint)
    {
        $this->isChequePrint = $isChequePrint;
    }

    /**
     * @return AccountHead
     */
    public function getAccountHead()
    {
        return $this->accountHead;
    }

    /**
     * @param AccountHead $accountHead
     */
    public function setAccountHead($accountHead)
    {
        $this->accountHead = $accountHead;
    }





}

