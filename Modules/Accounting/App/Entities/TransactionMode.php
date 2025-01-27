<?php

namespace Modules\Accounting\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AccountTransactionMode
 *
 * @ORM\Table(name ="acc_transaction_mode")
 * @ORM\Entity()
 */
class TransactionMode
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
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     **/
    private $config;

    /**
     * @var Setting
     * @ORM\ManyToOne(targetEntity="Setting")
     **/
    private $method;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $accountOwner;


    /**
     * @var string
     *
     * @ORM\Column(name="authorised", type="string", length=255, nullable=true)
     */
    private $authorised;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $serviceName;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shortName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=255, nullable=true)
     */
    private $mobile;


     /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $serviceCharge = 0;


     /**
     * @var float
     *
     * @ORM\Column(type="boolean",options={"default"="0"})
     */
    private $isSelected;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status = true;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Accounting\App\Entities\Setting", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     **/
    private $authorisedMode;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Accounting\App\Entities\Setting", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     **/
    private $accountMode;



    /**
     * @ORM\ManyToOne(targetEntity="Modules\Accounting\App\Entities\Setting", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     **/
    private $accountTypeMode;



    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $accountType;



    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="updated_at", type="datetime",nullable=true)
     */
    private $updatedAt;

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
     * @return Setting
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param Setting $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getAccountOwner()
    {
        return $this->accountOwner;
    }

    /**
     * @param string $accountOwner
     */
    public function setAccountOwner($accountOwner)
    {
        $this->accountOwner = $accountOwner;
    }

    /**
     * @return string
     */
    public function getAuthorised()
    {
        return $this->authorised;
    }

    /**
     * @param string $authorised
     */
    public function setAuthorised($authorised)
    {
        $this->authorised = $authorised;
    }

    /**
     * @return string
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @param string $serviceName
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
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
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return float
     */
    public function getServiceCharge()
    {
        return $this->serviceCharge;
    }

    /**
     * @param float $serviceCharge
     */
    public function setServiceCharge($serviceCharge)
    {
        $this->serviceCharge = $serviceCharge;
    }

    /**
     * @return float
     */
    public function getIsSelected()
    {
        return $this->isSelected;
    }

    /**
     * @param float $isSelected
     */
    public function setIsSelected($isSelected)
    {
        $this->isSelected = $isSelected;
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
     * @return mixed
     */
    public function getAuthorisedMode()
    {
        return $this->authorisedMode;
    }

    /**
     * @param mixed $authorisedMode
     */
    public function setAuthorisedMode($authorisedMode)
    {
        $this->authorisedMode = $authorisedMode;
    }

    /**
     * @return mixed
     */
    public function getAccountMode()
    {
        return $this->accountMode;
    }

    /**
     * @param mixed $accountMode
     */
    public function setAccountMode($accountMode)
    {
        $this->accountMode = $accountMode;
    }

    /**
     * @return mixed
     */
    public function getAccountTypeMode()
    {
        return $this->accountTypeMode;
    }

    /**
     * @param mixed $accountTypeMode
     */
    public function setAccountTypeMode($accountTypeMode)
    {
        $this->accountTypeMode = $accountTypeMode;
    }

    /**
     * @return string
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @param string $accountType
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
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




}

