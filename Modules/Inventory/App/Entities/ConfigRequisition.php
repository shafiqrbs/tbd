<?php

namespace Modules\Inventory\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ConfigRequisition
 *
 * @ORM\Table( name ="inv_config_requisition")
 * @ORM\Entity()
 */
class ConfigRequisition
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
     * @ORM\ManyToOne(targetEntity="Setting")
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
     * @return bool
     */
    public function isRemoveImage()
    {
        return $this->removeImage;
    }

    /**
     * @param bool $removeImage
     */
    public function setRemoveImage($removeImage)
    {
        $this->removeImage = $removeImage;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
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




}

