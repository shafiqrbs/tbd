<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BusinessParticular
 *
 * @ORM\Table( name = "inv_config_discount")
 * @ORM\Entity()
 */
class ConfigDiscount
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
     * @ORM\OneToOne(targetEntity="Config", cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

     /**
     * @var float
     *
     * @ORM\Column(name="max_discount", type="float" , nullable=true)
     */
    private $maxDiscount;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" ,options={"default"="false"})
     */
    private $discountWithCustomer = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" ,options={"default"="false"})
     */
    private $onlineB2BCustomer = false;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="updated_at", type="datetime")
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
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
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
     * @return float
     */
    public function getMaxDiscount()
    {
        return $this->maxDiscount;
    }

    /**
     * @param float $maxDiscount
     */
    public function setMaxDiscount($maxDiscount)
    {
        $this->maxDiscount = $maxDiscount;
    }

    /**
     * @return bool
     */
    public function isDiscountWithCustomer()
    {
        return $this->discountWithCustomer;
    }

    /**
     * @param bool $discountWithCustomer
     */
    public function setDiscountWithCustomer($discountWithCustomer)
    {
        $this->discountWithCustomer = $discountWithCustomer;
    }

    /**
     * @return bool
     */
    public function isOnlineB2BCustomer()
    {
        return $this->onlineB2BCustomer;
    }

    /**
     * @param bool $onlineB2BCustomer
     */
    public function setOnlineB2BCustomer($onlineB2BCustomer)
    {
        $this->onlineB2BCustomer = $onlineB2BCustomer;
    }



}

