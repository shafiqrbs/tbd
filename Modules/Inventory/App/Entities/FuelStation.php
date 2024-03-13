<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * FuelStation
 *
 * @ORM\Table( name ="inv_fuelstation")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\FuelStationRepository")
 */
class FuelStation
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config", inversedBy="marketing" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\WearHouse")
     **/
    private $waerhouse;

    /**
     * @ORM\ManyToOne(targetEntity="Product")
     **/
    private $particular;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private $salesBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private $verifiedBy;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private $approvedBy;


     /**
     * @var string
     *
     * @ORM\Column(name="process", type="string",nullable=true)
     */
     private $process = 'New';

     /**
     * @var float
     *
     * @ORM\Column(name="price", type="float",nullable=true)
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(name="total", type="float",nullable=true)
     */
    private $total;

     /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float",nullable=true)
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(name="startReader", type="float",nullable=true)
     */
    private $startReader;

    /**
     * @var float
     *
     * @ORM\Column(name="endReader", type="float",nullable=true)
     */
    private $endReader;


    /**
     * @var \DateTime
     * @ORM\Column(name="startDateTime", type="datetime",  nullable=true)
     */
    private $startDateTime;

    /**
     * @var \DateTime
     * @ORM\Column(name="endDateTime", type="datetime",  nullable=true)
     */
    private $endDateTime;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;


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
     * @return Config
     */
    public function getBusinessConfig()
    {
        return $this->config;
    }

    /**
     * @param Config $businessConfig
     */
    public function setBusinessConfig($businessConfig)
    {
        $this->businessConfig = $config;
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

