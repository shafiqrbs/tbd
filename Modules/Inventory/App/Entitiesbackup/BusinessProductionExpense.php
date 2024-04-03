<?php

namespace Modules\Inventory\App\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * BusinessProductionElement
 *
 * @ORM\Table(name ="inv_production_expense")
 * @ORM\Entity()
 */
class BusinessProductionExpense
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessProduction", inversedBy="businessProductionExpense" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $businessProduction;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="businessProductionExpense" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $productionItem;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="businessProductionExpenseItem" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $productionElement;

    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float")
     */
    private $quantity;

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
     * Get id
     *
     * @return integer
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
     * Set purchasePrice
     * @param float $purchasePrice
     */
    public function setPurchasePrice($purchasePrice)
    {
        $this->purchasePrice = $purchasePrice;
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
     * @param float $salesPrice
     */
    public function setSalesPrice($salesPrice)
    {
        $this->salesPrice = $salesPrice;
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
     * @return SalesItem
     */
    public function getBusinessInvoiceParticular()
    {
        return $this->businessInvoiceParticular;
    }

    /**
     * @param SalesItem $salesInvoiceItem
     */
    public function setBusinessInvoiceParticular($salesInvoiceItem)
    {
        $this->businessInvoiceParticular = $salesInvoiceItem;
    }

    /**
     * @return Product
     */
    public function getProductionItem()
    {
        return $this->productionItem;
    }

    /**
     * @param Product $productionItem
     */
    public function setProductionItem($productionItem)
    {
        $this->productionItem = $productionItem;
    }

    /**
     * @return Product
     */
    public function getProductionElement()
    {
        return $this->productionElement;
    }

    /**
     * @param Product $productionElement
     */
    public function setProductionElement($productionElement)
    {
        $this->productionElement = $productionElement;
    }

	/**
	 * @return BusinessProduction
	 */
	public function getBusinessProduction() {
		return $this->businessProduction;
	}

	/**
	 * @param BusinessProduction $businessProduction
	 */
	public function setBusinessProduction( $businessProduction ) {
		$this->businessProduction = $businessProduction;
	}


}

