<?php

namespace Modules\Accounting\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AccountBank
 *
 * @ORM\Table(name="acc_voucher")
 * @ORM\Entity(repositoryClass="Modules\Accounting\App\Repositories\AccountVoucherRepository")
 *
 */
class AccountVoucher
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
     * @ORM\ManyToOne(targetEntity="Config", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;

    /**
     * @ORM\ManyToOne(targetEntity="Setting")
     * @ORM\JoinColumn(name="voucher_type_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $voucherType;

    /**
     * @ORM\ManyToOne(targetEntity="AccountMasterVoucher")
     * @ORM\JoinColumn(name="master_voucher_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $masterVoucher;

    /**
     * @var AccountHead
     * @ORM\ManyToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="ledger_account_head_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private $accountHead;



    /**
     * @ORM\ManyToMany(targetEntity="AccountHead")
     * @ORM\JoinTable(name="acc_voucher_account_primary",
     *   joinColumns={@ORM\JoinColumn(name="account_voucher_id", referencedColumnName="id" , nullable=true, onDelete="cascade")},
     *   inverseJoinColumns={@ORM\JoinColumn(name="primary_account_head_id", referencedColumnName="id" , nullable=true, onDelete="cascade")}
     * )
     */
    private $primaryAccountHead;

    /**
     * @ORM\ManyToMany(targetEntity="AccountHead")
     * @ORM\JoinTable(name="acc_voucher_account_secondary",
     *   joinColumns={@ORM\JoinColumn(name="account_voucher_id", referencedColumnName="id" , nullable=true, onDelete="cascade")},
     *   inverseJoinColumns={@ORM\JoinColumn(name="secondary_account_head_id", referencedColumnName="id" , nullable=true, onDelete="cascade")}
     * )
     */
    private $secondaryAccountHead;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="short_name", type="string", length=20, nullable=true)
     */
    private $shortName;

    /**
     * @var string
     *
     * @ORM\Column(name="short_code", type="string", length=100, nullable=true)
     */
    private $shortCode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $mode;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=100, nullable=true)
     */
    private $slug;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_default", type="boolean", nullable=true)
     */
    private $isDefault = false;

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
    public function getVoucherType()
    {
        return $this->voucherType;
    }

    /**
     * @param mixed $voucherType
     */
    public function setVoucherType($voucherType)
    {
        $this->voucherType = $voucherType;
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
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
    }

    /**
     * @return string
     */
    public function getShortCode()
    {
        return $this->shortCode;
    }

    /**
     * @param string $shortCode
     */
    public function setShortCode($shortCode)
    {
        $this->shortCode = $shortCode;
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
    public function getAccountHeads()
    {
        return $this->accountHeads;
    }

    /**
     * @param mixed $accountHeads
     */
    public function setAccountHeads($accountHeads)
    {
        $this->accountHeads = $accountHeads;
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
     * @return mixed
     */
    public function getMasterVoucher()
    {
        return $this->masterVoucher;
    }

    /**
     * @param mixed $masterVoucher
     */
    public function setMasterVoucher($masterVoucher)
    {
        $this->masterVoucher = $masterVoucher;
    }

    /**
     * @return mixed
     */
    public function getLedgerAccountHead()
    {
        return $this->ledgerAccountHead;
    }

    /**
     * @param mixed $ledgerAccountHead
     */
    public function setLedgerAccountHead($ledgerAccountHead)
    {
        $this->ledgerAccountHead = $ledgerAccountHead;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->isDefault;
    }

    /**
     * @param bool $isDefault
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
    }






}

