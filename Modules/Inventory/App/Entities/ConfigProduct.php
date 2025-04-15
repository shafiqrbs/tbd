<?php

namespace Modules\Inventory\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ConfigProduct
 *
 * @ORM\Table( name ="inv_config_product")
 * @ORM\Entity()
 */
class ConfigProduct
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
    private $skuCategory;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $skuBrand;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $skuModel;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $skuColor;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $skuSize;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $skuWarehouse;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $barcodePrint;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $barcodePriceHide;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $barcodeColor;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $barcodeSize;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $barcodeBrand;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isBrand;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isColor;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isSize;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isGrade;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isSku;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isModel;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isMultiPrice;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isMeasurement;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isProductGallery;

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
    public function isSkuCategory()
    {
        return $this->skuCategory;
    }

    /**
     * @param bool $skuCategory
     */
    public function setSkuCategory($skuCategory)
    {
        $this->skuCategory = $skuCategory;
    }

    /**
     * @return bool
     */
    public function isSkuBrand()
    {
        return $this->skuBrand;
    }

    /**
     * @param bool $skuBrand
     */
    public function setSkuBrand($skuBrand)
    {
        $this->skuBrand = $skuBrand;
    }

    /**
     * @return bool
     */
    public function isSkuModel()
    {
        return $this->skuModel;
    }

    /**
     * @param bool $skuModel
     */
    public function setSkuModel($skuModel)
    {
        $this->skuModel = $skuModel;
    }

    /**
     * @return bool
     */
    public function isSkuColor()
    {
        return $this->skuColor;
    }

    /**
     * @param bool $skuColor
     */
    public function setSkuColor($skuColor)
    {
        $this->skuColor = $skuColor;
    }

    /**
     * @return bool
     */
    public function isSkuSize()
    {
        return $this->skuSize;
    }

    /**
     * @param bool $skuSize
     */
    public function setSkuSize($skuSize)
    {
        $this->skuSize = $skuSize;
    }

    /**
     * @return bool
     */
    public function isSkuWarehouse()
    {
        return $this->skuWarehouse;
    }

    /**
     * @param bool $skuWarehouse
     */
    public function setSkuWarehouse($skuWarehouse)
    {
        $this->skuWarehouse = $skuWarehouse;
    }

    /**
     * @return bool
     */
    public function isBarcodePrint()
    {
        return $this->barcodePrint;
    }

    /**
     * @param bool $barcodePrint
     */
    public function setBarcodePrint($barcodePrint)
    {
        $this->barcodePrint = $barcodePrint;
    }

    /**
     * @return bool
     */
    public function isBarcodePriceHide()
    {
        return $this->barcodePriceHide;
    }

    /**
     * @param bool $barcodePriceHide
     */
    public function setBarcodePriceHide($barcodePriceHide)
    {
        $this->barcodePriceHide = $barcodePriceHide;
    }

    /**
     * @return bool
     */
    public function isBarcodeColor()
    {
        return $this->barcodeColor;
    }

    /**
     * @param bool $barcodeColor
     */
    public function setBarcodeColor($barcodeColor)
    {
        $this->barcodeColor = $barcodeColor;
    }

    /**
     * @return bool
     */
    public function isBarcodeSize()
    {
        return $this->barcodeSize;
    }

    /**
     * @param bool $barcodeSize
     */
    public function setBarcodeSize($barcodeSize)
    {
        $this->barcodeSize = $barcodeSize;
    }

    /**
     * @return bool
     */
    public function isBarcodeBrand()
    {
        return $this->barcodeBrand;
    }

    /**
     * @param bool $barcodeBrand
     */
    public function setBarcodeBrand($barcodeBrand)
    {
        $this->barcodeBrand = $barcodeBrand;
    }

    /**
     * @return bool
     */
    public function isBrand()
    {
        return $this->isBrand;
    }

    /**
     * @param bool $isBrand
     */
    public function setIsBrand($isBrand)
    {
        $this->isBrand = $isBrand;
    }

    /**
     * @return bool
     */
    public function isColor()
    {
        return $this->isColor;
    }

    /**
     * @param bool $isColor
     */
    public function setIsColor($isColor)
    {
        $this->isColor = $isColor;
    }

    /**
     * @return bool
     */
    public function isSize()
    {
        return $this->isSize;
    }

    /**
     * @param bool $isSize
     */
    public function setIsSize($isSize)
    {
        $this->isSize = $isSize;
    }

    /**
     * @return bool
     */
    public function isGrade()
    {
        return $this->isGrade;
    }

    /**
     * @param bool $isGrade
     */
    public function setIsGrade($isGrade)
    {
        $this->isGrade = $isGrade;
    }

    /**
     * @return bool
     */
    public function isSku()
    {
        return $this->isSku;
    }

    /**
     * @param bool $isSku
     */
    public function setIsSku($isSku)
    {
        $this->isSku = $isSku;
    }

    /**
     * @return bool
     */
    public function isModel()
    {
        return $this->isModel;
    }

    /**
     * @param bool $isModel
     */
    public function setIsModel($isModel)
    {
        $this->isModel = $isModel;
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
    public function isMeasurement()
    {
        return $this->isMeasurement;
    }

    /**
     * @param bool $isMeasurement
     */
    public function setIsMeasurement($isMeasurement)
    {
        $this->isMeasurement = $isMeasurement;
    }

    /**
     * @return bool
     */
    public function isProductGallery()
    {
        return $this->isProductGallery;
    }

    /**
     * @param bool $isProductGallery
     */
    public function setIsProductGallery($isProductGallery)
    {
        $this->isProductGallery = $isProductGallery;
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

