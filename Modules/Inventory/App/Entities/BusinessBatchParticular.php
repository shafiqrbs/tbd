<?php

namespace Modules\Inventory\App\Entities;

use Modules\Core\App\Entities\Vendor;
use Doctrine\ORM\Mapping as ORM;


/**
 * BusinessInvoiceParticular
 *
 * @ORM\Table( name = "inv_batch_particular")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\BusinessBatchParticularRepository")
 */
class BusinessBatchParticular
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var BusinessBatch
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessBatch", inversedBy="businessBatchParticulars")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"id" = "ASC"})
     **/
    private $businessBatch;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessParticular")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $businessParticular;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\WearHouse")
     **/
    private $wearhouse;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private $salesBy;

    /**
     * @var string
     *
     * @ORM\Column(name="particular", type="text", nullable=true)
     */
    private $particular;

    /**
     * @var float
     *
     * @ORM\Column(name="startMeterReading", type="float", nullable=true)
     */
    private $startMeterReading;

     /**
     * @var float
     *
     * @ORM\Column(name="endMeterReading", type="float", nullable=true)
     */
    private $endMeterReading;

    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float",  nullable=true)
     */
    private $quantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="customerSalesQuantity", type="float",  nullable=true)
     */
    private $customerSalesQuantity = 0;

     /**
     * @var float
     *
     * @ORM\Column(name="customerSalesAmount", type="float",  nullable=true)
     */
    private $customerSalesAmount = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(name="purchasePrice", type="float", nullable=true)
     */
    private $purchasePrice = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="subTotal", type="float", nullable=true)
     */
    private $subTotal = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=50, nullable=true)
     */
    private $process ='Created';


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

    /**
     * @return mixed
     */
    public function getSalesBy()
    {
        return $this->salesBy;
    }

    /**
     * @param mixed $salesBy
     */
    public function setSalesBy($salesBy)
    {
        $this->salesBy = $salesBy;
    }

    /**
     * @return string
     */
    public function getParticular()
    {
        return $this->particular;
    }

    /**
     * @param string $particular
     */
    public function setParticular($particular)
    {
        $this->particular = $particular;
    }

    /**
     * @return float
     */
    public function getStartMeterReading()
    {
        return $this->startMeterReading;
    }

    /**
     * @param float $startMeterReading
     */
    public function setStartMeterReading($startMeterReading)
    {
        $this->startMeterReading = $startMeterReading;
    }

    /**
     * @return float
     */
    public function getEndMeterReading()
    {
        return $this->endMeterReading;
    }

    /**
     * @param float $endMeterReading
     */
    public function setEndMeterReading($endMeterReading)
    {
        $this->endMeterReading = $endMeterReading;
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
    public function getCustomerSalesQuantity()
    {
        return $this->customerSalesQuantity;
    }

    /**
     * @param float $customerSalesQuantity
     */
    public function setCustomerSalesQuantity($customerSalesQuantity)
    {
        $this->customerSalesQuantity = $customerSalesQuantity;
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
    public function getCustomerSalesAmount()
    {
        return $this->customerSalesAmount;
    }

    /**
     * @param float $customerSalesAmount
     */
    public function setCustomerSalesAmount($customerSalesAmount)
    {
        $this->customerSalesAmount = $customerSalesAmount;
    }

    /**
     * @return BusinessBatch
     */
    public function getBusinessBatch()
    {
        return $this->businessBatch;
    }

    /**
     * @param BusinessBatch $businessBatch
     */
    public function setBusinessBatch($businessBatch)
    {
        $this->businessBatch = $businessBatch;
    }






}

