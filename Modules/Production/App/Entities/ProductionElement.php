<?php

namespace Modules\Production\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * ProductionElement
 *
 * @ORM\Table(name ="pro_element")
 * @ORM\Entity(repositoryClass="Modules\Production\App\Repositories\ProductionElementRepository")
 */
class ProductionElement
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
     * @ORM\ManyToOne(targetEntity="Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="ProductionItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $productionItem;

     /**
     * @ORM\ManyToOne(targetEntity="ProductionItemAmendment")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $productionItemAmendment;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $material;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $materialQuantity;


    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float")
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $uom;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $purchasePrice;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $price;


    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable = true)
     */
    private $subTotal;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $wastageQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $totalQuantity;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $wastagePercent;


    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable = true)
     */
    private $wastageAmount;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $wastageSubTotal;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", options={"default"="false"}, nullable=true)
     */
    private $status = true;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime",nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime", nullable = true)
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
    public function getProductionItem()
    {
        return $this->productionItem;
    }

    /**
     * @param mixed $productionItem
     */
    public function setProductionItem($productionItem)
    {
        $this->productionItem = $productionItem;
    }

    /**
     * @return mixed
     */
    public function getProductionItemAmendment()
    {
        return $this->productionItemAmendment;
    }

    /**
     * @param mixed $productionItemAmendment
     */
    public function setProductionItemAmendment($productionItemAmendment)
    {
        $this->productionItemAmendment = $productionItemAmendment;
    }

    /**
     * @return mixed
     */
    public function getMaterial()
    {
        return $this->material;
    }

    /**
     * @param mixed $material
     */
    public function setMaterial($material)
    {
        $this->material = $material;
    }

    /**
     * @return float
     */
    public function getMaterialQuantity()
    {
        return $this->materialQuantity;
    }

    /**
     * @param float $materialQuantity
     */
    public function setMaterialQuantity($materialQuantity)
    {
        $this->materialQuantity = $materialQuantity;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param float $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return string
     */
    public function getUom()
    {
        return $this->uom;
    }

    /**
     * @param string $uom
     */
    public function setUom($uom)
    {
        $this->uom = $uom;
    }

    /**
     * @return float
     */
    public function getPurchasePrice()
    {
        return $this->purchasePrice;
    }

    /**
     * @param float $purchasePrice
     */
    public function setPurchasePrice($purchasePrice)
    {
        $this->purchasePrice = $purchasePrice;
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
     * @return float
     */
    public function getSubTotal()
    {
        return $this->subTotal;
    }

    /**
     * @param float $subTotal
     */
    public function setSubTotal($subTotal)
    {
        $this->subTotal = $subTotal;
    }

    /**
     * @return float
     */
    public function getWastageQuantity()
    {
        return $this->wastageQuantity;
    }

    /**
     * @param float $wastageQuantity
     */
    public function setWastageQuantity($wastageQuantity)
    {
        $this->wastageQuantity = $wastageQuantity;
    }

    /**
     * @return float
     */
    public function getTotalQuantity()
    {
        return $this->totalQuantity;
    }

    /**
     * @param float $totalQuantity
     */
    public function setTotalQuantity($totalQuantity)
    {
        $this->totalQuantity = $totalQuantity;
    }

    /**
     * @return float
     */
    public function getWastagePercent()
    {
        return $this->wastagePercent;
    }

    /**
     * @param float $wastagePercent
     */
    public function setWastagePercent($wastagePercent)
    {
        $this->wastagePercent = $wastagePercent;
    }

    /**
     * @return float
     */
    public function getWastageAmount()
    {
        return $this->wastageAmount;
    }

    /**
     * @param float $wastageAmount
     */
    public function setWastageAmount($wastageAmount)
    {
        $this->wastageAmount = $wastageAmount;
    }

    /**
     * @return float
     */
    public function getWastageSubTotal()
    {
        return $this->wastageSubTotal;
    }

    /**
     * @param float $wastageSubTotal
     */
    public function setWastageSubTotal($wastageSubTotal)
    {
        $this->wastageSubTotal = $wastageSubTotal;
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

