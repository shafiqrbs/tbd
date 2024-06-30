<?php

namespace Modules\Production\App\Entities;

use App\Entity\Application\Production;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Damage
 *
 * @ORM\Table("pro_inventory")
 * @ORM\Entity(repositoryClass="Modules\Production\App\Repositories\ProductionInventoryRepository")
 */
class ProductionInventory
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Item")
     **/
    private  $item;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string" , nullable=true)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="purchasePrice", type="float", nullable = true)
     */
    private $purchasePrice;



    /**
     * @var integer
     *
     * @ORM\Column(type="integer" , nullable=true)
     */
    private $quantity = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer" , nullable=true)
     */
    private $issueQuantity = 0;

     /**
     * @var integer
     *
     * @ORM\Column(type="integer" , nullable=true)
     */
    private $returnQuantity = 0;


      /**
     * @var integer
     *
     * @ORM\Column(type="integer" , nullable=true)
     */
    private $damageQuantity = 0;


     /**
     * @var integer
     *
     * @ORM\Column(type="integer" , nullable = true)
     */
    private $remainigQuantity = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="uom", type="string", nullable=true)
     */
    private $uom;




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
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
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
     * @return Production
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Production $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }



    /**
     * @return int
     */
    public function getIssueQuantity(): int
    {
        return $this->issueQuantity;
    }

    /**
     * @param int $issueQuantity
     */
    public function setIssueQuantity(int $issueQuantity)
    {
        $this->issueQuantity = $issueQuantity;
    }

    /**
     * @return int
     */
    public function getReturnQuantity(): int
    {
        return $this->returnQuantity;
    }

    /**
     * @param int $returnQuantity
     */
    public function setReturnQuantity(int $returnQuantity)
    {
        $this->returnQuantity = $returnQuantity;
    }

    /**
     * @return int
     */
    public function getDamageQuantity(): int
    {
        return $this->damageQuantity;
    }

    /**
     * @param int $damageQuantity
     */
    public function setDamageQuantity(int $damageQuantity)
    {
        $this->damageQuantity = $damageQuantity;
    }



    /**
     * @return string
     */
    public function getUom(): string
    {
        return $this->uom;
    }

    /**
     * @param string $uom
     */
    public function setUom(string $uom)
    {
        $this->uom = $uom;
    }

    /**
     * @return int
     */
    public function getRemainigQuantity(): int
    {
        return $this->remainigQuantity;
    }

    /**
     * @param int $remainigQuantity
     */
    public function setRemainigQuantity(int $remainigQuantity)
    {
        $this->remainigQuantity = $remainigQuantity;
    }

    /**
     * @return string
     */
    public function getName():? string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return float
     */
    public function getPurchasePrice(): ? float
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

}

