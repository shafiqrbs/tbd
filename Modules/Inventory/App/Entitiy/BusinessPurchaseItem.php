<?php

namespace Appstore\Bundle\BusinessBundle\Entity;

use Appstore\Bundle\BusinessBundle\Entity\BusinessParticular;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Setting\Bundle\ToolBundle\Entity\ProductUnit;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * BusinessPurchaseItem
 *
 * @ORM\Table(name ="business_purchase_item")
 * @ORM\Entity(repositoryClass="Appstore\Bundle\BusinessBundle\Repository\BusinessPurchaseItemRepository")
 */
class BusinessPurchaseItem
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
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessParticular", inversedBy="businessPurchaseItems" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $businessParticular;


    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessPurchase", inversedBy="businessPurchaseItems" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $businessPurchase;

    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessDamage", mappedBy="businessPurchaseItem" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $businessDamages;

    /**
     * @ORM\ManyToOne(targetEntity="Setting\Bundle\ToolBundle\Entity\ProductUnit", inversedBy="businessPurchaseItems" )
     **/
    private  $unit;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\WearHouse")
     **/
    private  $wearhouse;


    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float")
     */
    private $quantity;



    /**
     * @var integer
     *
     * @ORM\Column(name="salesQuantity", type="integer",nullable=true)
     */
    private $salesQuantity = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="salesReturnQuantity", type="integer",nullable=true)
     */
    private $salesReturnQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="salesReplaceQuantity", type="integer",nullable=true)
     */
    private $salesReplaceQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="purchaseReturnQuantity", type="integer",nullable=true)
     */
    private $purchaseReturnQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="damageQuantity", type="integer",nullable=true)
     */
    private $damageQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="bonusQuantity", type="integer", nullable=true)
     */
    private $bonusQuantity = 0;


    /**
     * @var integer
     *
     * @ORM\Column(name="remainingQuantity", type="integer",nullable=true)
     */
    private $remainingQuantity;


    /**
     * @var float
     *
     * @ORM\Column(name="purchasePrice", type="float")
     */
    private $purchasePrice;


    /**
     * @var float
     *
     * @ORM\Column(name="actualPurchasePrice", type="float")
     */
    private $actualPurchasePrice;


    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer", nullable = true)
     */
    private $code;


    /**
     * @var float
     *
     * @ORM\Column(name="salesPrice", type="float", nullable = true)
     */
    private $salesPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="purchaseSubTotal", type="float", nullable = true)
     */
    private $purchaseSubTotal;


    /**
     * @var string
     *
     * @ORM\Column(name="barcode", type="string",  nullable = true)
     */
    private $barcode;


	/**
	 * @var float
	 *
	 * @ORM\Column(name="height", type="float", nullable=true)
	 */
	private $height;


	/**
	 * @var float
	 *
	 * @ORM\Column(name="width", type="float", nullable=true)
	 */
	private $width;


	/**
	 * @var float
	 *
	 * @ORM\Column(name="particularType", type="float", nullable=true)
	 */
	private $particularType;


	/**
	 * @var float
	 *
	 * @ORM\Column(name="length", type="float", nullable=true)
	 */
	private $length;


	/**
	 * @var float
	 *
	 * @ORM\Column(name="subQuantity", type="float", nullable=true)
	 */
	private $subQuantity;

	/**
	 * @var float
	 *
	 * @ORM\Column(name="totalQuantity", type="float", nullable=true)
	 */
	private $totalQuantity;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="status", type="boolean")
	 */
	private $status=false;




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
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return BusinessPurchaseItem
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
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
     * Set purchasePrice
     *
     * @param float $purchasePrice
     *
     * @return BusinessPurchase
     */
    public function setPurchasePrice($purchasePrice)
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }

    /**
     * Get purchasePrice
     *
     * @return float
     */
    public function getPurchasePrice()
    {
        return $this->purchasePrice;
    }




    /**
     * Set salesPrice
     *
     * @param float $salesPrice
     *
     * @return BusinessPurchaseItem
     */
    public function setSalesPrice($salesPrice)
    {
        $this->salesPrice = $salesPrice;

        return $this;
    }

    /**
     * Get salesPrice
     *
     * @return float
     */
    public function getSalesPrice()
    {
        return $this->salesPrice;
    }

    /**
     * @return string
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * @param string $barcode
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;
    }

    /**
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param integer $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }


    /**
     * @return float
     */
    public function getPurchaseSubTotal()
    {
        return $this->purchaseSubTotal;
    }

    /**
     * @param float $purchaseSubTotal
     */
    public function setPurchaseSubTotal($purchaseSubTotal)
    {
        $this->purchaseSubTotal = $purchaseSubTotal;
    }

    /**
     * @return BusinessPurchase
     */
    public function getBusinessPurchase()
    {
        return $this->businessPurchase;
    }

    /**
     * @param BusinessPurchase $businessPurchase
     */
    public function setBusinessPurchase($businessPurchase)
    {
        $this->businessPurchase = $businessPurchase;
    }

    /**
     * @return BusinessParticular
     */
    public function getBusinessParticular()
    {
        return $this->businessParticular;
    }

    /**
     * @param BusinessParticular $businessParticular
     */
    public function setBusinessParticular($businessParticular)
    {
        $this->businessParticular = $businessParticular;
    }

    /**
     * @return ProductUnit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param ProductUnit $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return float
     */
    public function getActualPurchasePrice()
    {
        return $this->actualPurchasePrice;
    }

    /**
     * @param float $actualPurchasePrice
     */
    public function setActualPurchasePrice($actualPurchasePrice)
    {
        $this->actualPurchasePrice = $actualPurchasePrice;
    }

    /**
     * @return int
     */
    public function getSalesQuantity()
    {
        return $this->salesQuantity;
    }

    /**
     * @param int $salesQuantity
     */
    public function setSalesQuantity($salesQuantity)
    {
        $this->salesQuantity = $salesQuantity;
    }

    /**
     * @return int
     */
    public function getSalesReturnQuantity()
    {
        return $this->salesReturnQuantity;
    }

    /**
     * @param int $salesReturnQuantity
     */
    public function setSalesReturnQuantity($salesReturnQuantity)
    {
        $this->salesReturnQuantity = $salesReturnQuantity;
    }

    /**
     * @return int
     */
    public function getSalesReplaceQuantity()
    {
        return $this->salesReplaceQuantity;
    }

    /**
     * @param int $salesReplaceQuantity
     */
    public function setSalesReplaceQuantity($salesReplaceQuantity)
    {
        $this->salesReplaceQuantity = $salesReplaceQuantity;
    }

    /**
     * @return int
     */
    public function getPurchaseReturnQuantity()
    {
        return $this->purchaseReturnQuantity;
    }

    /**
     * @param int $purchaseReturnQuantity
     */
    public function setPurchaseReturnQuantity($purchaseReturnQuantity)
    {
        $this->purchaseReturnQuantity = $purchaseReturnQuantity;
    }

    /**
     * @return int
     */
    public function getDamageQuantity()
    {
        return $this->damageQuantity;
    }

    /**
     * @param int $damageQuantity
     */
    public function setDamageQuantity($damageQuantity)
    {
        $this->damageQuantity = $damageQuantity;
    }

    /**
     * @return int
     */
    public function getRemainingQuantity()
    {
        return $this->remainingQuantity;
    }

    /**
     * @param int $remainingQuantity
     */
    public function setRemainingQuantity($remainingQuantity)
    {
        $this->remainingQuantity = $remainingQuantity;
    }

    /**
     * @return BusinessPurchaseReturnItem
     */
    public function getBusinessPurchaseReturnItems()
    {
        return $this->businessPurchaseReturnItems;
    }

    /**
     * @return BusinessDamage
     */
    public function getBusinessDamages()
    {
        return $this->businessDamages;
    }

	/**
	 * @return float
	 */
	public function getHeight(){
		return $this->height;
	}

	/**
	 * @param float $height
	 */
	public function setHeight( float $height ) {
		$this->height = $height;
	}

	/**
	 * @return float
	 */
	public function getWidth(){
		return $this->width;
	}

	/**
	 * @param float $width
	 */
	public function setWidth( float $width ) {
		$this->width = $width;
	}

	/**
	 * @return float
	 */
	public function getParticularType(): float {
		return $this->particularType;
	}

	/**
	 * @param float $particularType
	 */
	public function setParticularType( float $particularType ) {
		$this->particularType = $particularType;
	}

	/**
	 * @return float
	 */
	public function getLength(){
		return $this->length;
	}

	/**
	 * @param float $length
	 */
	public function setLength( float $length ) {
		$this->length = $length;
	}

	/**
	 * @return float
	 */
	public function getSubQuantity(){
		return $this->subQuantity;
	}

	/**
	 * @param float $subQuantity
	 */
	public function setSubQuantity( float $subQuantity ) {
		$this->subQuantity = $subQuantity;
	}

	/**
	 * @return float
	 */
	public function getTotalQuantity(){
		return $this->totalQuantity;
	}

	/**
	 * @param float $totalQuantity
	 */
	public function setTotalQuantity(  $totalQuantity ) {
		$this->totalQuantity = $totalQuantity;
	}

	/**
	 * @return bool
	 */
	public function isStatus(){
		return $this->status;
	}

	/**
	 * @param bool $status
	 */
	public function setStatus( bool $status ) {
		$this->status = $status;
	}

    /**
     * @return int
     */
    public function getBonusQuantity()
    {
        return $this->bonusQuantity;
    }

    /**
     * @param int $bonusQuantity
     */
    public function setBonusQuantity($bonusQuantity)
    {
        $this->bonusQuantity = $bonusQuantity;
    }

    /**
     * @return WearHouse
     */
    public function getWearhouse()
    {
        return $this->wearhouse;
    }

    /**
     * @param WearHouse $wearhouse
     */
    public function setWearhouse($wearhouse)
    {
        $this->wearhouse = $wearhouse;
    }




}

