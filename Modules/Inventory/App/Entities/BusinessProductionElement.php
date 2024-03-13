<?php

namespace Modules\Inventory\App\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * BusinessProductionElement
 *
 * @ORM\Table(name ="inv_production_element")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\BusinessProductionElementRepository")
 */
class BusinessProductionElement
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
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="productionElements" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $businessParticular;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="production" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $particular;

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
     * @return Product
     */
    public function getBusinessParticular()
    {
        return $this->businessParticular;
    }

    /**
     * @param Product $businessParticular
     */
    public function setBusinessParticular($businessParticular)
    {
        $this->businessParticular = $businessParticular;
    }

    /**
     * @return Product
     */
    public function getParticular()
    {
        return $this->particular;
    }

    /**
     * @param Product $particular
     */
    public function setParticular($particular)
    {
        $this->particular = $particular;
    }


}

