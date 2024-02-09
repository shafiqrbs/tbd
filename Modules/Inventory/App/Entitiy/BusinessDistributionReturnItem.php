<?php

namespace Appstore\Bundle\BusinessBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BusinessPurchaseReturnItem
 *
 * @ORM\Table(name ="business_distribution_return_item")
 * @ORM\Entity(repositoryClass="Appstore\Bundle\BusinessBundle\Repository\BusinessDistributionReturnItemRepository")
 */
class BusinessDistributionReturnItem
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
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessConfig", inversedBy="businessPurchasesReturns" , cascade={"detach","merge"} )
     **/
    private  $businessConfig;


    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessParticular", inversedBy="businessDistributionReturnItem" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $businessParticular;

     /**
     * @ORM\OneToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessInvoiceReturnItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $invoiceReturnItem;

    /**
     * @ORM\OneToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessInvoiceParticular")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $invoiceItem;


    /**
     * @var int
     *
     * @ORM\Column(name="salesInvoice", type="integer", nullable=true)
     */
    private $salesInvoice;



    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer",nullable=true)
     */
    private $quantity;

     /**
     * @var integer
     *
     * @ORM\Column(name="spoilQnt", type="integer",nullable=true)
     */
    private $spoilQnt;


     /**
     * @var integer
     *
     * @ORM\Column(name="damageQnt", type="integer",nullable=true)
     */
    private $damageQnt;

    /**
     * @var integer
     *
     * @ORM\Column(name="deliverQnt", type="integer",nullable=true)
     */
    private $deliverQnt;


     /**
     * @var integer
     *
     * @ORM\Column(name="remainingQnt", type="integer",nullable=true)
     */
    private $remainingQnt;


     /**
     * @var integer
     *
     * @ORM\Column(name="salesInvoiceItem", type="integer",nullable=true)
     */
    private $salesInvoiceItem;

    /**
     * @var float
     *
     * @ORM\Column(name="purchasePrice", type="float",nullable=true)
     */
    private $purchasePrice;

    /**
     * @var float
     *
     * @ORM\Column(name="subTotal", type="float",nullable=true)
     */
    private $subTotal;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status=false;

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
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return BusinessPurchaseReturnItem
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
     * @return BusinessPurchaseReturnItem
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
     * @return int
     */
    public function getSalesInvoiceItem()
    {
        return $this->salesInvoiceItem;
    }

    /**
     * @param int $salesInvoiceItem
     */
    public function setSalesInvoiceItem($salesInvoiceItem)
    {
        $this->salesInvoiceItem = $salesInvoiceItem;
    }

    /**
     * @return int
     */
    public function getSpoilQnt()
    {
        return $this->spoilQnt;
    }

    /**
     * @param int $spoilQnt
     */
    public function setSpoilQnt($spoilQnt)
    {
        $this->spoilQnt = $spoilQnt;
    }

    /**
     * @return int
     */
    public function getDamageQnt()
    {
        return $this->damageQnt;
    }

    /**
     * @param int $damageQnt
     */
    public function setDamageQnt($damageQnt)
    {
        $this->damageQnt = $damageQnt;
    }

    /**
     * @return int
     */
    public function getDeliverQnt()
    {
        return $this->deliverQnt;
    }

    /**
     * @param int $deliverQnt
     */
    public function setDeliverQnt($deliverQnt)
    {
        $this->deliverQnt = $deliverQnt;
    }

    /**
     * @return int
     */
    public function getRemainingQnt()
    {
        return $this->remainingQnt;
    }

    /**
     * @param int $remainingQnt
     */
    public function setRemainingQnt($remainingQnt)
    {
        $this->remainingQnt = $remainingQnt;
    }

    /**
     * @return BusinessConfig
     */
    public function getBusinessConfig()
    {
        return $this->businessConfig;
    }

    /**
     * @param BusinessConfig $businessConfig
     */
    public function setBusinessConfig($businessConfig)
    {
        $this->businessConfig = $businessConfig;
    }

    /**
     * @return int
     */
    public function getSalesInvoice()
    {
        return $this->salesInvoice;
    }

    /**
     * @param int $salesInvoice
     */
    public function setSalesInvoice($salesInvoice)
    {
        $this->salesInvoice = $salesInvoice;
    }

    /**
     * @return mixed
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param mixed $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
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
     * @return mixed
     */
    public function getInvoiceReturnItem()
    {
        return $this->invoiceReturnItem;
    }

    /**
     * @param mixed $invoiceReturnItem
     */
    public function setInvoiceReturnItem($invoiceReturnItem)
    {
        $this->invoiceReturnItem = $invoiceReturnItem;
    }

    /**
     * @return mixed
     */
    public function getInvoiceItem()
    {
        return $this->invoiceItem;
    }

    /**
     * @param mixed $invoiceItem
     */
    public function setInvoiceItem($invoiceItem)
    {
        $this->invoiceItem = $invoiceItem;
    }


}

