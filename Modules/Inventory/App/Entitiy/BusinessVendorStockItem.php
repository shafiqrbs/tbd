<?php

namespace Appstore\Bundle\BusinessBundle\Entity;

use Core\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Setting\Bundle\ToolBundle\Entity\ProductUnit;

/**
 * BusinessVendorStockItem
 *
 * @ORM\Table(name="business_vendor_stock_item")
 * @ORM\Entity(repositoryClass="Appstore\Bundle\BusinessBundle\Repository\BusinessVendorStockItemRepository")
 */
class BusinessVendorStockItem
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
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessVendorStock", inversedBy="businessVendorStockItems")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $businessVendorStock;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessParticular", inversedBy="businessVendorStockItems", cascade={"persist"} )
     **/
    private $particular;

     /**
         * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessInvoiceParticular", mappedBy="vendorStockItem", cascade={"persist"} )
         **/
    private $businessInvoiceParticulars;


    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     */
    private $quantity = 1;


    /**
     * @var integer
     *
     * @ORM\Column(name="salesQuantity", type="integer", nullable=true)
     */
    private $salesQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="subTotal", type="float", nullable=true)
     */
    private $subTotal = 0;



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
     * @return BusinessParticular
     */
    public function getParticular()
    {
        return $this->particular;
    }

    /**
     * @param BusinessParticular $particular
     */
    public function setParticular($particular)
    {
        $this->particular = $particular;
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
     * @return BusinessVendorStock
     */
    public function getBusinessVendorStock()
    {
        return $this->businessVendorStock;
    }

    /**
     * @param BusinessVendorStock $businessVendorStock
     */
    public function setBusinessVendorStock($businessVendorStock)
    {
        $this->businessVendorStock = $businessVendorStock;
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
     * @return BusinessInvoiceParticular
     */
    public function getBusinessInvoiceParticulars()
    {
        return $this->businessInvoiceParticulars;
    }


}

