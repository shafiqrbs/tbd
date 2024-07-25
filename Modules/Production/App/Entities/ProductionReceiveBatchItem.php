<?php

namespace Modules\Production\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Terminalbd\InventoryBundle\Entity\Item;

/**
 * ProductionWorkOrderItem
 *
 * @ORM\Table(name ="pro_receive_batch_item")
 * @ORM\Entity(repositoryClass="Modules\Production\App\Repositories\ProductionReceiveBatchItemRepository")
 */
class ProductionReceiveBatchItem
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
     * @ORM\ManyToOne(targetEntity="ProductionBatchItem", inversedBy="receiveItems")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"created" = "DESC"})
     **/
    private  $batchItem;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItem", inversedBy="receiveItems")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"created" = "DESC"})
     **/
    private  $item;


    /**
     * @ORM\ManyToOne(targetEntity="ProductionReceiveBatch", inversedBy="receiveItems")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $receiveBatch;



     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $receiveQuantity;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $damageQuantity;

     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $returnQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $totalQuantity;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $status = "invalid";


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
     * @return float
     */
    public function getDamageQuantity(): ? float
    {
        return $this->damageQuantity;
    }

    /**
     * @param float $damageQuantity
     */
    public function setDamageQuantity(float $damageQuantity)
    {
        $this->damageQuantity = $damageQuantity;
    }



    /**
     * @return float
     */
    public function getReceiveQuantity(): ? float
    {
        return $this->receiveQuantity;
    }

    /**
     * @param float $receiveQuantity
     */
    public function setReceiveQuantity(float $receiveQuantity)
    {
        $this->receiveQuantity = $receiveQuantity;
    }


    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return ProductionBatchItem
     */
    public function getBatchItem()
    {
        return $this->batchItem;
    }

    /**
     * @param ProductionBatchItem $batchItem
     */
    public function setBatchItem($batchItem)
    {
        $this->batchItem = $batchItem;
    }

    /**
     * @return float
     */
    public function getReturnQuantity()
    {
        return $this->returnQuantity;
    }

    /**
     * @param float $returnQuantity
     */
    public function setReturnQuantity($returnQuantity)
    {
        $this->returnQuantity = $returnQuantity;
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
    public function setTotalQuantity(float $totalQuantity)
    {
        $this->totalQuantity = $totalQuantity;
    }

    /**
     * @return ProductionReceiveBatch
     */
    public function getReceiveBatch()
    {
        return $this->receiveBatch;
    }

    /**
     * @param ProductionReceiveBatch $receiveBatch
     */
    public function setReceiveBatch($receiveBatch)
    {
        $this->receiveBatch = $receiveBatch;
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




}

