<?php

namespace Modules\Production\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ProductionElement
 *
 * @ORM\Table(name ="pro_expense")
 * @ORM\Entity(repositoryClass="Modules\Production\App\Repositories\ProductionExpenseRepository")
 */
class ProductionExpense
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
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionInventory")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $productionInventory;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionItem")
     **/
    private  $productionItem;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionBatchItem")
      * @ORM\JoinColumn(onDelete="CASCADE")
      **/
    private  $productionBatchItem;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Item")
     **/
    private  $item;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionElement")
     **/
    private  $productionElement;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionReceiveBatchItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $returnReceiveBatchItem;


    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float", nullable= true)
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $returnQuantity;

    /**
     * @var float
     *
     * @ORM\Column(name="purchasePrice", type="float", nullable = true)
     */
    private $purchasePrice;


    /**
     * @var float
     *
     * @ORM\Column(name="salesPrice", type="float", nullable = true)
     */
    private $salesPrice;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="issueDate", type="datetime",nullable=true)
     */
    private $issueDate;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;


    /**
     * Get id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param integer $quantity
     */

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }


    /**
     * @return ProductionElement
     */
    public function getProductionElement()
    {
        return $this->productionElement;
    }

    /**
     * @param ProductionElement $productionElement
     */
    public function setProductionElement($productionElement)
    {
        $this->productionElement = $productionElement;
    }


    /**
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param Item $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * @return ProductionInventory
     */
    public function getProductionInventory()
    {
        return $this->productionInventory;
    }

    /**
     * @param ProductionInventory $productionInventory
     */
    public function setProductionInventory($productionInventory)
    {
        $this->productionInventory = $productionInventory;
    }

    /**
     * @return ProductionItem
     */
    public function getProductionItem()
    {
        return $this->productionItem;
    }

    /**
     * @param ProductionItem $productionItem
     */
    public function setProductionItem($productionItem)
    {
        $this->productionItem = $productionItem;
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
    public function setPurchasePrice(float $purchasePrice)
    {
        $this->purchasePrice = $purchasePrice;
    }

    /**
     * @return float
     */
    public function getSalesPrice()
    {
        return $this->salesPrice;
    }

    /**
     * @param float $salesPrice
     */
    public function setSalesPrice(float $salesPrice)
    {
        $this->salesPrice = $salesPrice;
    }

    /**
     * @return ProductionBatchItem
     */
    public function getProductionBatchItem()
    {
        return $this->productionBatchItem;
    }

    /**
     * @param ProductionBatchItem $productionBatchItem
     */
    public function setProductionBatchItem($productionBatchItem)
    {
        $this->productionBatchItem = $productionBatchItem;
    }

    /**
     * @return float
     */
    public function getReturnQuantity(): float
    {
        return $this->returnQuantity;
    }

    /**
     * @param float $returnQuantity
     */
    public function setReturnQuantity(float $returnQuantity)
    {
        $this->returnQuantity = $returnQuantity;
    }

    /**
     * @return ProductionReceiveBatchItem
     */
    public function getReturnReceiveBatchItem()
    {
        return $this->returnReceiveBatchItem;
    }

    /**
     * @param ProductionReceiveBatchItem $returnReceiveBatchItem
     */
    public function setReturnReceiveBatchItem($returnReceiveBatchItem)
    {
        $this->returnReceiveBatchItem = $returnReceiveBatchItem;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getIssueDate()
    {
        return $this->issueDate;
    }

    /**
     * @param \DateTime $issueDate
     */
    public function setIssueDate(\DateTime $issueDate)
    {
        $this->issueDate = $issueDate;
    }




}
