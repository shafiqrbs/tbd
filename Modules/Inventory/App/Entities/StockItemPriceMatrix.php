<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * StockItemPriceMatrix
 *
 * @ORM\Table( name = "inv_stock_item_price_matrix")
 * @ORM\Entity()
 */
class  StockItemPriceMatrix
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
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumn(name="stock_item_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $stockItem;


    /**
     * @ORM\ManyToOne(targetEntity="setting")
     * @ORM\JoinColumn(name="price_unit_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $priceUnit;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $price = 0;


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
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getStockItem()
    {
        return $this->stockItem;
    }

    /**
     * @param mixed $stockItem
     */
    public function setStockItem($stockItem)
    {
        $this->stockItem = $stockItem;
    }

    /**
     * @return mixed
     */
    public function getPriceUnit()
    {
        return $this->priceUnit;
    }

    /**
     * @param mixed $priceUnit
     */
    public function setPriceUnit($priceUnit)
    {
        $this->priceUnit = $priceUnit;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
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


}

