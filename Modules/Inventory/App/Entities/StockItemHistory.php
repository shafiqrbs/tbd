<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * BusinessStockHistory
 *
 * @ORM\Table( name="inv_stock_item_history")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\StockItemHistoryRepository")
 */
class StockItemHistory
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
     * @ORM\ManyToOne(targetEntity="Config" , cascade={"detach","merge"} )
     **/
    private  $config;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Setting")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $wearhouse;

    /**
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $stockItem;


    /**
     * @var string
     * @ORM\Column(type="string", nullable = true)
     */
    protected $itemName;


    /**
     * @var string
     * @ORM\Column(type="string", nullable = true)
     */
    protected $createdBy;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable = true)
     */
    private $price = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float", nullable = true)
     */
    private $quantity= 0.00;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $openingQuantity= 0.00;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $openingBalance= 0.00;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $closingQuantity= 0.00;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $closingBalance= 0.00;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", nullable = true)
     */
    private $process;

    /**
     * @var string
     *
     * @ORM\Column(name="mode", type="string", length = 40, nullable = true)
     */
    private $mode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $remark;


    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=50, nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $sku;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="issueDate", type="datetime", nullable=true)
     */
    private $issueDate;



    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create_at")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update_at")
     * @ORM\Column(type="datetime")
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
     * @return string
     */
    public function getItemName()
    {
        return $this->itemName;
    }

    /**
     * @param string $itemName
     */
    public function setItemName($itemName)
    {
        $this->itemName = $itemName;
    }

    /**
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param string $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
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
    public function getOpeningBalance()
    {
        return $this->openingBalance;
    }

    /**
     * @param float $openingBalance
     */
    public function setOpeningBalance($openingBalance)
    {
        $this->openingBalance = $openingBalance;
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
    public function getClosingBalance()
    {
        return $this->closingBalance;
    }

    /**
     * @param float $closingBalance
     */
    public function setClosingBalance($closingBalance)
    {
        $this->closingBalance = $closingBalance;
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
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param string $remark
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
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
    public function setIssueDate($issueDate)
    {
        $this->issueDate = $issueDate;
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
     * @return mixed
     */
    public function getWearhouse()
    {
        return $this->wearhouse;
    }

    /**
     * @param mixed $wearhouse
     */
    public function setWearhouse($wearhouse)
    {
        $this->wearhouse = $wearhouse;
    }



}
