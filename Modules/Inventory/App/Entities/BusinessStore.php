<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Courier
 *
 * @ORM\Table( name ="inv_store")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\BusinessStoreRepository")
 */
class BusinessStore
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
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Customer")
     **/
    private $customer;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Marketing")
     **/
    private $marketing;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessArea", inversedBy="stores" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $area;

    /**
     * @ORM\OneToMany(targetEntity="Modules\Inventory\App\Entities\BusinessStoreLedger", mappedBy="store")
     **/
    private $storeLedgers;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="mobileNo", type="string", length=100, nullable=true)
     */
    private $mobileNo;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", nullable=true)
     */
    private $address;

    /**
     * @var \DateTime
     * @ORM\Column(name="joiningDate", type="datetime",  nullable=true)
     */
    private $joiningDate;

    /**
     * @var float
     *
     * @ORM\Column(name="balance", type="float",  nullable = true)
     */
    private $balance;



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
    public function getMarketing()
    {
        return $this->marketing;
    }

    /**
     * @param mixed $marketing
     */
    public function setMarketing($marketing)
    {
        $this->marketing = $marketing;
    }

    /**
     * @return mixed
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param mixed $area
     */
    public function setArea($area)
    {
        $this->area = $area;
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
    public function getStoreLedgers()
    {
        return $this->storeLedgers;
    }

    /**
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param float $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }


}

