<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * BusinessWearHouse
 *
 * @ORM\Table( name ="inv_wearhouse")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\WearHouseRepository")
 */
class WearHouse
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config", inversedBy="wearHouses" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;


    /**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="wearHouse")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $businessParticulars;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="shortCode", type="string", length=5, nullable=true)
     */
    private $shortCode;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=50, nullable=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=10, nullable=true)
     */
    private $code;

     /**
     * @var string
     *
     * @ORM\Column(name="wearHouseCode", type="string", length=10, nullable=true)
     */
    private $wearHouseCode;

    /**
     * @var int
     *
     * @ORM\Column(name="sorting", type="smallint",  length=2, nullable=true)
     */
    private $sorting = 0;


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
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }


    /**
     * @return bool
     */
    public function getStatus()
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
     * @return Product
     */
    public function getBusinessParticulars()
    {
        return $this->businessParticulars;
    }

	/**
	 * @return string
	 */
	public function getShortCode(){
		return $this->shortCode;
	}

	/**
	 * @param string $shortCode
	 */
	public function setShortCode( string $shortCode ) {
		$this->shortCode = $shortCode;
	}

	/**
	 * @return string
	 */
	public function getWearHouseCode(){
		return $this->wearHouseCode;
	}

	/**
	 * @param string $wearHouseCode
	 */
	public function setWearHouseCode( string $wearHouseCode ) {
		$this->wearHouseCode = $wearHouseCode;
	}


}

