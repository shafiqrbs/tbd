<?php

namespace Modules\Inventory\App\Entities;

use Modules\Core\App\Entities\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Utility\App\Entities\ProductUnit;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * BusinessStockHistory
 *
 * @ORM\Table( name = "inv_stock_history")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\BusinessStockHistoryRepository")
 */
class BusinessStockHistory
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config" , cascade={"detach","merge"} )
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="stockHistory" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $item;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessPurchaseItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $purchaseItem;


     /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessPurchaseReturnItem")
      * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $purchaseReturnItem;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessInvoiceParticular")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $salesItem;


     /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessInvoiceReturnItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $salesReturnItem;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessDamage")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $damageItem;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Marketing")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $marketing;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\WearHouse")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $wearHouse;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\ProductTransfer")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $itemTransfer;


    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $process;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $opening = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $quantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $openingQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $closingQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchaseQuantity = 0;


     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchaseReturnQuantity = 0;


     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $salesQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $transferQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $salesReturnQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $damageQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $bonusQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $bonusSalesQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $spoilQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $salesPrice = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchasePrice = 0;



    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;


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
     * @return mixed
     */
    public function getBusinessConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $businessConfig
     */
    public function setBusinessConfig($businessConfig)
    {
        $this->businessConfig = $config;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * @return mixed
     */
    public function getPurchaseItem()
    {
        return $this->purchaseItem;
    }

    /**
     * @param mixed $purchaseItem
     */
    public function setPurchaseItem($purchaseItem)
    {
        $this->purchaseItem = $purchaseItem;
    }

    /**
     * @return mixed
     */
    public function getPurchaseReturnItem()
    {
        return $this->purchaseReturnItem;
    }

    /**
     * @param mixed $purchaseReturnItem
     */
    public function setPurchaseReturnItem($purchaseReturnItem)
    {
        $this->purchaseReturnItem = $purchaseReturnItem;
    }

    /**
     * @return mixed
     */
    public function getSalesItem()
    {
        return $this->salesItem;
    }

    /**
     * @param mixed $salesItem
     */
    public function setSalesItem($salesItem)
    {
        $this->salesItem = $salesItem;
    }

    /**
     * @return mixed
     */
    public function getSalesReturnItem()
    {
        return $this->salesReturnItem;
    }

    /**
     * @param mixed $salesReturnItem
     */
    public function setSalesReturnItem($salesReturnItem)
    {
        $this->salesReturnItem = $salesReturnItem;
    }

    /**
     * @return mixed
     */
    public function getDamageItem()
    {
        return $this->damageItem;
    }

    /**
     * @param mixed $damageItem
     */
    public function setDamageItem($damageItem)
    {
        $this->damageItem = $damageItem;
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
     * @return float
     */
    public function getPurchaseQuantity()
    {
        return $this->purchaseQuantity;
    }

    /**
     * @param float $purchaseQuantity
     */
    public function setPurchaseQuantity($purchaseQuantity)
    {
        $this->purchaseQuantity = $purchaseQuantity;
    }

    /**
     * @return float
     */
    public function getPurchaseReturnQuantity()
    {
        return $this->purchaseReturnQuantity;
    }

    /**
     * @param float $purchaseReturnQuantity
     */
    public function setPurchaseReturnQuantity($purchaseReturnQuantity)
    {
        $this->purchaseReturnQuantity = $purchaseReturnQuantity;
    }

    /**
     * @return float
     */
    public function getSalesQuantity()
    {
        return $this->salesQuantity;
    }

    /**
     * @param float $salesQuantity
     */
    public function setSalesQuantity($salesQuantity)
    {
        $this->salesQuantity = $salesQuantity;
    }

    /**
     * @return float
     */
    public function getSalesReturnQuantity()
    {
        return $this->salesReturnQuantity;
    }

    /**
     * @param float $salesReturnQuantity
     */
    public function setSalesReturnQuantity($salesReturnQuantity)
    {
        $this->salesReturnQuantity = $salesReturnQuantity;
    }

    /**
     * @return float
     */
    public function getDamageQuantity()
    {
        return $this->damageQuantity;
    }

    /**
     * @param float $damageQuantity
     */
    public function setDamageQuantity($damageQuantity)
    {
        $this->damageQuantity = $damageQuantity;
    }

    /**
     * @return float
     */
    public function getBonusQuantity()
    {
        return $this->bonusQuantity;
    }

    /**
     * @param float $bonusQuantity
     */
    public function setBonusQuantity($bonusQuantity)
    {
        $this->bonusQuantity = $bonusQuantity;
    }

    /**
     * @return float
     */
    public function getSpoilQuantity()
    {
        return $this->spoilQuantity;
    }

    /**
     * @param float $spoilQuantity
     */
    public function setSpoilQuantity($spoilQuantity)
    {
        $this->spoilQuantity = $spoilQuantity;
    }

    /**
     * @return string
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param string $process
     */
    public function setProcess($process)
    {
        $this->process = $process;
    }

    /**
     * @return float
     */
    public function getOpeningQuantity()
    {
        return $this->openingQuantity;
    }

    /**
     * @param float $openingQuantity
     */
    public function setOpeningQuantity($openingQuantity)
    {
        $this->openingQuantity = $openingQuantity;
    }

    /**
     * @return float
     */
    public function getClosingQuantity()
    {
        return $this->closingQuantity;
    }

    /**
     * @param float $closingQuantity
     */
    public function setClosingQuantity($closingQuantity)
    {
        $this->closingQuantity = $closingQuantity;
    }

    /**
     * @return float
     */
    public function getOpening()
    {
        return $this->opening;
    }

    /**
     * @param float $opening
     */
    public function setOpening($opening)
    {
        $this->opening = $opening;
    }

    /**
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return float
     */
    public function getBonusSalesQuantity()
    {
        return $this->bonusSalesQuantity;
    }

    /**
     * @param float $bonusSalesQuantity
     */
    public function setBonusSalesQuantity($bonusSalesQuantity)
    {
        $this->bonusSalesQuantity = $bonusSalesQuantity;
    }

    /**
     * @return mixed
     */
    public function getMarketing()
    {
        return $this->marketing;
    }

    /**
     * @param mixed $marketing
     */
    public function setMarketing($marketing)
    {
        $this->marketing = $marketing;
    }

    /**
     * @return mixed
     */
    public function getWearHouse()
    {
        return $this->wearHouse;
    }

    /**
     * @param mixed $wearHouse
     */
    public function setWearHouse($wearHouse)
    {
        $this->wearHouse = $wearHouse;
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
     * @return float
     */
    public function getSalesPrice()
    {
        return $this->salesPrice;
    }

    /**
     * @param float $salesPrice
     */
    public function setSalesPrice($salesPrice)
    {
        $this->salesPrice = $salesPrice;
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
     * @return mixed
     */
    public function getItemTransfer()
    {
        return $this->itemTransfer;
    }

    /**
     * @param mixed $itemTransfer
     */
    public function setItemTransfer($itemTransfer)
    {
        $this->itemTransfer = $itemTransfer;
    }

    /**
     * @return float
     */
    public function getTransferQuantity()
    {
        return $this->transferQuantity;
    }

    /**
     * @param float $transferQuantity
     */
    public function setTransferQuantity($transferQuantity)
    {
        $this->transferQuantity = $transferQuantity;
    }






}

