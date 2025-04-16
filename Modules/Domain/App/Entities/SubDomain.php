<?php

namespace Modules\Domain\App\Entities;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * SubDomain
 *
 * @ORM\Table(name="dom_sub_domain")
 * @ORM\Entity(repositoryClass="Modules\Domain\App\Repositories\SubDomainRepository")
 */
class SubDomain
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
     * @ORM\ManyToOne(targetEntity="GlobalOption")
     **/
    private $domain;

     /**
     * @ORM\ManyToOne(targetEntity="GlobalOption")
     **/
    private $subDomain;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Customer")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $customer;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Vendor")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $vendor;

     /**
     * @var float
     * @ORM\Column(name="percent_mode",type="string" , nullable=true)
     */
    private $percentMode;

    /**
     * @var float
     *
     * @ORM\Column(name="mrp_percent",type="float" , nullable=true)
     */
    private $mrpPercent;

    /**
     * @var float
     *
     * @ORM\Column(name="purchase_percent",type="float" , nullable=true)
     */
    private $purchasePercent;

    /**
     * @var float
     *
     * @ORM\Column(name="bonus_percent",type="float" , nullable=true)
     */
    private $bonusPercent;

    /**
     * @var float
     *
     * @ORM\Column(name="sales_target_amount",type="float" , nullable=true)
     */
    private $salesTargetAmount;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\Setting")
     * @ORM\JoinColumn(name="domain_type", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $domainType;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status = true;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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
     * @return float
     */
    public function getPercentMode()
    {
        return $this->percentMode;
    }

    /**
     * @param float $percentMode
     */
    public function setPercentMode($percentMode)
    {
        $this->percentMode = $percentMode;
    }

    /**
     * @return float
     */
    public function getMrpPercent()
    {
        return $this->mrpPercent;
    }

    /**
     * @param float $mrpPercent
     */
    public function setMrpPercent($mrpPercent)
    {
        $this->mrpPercent = $mrpPercent;
    }

    /**
     * @return float
     */
    public function getPurchasePercent()
    {
        return $this->purchasePercent;
    }

    /**
     * @param float $purchasePercent
     */
    public function setPurchasePercent($purchasePercent)
    {
        $this->purchasePercent = $purchasePercent;
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
    public function getSalesTargetAmount()
    {
        return $this->salesTargetAmount;
    }

    /**
     * @param float $salesTargetAmount
     */
    public function setSalesTargetAmount($salesTargetAmount)
    {
        $this->salesTargetAmount = $salesTargetAmount;
    }

    /**
     * @return mixed
     */
    public function getDomainType()
    {
        return $this->domainType;
    }

    /**
     * @param mixed $domainType
     */
    public function setDomainType($domainType)
    {
        $this->domainType = $domainType;
    }




}

