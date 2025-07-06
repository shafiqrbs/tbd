<?php

namespace Modules\Inventory\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ConfigPurchase
 *
 * @ORM\Table( name ="inv_config_purchase")
 * @ORM\Entity()
 */
class ConfigPurchase
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
     * @ORM\OneToOne(targetEntity="Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;


    /**
     * @var array|null
     * @ORM\Column(type="json", nullable=true)
     */
    private $purchaseProductNature = null;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $searchByVendor;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $searchByWarehouse;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $searchByProductNature;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $searchByCategory;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $showProduct;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Setting")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $defaultVendorGroup;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isMeasurementEnable;


    /**
     * @var boolean
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $isPurchaseAutoApproved;


     /**
     * @var boolean
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $isBarcode;


     /**
     * @var boolean
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $itemPercent;

    /**
     * @var boolean
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $isWarehouse;

    /**
     * @var boolean
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $isBonusQuantity;


    /**
     * @var boolean
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $isPurchaseByPurchasePrice;


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
     * @return bool
     */
    public function isSearchByVendor()
    {
        return $this->searchByVendor;
    }

    /**
     * @param bool $searchByVendor
     */
    public function setSearchByVendor($searchByVendor)
    {
        $this->searchByVendor = $searchByVendor;
    }

    /**
     * @return bool
     */
    public function isSearchByWarehouse()
    {
        return $this->searchByWarehouse;
    }

    /**
     * @param bool $searchByWarehouse
     */
    public function setSearchByWarehouse($searchByWarehouse)
    {
        $this->searchByWarehouse = $searchByWarehouse;
    }

    /**
     * @return mixed
     */
    public function getDefaultVendorGroup()
    {
        return $this->defaultVendorGroup;
    }

    /**
     * @param mixed $defaultVendorGroup
     */
    public function setDefaultVendorGroup($defaultVendorGroup)
    {
        $this->defaultVendorGroup = $defaultVendorGroup;
    }

    /**
     * @return bool
     */
    public function isMeasurementEnable()
    {
        return $this->isMeasurementEnable;
    }

    /**
     * @param bool $isMeasurementEnable
     */
    public function setIsMeasurementEnable($isMeasurementEnable)
    {
        $this->isMeasurementEnable = $isMeasurementEnable;
    }

    /**
     * @return bool
     */
    public function isPurchaseAutoApproved()
    {
        return $this->isPurchaseAutoApproved;
    }

    /**
     * @param bool $isPurchaseAutoApproved
     */
    public function setIsPurchaseAutoApproved($isPurchaseAutoApproved)
    {
        $this->isPurchaseAutoApproved = $isPurchaseAutoApproved;
    }



    /**
     * @return bool
     */
    public function isSearchByProductNature()
    {
        return $this->searchByProductNature;
    }

    /**
     * @param bool $searchByProductNature
     */
    public function setSearchByProductNature($searchByProductNature)
    {
        $this->searchByProductNature = $searchByProductNature;
    }

    /**
     * @return bool
     */
    public function isSearchByCategory()
    {
        return $this->searchByCategory;
    }

    /**
     * @param bool $searchByCategory
     */
    public function setSearchByCategory($searchByCategory)
    {
        $this->searchByCategory = $searchByCategory;
    }

    /**
     * @return bool
     */
    public function isShowProduct()
    {
        return $this->showProduct;
    }

    /**
     * @param bool $showProduct
     */
    public function setShowProduct($showProduct)
    {
        $this->showProduct = $showProduct;
    }

    /**
     * @return bool
     */
    public function isMultiUnit()
    {
        return $this->multiUnit;
    }

    /**
     * @param bool $multiUnit
     */
    public function setMultiUnit($multiUnit)
    {
        $this->multiUnit = $multiUnit;
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
     * @return bool
     */
    public function isBarcode()
    {
        return $this->isBarcode;
    }

    /**
     * @param bool $isBarcode
     */
    public function setIsBarcode($isBarcode)
    {
        $this->isBarcode = $isBarcode;
    }

    /**
     * @return bool
     */
    public function isItemPercent()
    {
        return $this->itemPercent;
    }

    /**
     * @param bool $itemPercent
     */
    public function setItemPercent($itemPercent)
    {
        $this->itemPercent = $itemPercent;
    }

    /**
     * @return bool
     */
    public function isWarehouse()
    {
        return $this->isWarehouse;
    }

    /**
     * @param bool $isWarehouse
     */
    public function setIsWarehouse($isWarehouse)
    {
        $this->isWarehouse = $isWarehouse;
    }

    /**
     * @return bool
     */
    public function isBonusQuantity()
    {
        return $this->isBonusQuantity;
    }

    /**
     * @param bool $isBonusQuantity
     */
    public function setIsBonusQuantity($isBonusQuantity)
    {
        $this->isBonusQuantity = $isBonusQuantity;
    }

    /**
     * @return bool
     */
    public function isPurchaseByPurchasePrice()
    {
        return $this->isPurchaseByPurchasePrice;
    }

    /**
     * @param bool $isPurchaseByPurchasePrice
     */
    public function setIsPurchaseByPurchasePrice($isPurchaseByPurchasePrice)
    {
        $this->isPurchaseByPurchasePrice = $isPurchaseByPurchasePrice;
    }



}

