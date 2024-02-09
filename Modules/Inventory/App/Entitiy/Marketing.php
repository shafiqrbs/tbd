<?php

namespace Appstore\Bundle\BusinessBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BusinessWearHouse
 *
 * @ORM\Table( name ="business_marketing")
 * @ORM\Entity(repositoryClass="")
 */
class Marketing
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
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessConfig", inversedBy="marketing" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $businessConfig;

    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessInvoice", mappedBy="marketing")
     **/
    private $invoices;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="designation", type="string", length=100, nullable=true)
     */
    private $designation;

    /**
     * @var string
     *
     * @ORM\Column(name="mobileNo", type="string", length=100, nullable=true)
     */
    private $mobileNo;

    /**
     * @var string
     *
     * @ORM\Column(name="companyName", type="string", length=200, nullable=true)
     */
    private $companyName;

     /**
     * @var float
     *
     * @ORM\Column(name="monthlySales", type="float",nullable=true)
     */
    private $monthlySales;

    /**
     * @var float
     *
     * @ORM\Column(name="yearlySales", type="float",nullable=true)
     */
    private $yearlySales;

     /**
     * @var float
     *
     * @ORM\Column(name="discount", type="float",nullable=true)
     */
    private $discount;

    /**
     * @var \DateTime
     * @ORM\Column(name="joiningDate", type="datetime",  nullable=true)
     */
    private $joiningDate;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;


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
     * @return WearHouse
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param WearHouse $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return BusinessConfig
     */
    public function getBusinessConfig()
    {
        return $this->businessConfig;
    }

    /**
     * @param BusinessConfig $businessConfig
     */
    public function setBusinessConfig($businessConfig)
    {
        $this->businessConfig = $businessConfig;
    }

    /**
     * @return BusinessInvoice
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

    /**
     * @return string
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * @param string $designation
     */
    public function setDesignation(string $designation)
    {
        $this->designation = $designation;
    }

    /**
     * @return string
     */
    public function getMobileNo()
    {
        return $this->mobileNo;
    }

    /**
     * @param string $mobileNo
     */
    public function setMobileNo(string $mobileNo)
    {
        $this->mobileNo = $mobileNo;
    }

    /**
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     */
    public function setCompanyName(string $companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * @return \DateTime
     */
    public function getJoiningDate()
    {
        return $this->joiningDate;
    }

    /**
     * @param \DateTime $joiningDate
     */
    public function setJoiningDate($joiningDate)
    {
        $this->joiningDate = $joiningDate;
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
     * @return float
     */
    public function getMonthlySales()
    {
        return $this->monthlySales;
    }

    /**
     * @param float $monthlySales
     */
    public function setMonthlySales($monthlySales)
    {
        $this->monthlySales = $monthlySales;
    }

    /**
     * @return float
     */
    public function getYearlySales()
    {
        return $this->yearlySales;
    }

    /**
     * @param float $yearlySales
     */
    public function setYearlySales($yearlySales)
    {
        $this->yearlySales = $yearlySales;
    }

    /**
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }




}

