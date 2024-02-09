<?php

namespace Appstore\Bundle\BusinessBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Setting\Bundle\LocationBundle\Entity\Location;

/**
 * BusinessWearHouse
 *
 * @ORM\Table( name ="business_area")
 * @ORM\Entity(repositoryClass="")
 */
class BusinessArea
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
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessConfig", inversedBy="area" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $businessConfig;

    /**
     * @ORM\ManyToOne(targetEntity="Setting\Bundle\LocationBundle\Entity\Location", inversedBy="area")
     **/
    private $location;

    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessInvoice", mappedBy="area")
      **/
    private $invoices;

    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessStore", mappedBy="area")
      **/
    private $stores;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;


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
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param Location $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
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
     * @return BusinessStore
     */
    public function getStores()
    {
        return $this->stores;
    }


}

