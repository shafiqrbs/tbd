<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * RestaurantCategory
 * @Gedmo\Tree(type="materializedPath")
 * @ORM\Table( name ="inv_category")
 * @ORM\Entity()
 */
class Category
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
     * @ORM\ManyToOne(targetEntity="Config" , cascade={"persist", "remove"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Setting")
     * @ORM\JoinColumn(name="category_nature_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private  $categoryNature;


    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children" , cascade={"persist", "remove"}  )
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * })
     */
    private $parent;

    /**
     * @ORM\ManyToOne(targetEntity="Discount")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private  $discount;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=50, nullable=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="integer", length=3, nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="generate_id", type="string", length=10, nullable=true)
     */
    private $generateId;

    /**
     * @var int
     *
     * @ORM\Column(name="sorting", type="smallint",  length=2, nullable=true)
     */
    private $sorting = 0;


    /**
     * @var int
     *
     * @ORM\Column( type="smallint",  length=2, nullable=true)
     */
    private $weightAvgPriceDuration = 0;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",nullable=true )
     */
    private $hasQuantity = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;

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
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
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
     * @return int
     */
    public function getWeightAvgPriceDuration()
    {
        return $this->weightAvgPriceDuration;
    }

    /**
     * @param int $weightAvgPriceDuration
     */
    public function setWeightAvgPriceDuration($weightAvgPriceDuration)
    {
        $this->weightAvgPriceDuration = $weightAvgPriceDuration;
    }

    /**
     * @return bool
     */
    public function isHasQuantity()
    {
        return $this->hasQuantity;
    }

    /**
     * @param bool $hasQuantity
     */
    public function setHasQuantity($hasQuantity)
    {
        $this->hasQuantity = $hasQuantity;
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
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param mixed $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * @return string
     */
    public function getGenerateId()
    {
        return $this->generateId;
    }

    /**
     * @param string $generateId
     */
    public function setGenerateId($generateId)
    {
        $this->generateId = $generateId;
    }






}

