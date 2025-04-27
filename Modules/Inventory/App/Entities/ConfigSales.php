<?php

namespace Modules\Inventory\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ConfigSales
 *
 * @ORM\Table( name ="inv_config_sales")
 * @ORM\Entity()
 */
class ConfigSales
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
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Setting")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
     private $defaultCustomerGroup;


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
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isMeasurementEnable;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default"="0"})
     */
    private $isZeroReceiveAllow;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $dueSalesWithoutCustomer;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $itemSalesPercent;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $zeroStock;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isMultiPrice;


    /**
     * @var boolean
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $isSalesAutoApproved;


     /**
     * @var boolean
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $discountWithCustomer = false;


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
     * @return mixed
     */
    public function getDefaultCustomerGroup()
    {
        return $this->defaultCustomerGroup;
    }

    /**
     * @param mixed $defaultCustomerGroup
     */
    public function setDefaultCustomerGroup($defaultCustomerGroup)
    {
        $this->defaultCustomerGroup = $defaultCustomerGroup;
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
     * @return bool
     */
    public function isPayFirst()
    {
        return $this->isPayFirst;
    }

    /**
     * @param bool $isPayFirst
     */
    public function setIsPayFirst($isPayFirst)
    {
        $this->isPayFirst = $isPayFirst;
    }

    /**
     * @return bool
     */
    public function isZeroReceiveAllow()
    {
        return $this->isZeroReceiveAllow;
    }

    /**
     * @param bool $isZeroReceiveAllow
     */
    public function setIsZeroReceiveAllow($isZeroReceiveAllow)
    {
        $this->isZeroReceiveAllow = $isZeroReceiveAllow;
    }

    /**
     * @return bool
     */
    public function isDueSalesWithoutCustomer()
    {
        return $this->dueSalesWithoutCustomer;
    }

    /**
     * @param bool $dueSalesWithoutCustomer
     */
    public function setDueSalesWithoutCustomer($dueSalesWithoutCustomer)
    {
        $this->dueSalesWithoutCustomer = $dueSalesWithoutCustomer;
    }

    /**
     * @return bool
     */
    public function isZeroStock()
    {
        return $this->zeroStock;
    }

    /**
     * @param bool $zeroStock
     */
    public function setZeroStock($zeroStock)
    {
        $this->zeroStock = $zeroStock;
    }

    /**
     * @return bool
     */
    public function isSalesAutoApproved()
    {
        return $this->isSalesAutoApproved;
    }

    /**
     * @param bool $isSalesAutoApproved
     */
    public function setIsSalesAutoApproved($isSalesAutoApproved)
    {
        $this->isSalesAutoApproved = $isSalesAutoApproved;
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
    public function isItemSalesPercent()
    {
        return $this->itemSalesPercent;
    }

    /**
     * @param bool $itemSalesPercent
     */
    public function setItemSalesPercent($itemSalesPercent)
    {
        $this->itemSalesPercent = $itemSalesPercent;
    }

    /**
     * @return bool
     */
    public function isMultiPrice()
    {
        return $this->isMultiPrice;
    }

    /**
     * @param bool $isMultiPrice
     */
    public function setIsMultiPrice($isMultiPrice)
    {
        $this->isMultiPrice = $isMultiPrice;
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



}

