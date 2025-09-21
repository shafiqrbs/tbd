<?php

namespace Modules\Inventory\App\Entities;

use Modules\Inventory\App\Entities\Product;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Utility\App\Entities\ProductUnit;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * BusinessInvoiceReturnItem
 *
 * @ORM\Table(name ="inv_sales_return_item")
 * @ORM\Entity()
 */
class SalesReturnItem
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
     * @ORM\ManyToOne(targetEntity="SalesItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $salesItem;

    /**
     * @ORM\ManyToOne(targetEntity="SalesReturn")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $salesReturn;

    /**
     * @var string
     * @ORM\Column(name="item_name", type="string", nullable=true)
     */
    private $itemName;

    /**
     * @var string
     * @ORM\Column(name="uom", type="string", nullable=true)
     */
    private $uom;

    /**
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $stockItem;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Warehouse")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $warehouse;

    /**
     * @ORM\ManyToOne(targetEntity="PurchaseReturnItem")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $purchaseReturnItem;

    /**
     * @var float
     * @ORM\Column(name="quantity", type="float", nullable=true)
     */
    private $quantity;

    /**
     * @var float
     * @ORM\Column(name="bonus_quantity", type="float",nullable=true)
     */
    private $bonusQuantity;

    /**
     * @var float
     * @ORM\Column(name="price", type="float",nullable=true)
     */
    private $price;

    /**
     * @var float
     * @ORM\Column(name="sub_total", type="float", nullable = true)
     */
    private $subTotal;


	/**
	 * @var boolean
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
    public function getParticular()
    {
        return $this->particular;
    }

    /**
     * @param mixed $particular
     */
    public function setParticular($particular)
    {
        $this->particular = $particular;
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
     * @return string
     */
    public function getItemProcess()
    {
        return $this->itemProcess;
    }

    /**
     * @param string $itemProcess
     */
    public function setItemProcess($itemProcess)
    {
        $this->itemProcess = $itemProcess;
    }

    /**
     * @return mixed
     */
    public function getInvoiceReturn()
    {
        return $this->invoiceReturn;
    }

    /**
     * @param mixed $invoiceReturn
     */
    public function setInvoiceReturn($invoiceReturn)
    {
        $this->invoiceReturn = $invoiceReturn;
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
     * @return mixed
     */
    public function getInvoiceParticular()
    {
        return $this->invoiceParticular;
    }

    /**
     * @param mixed $invoiceParticular
     */
    public function setInvoiceParticular($invoiceParticular)
    {
        $this->invoiceParticular = $invoiceParticular;
    }

    /**
     * @return mixed
     */
    public function getBusinessParticular()
    {
        return $this->businessParticular;
    }

    /**
     * @param mixed $product
     */
    public function setBusinessParticular($product)
    {
        $this->businessParticular = $product;
    }






}

