<?php

namespace Modules\Utility\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * ProductUnit
 *
 * @ORM\Table(name="uti_product_unit")
 * @ORM\Entity()
 */

class ProductUnit
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="nameBn", type="string", length=255)
     */
    private $nameBn;


    /**
     * @Gedmo\Slug(fields={"name"})
     * @Doctrine\ORM\Mapping\Column(length=255)
     */
    private $slug;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status=true;


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
     * Set name
     *
     * @param string $name
     *
     * @return ProductUnit
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }



    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return ProductUnit
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set status
     *
     * @param boolean $status
     * @return ProductUnit
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return Product
     */
    public function getMasterProducts()
    {
        return $this->masterProducts;
    }

    /**
     * @return StockItem
     */
    public function getStockItems()
    {
        return $this->stockItems;
    }

    /**
     * @return Particular
     */
    public function getParticulars()
    {
        return $this->particulars;
    }

    /**
     * @return DmsParticular
     */
    public function getDmsParticulars()
    {
        return $this->dmsParticulars;
    }

    /**
     * @return MedicineStock
     */
    public function getMedicineStocks()
    {
        return $this->medicineStocks;
    }

    /**
     * @return BusinessParticular
     */
    public function getBusinessParticulars()
    {
        return $this->businessParticulars;
    }

    /**
     * @return DpsParticular
     */
    public function getDpsParticulars()
    {
        return $this->dpsParticulars;
    }

    /**
     * @return MedicineMinimumStock
     */
    public function getMedicineMinimumStock()
    {
        return $this->medicineMinimumStock;
    }

    /**
     * @return \Appstore\Bundle\RestaurantBundle\Entity\Particular
     */
    public function getRestaurantProduct()
    {
        return $this->restaurantProduct;
    }

    /**
     * @return string
     */
    public function getNameBn()
    {
        return $this->nameBn;
    }

    /**
     * @param string $nameBn
     */
    public function setNameBn($nameBn)
    {
        $this->nameBn = $nameBn;
    }




}

