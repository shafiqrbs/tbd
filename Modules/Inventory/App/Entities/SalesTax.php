<?php

namespace Modules\Inventory\App\Entities;



use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Sales
 *
 * @ORM\Table( name ="inv_sales_tax")
 * @ORM\Entity()
 */
class SalesTax
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;

    /**
     * @ORM\OneToOne(targetEntity="Sales")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $sales;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $discountType ='';

    /**
     * @var float
     *
     * @ORM\Column(type="float" , nullable=true)
     */
    private $discountCalculation;


    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable=true)
     */
    private $subTotal;


    /**
     * @var float
     *
     * @ORM\Column(name="discount", type="float", nullable=true)
     */
    private $discount;

    /**
     * @var float
     *
     * @ORM\Column(name="vat", type="float", nullable=true)
     */
    private $vat;

     /**
     * @var float
     *
     * @ORM\Column(name="ait", type="float", nullable=true)
     */
    private $ait;

    /**
     * @var float
     *
     * @ORM\Column(name="total", type="float", nullable=true)
     */
    private $total;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $tloPrice;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $srCommission;


    /**
     * @var float
     *
     * @ORM\Column(name="payment", type="float", nullable=true)
     */
    private $payment;

    /**
     * @var float
     *
     * @ORM\Column(name="received", type="float", nullable=true)
     */
    private $received;

    /**
     * @var float
     *
     * @ORM\Column(name="commission", type="float", nullable=true)
     */
    private $commission;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $billingAddress;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $shippingAddress;

    /**
     * @var string
     * @ORM\Column(name="lcNo", type="string", length = 50, nullable=true)
     */
    private $lcNo;

    /**
     * @var string
     * @ORM\Column(type="string", length = 50, nullable=true)
     */
    private $transportInfo;


    /**
     * @var string
     * @ORM\Column(type="string", length = 50, nullable=true)
     */
    private $billOfEntryNo;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $billOfEntryDate;


    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $vehicleInfo;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $clearForwardingFirm;


    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lcDate;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $totalQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $totalItem = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="customsDuty", type="float", nullable=true)
     */
    private $customsDuty = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="supplementaryDuty", type="float", nullable=true)
     */
    private $supplementaryDuty = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(name="valueAddedTax", type="float", nullable=true)
     */
    private $valueAddedTax = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceIncomeTax", type="float", nullable=true)
     */
    private $advanceIncomeTax = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="regulatoryDuty", type="float", nullable=true)
     */
    private $regulatoryDuty = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceTradeVat", type="float", nullable=true)
     */
    private $advanceTradeVat = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceTax", type="float", nullable=true)
     */
    private $advanceTax = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="taxTariffCalculation", type="float", nullable=true)
     */
    private $taxTariffCalculation = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="totalTaxIncidence", type="float", nullable=true)
     */
    private $totalTaxIncidence = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="taxableVat", type="float", nullable=true)
     */
    private $taxableVat = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", nullable=true)
     */
    private $amount = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="rebate", type="float", nullable=true)
     */
    private $rebate = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="vatDeductionSource", type="float", nullable=true)
     */
    private $vatDeductionSource = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="vdsAmount", type="float", nullable=true)
     */
    private $vdsAmount = 0;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
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
    public function getSales()
    {
        return $this->sales;
    }

    /**
     * @param mixed $sales
     */
    public function setSales($sales)
    {
        $this->sales = $sales;
    }

    /**
     * @return string
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }

    /**
     * @param string $discountType
     */
    public function setDiscountType($discountType)
    {
        $this->discountType = $discountType;
    }

    /**
     * @return float
     */
    public function getDiscountCalculation()
    {
        return $this->discountCalculation;
    }

    /**
     * @param float $discountCalculation
     */
    public function setDiscountCalculation($discountCalculation)
    {
        $this->discountCalculation = $discountCalculation;
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
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * @return float
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @param float $vat
     */
    public function setVat($vat)
    {
        $this->vat = $vat;
    }

    /**
     * @return float
     */
    public function getAit()
    {
        return $this->ait;
    }

    /**
     * @param float $ait
     */
    public function setAit($ait)
    {
        $this->ait = $ait;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return float
     */
    public function getTloPrice()
    {
        return $this->tloPrice;
    }

    /**
     * @param float $tloPrice
     */
    public function setTloPrice($tloPrice)
    {
        $this->tloPrice = $tloPrice;
    }

    /**
     * @return float
     */
    public function getSrCommission()
    {
        return $this->srCommission;
    }

    /**
     * @param float $srCommission
     */
    public function setSrCommission($srCommission)
    {
        $this->srCommission = $srCommission;
    }

    /**
     * @return float
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param float $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return float
     */
    public function getReceived()
    {
        return $this->received;
    }

    /**
     * @param float $received
     */
    public function setReceived($received)
    {
        $this->received = $received;
    }

    /**
     * @return float
     */
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * @param float $commission
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;
    }

    /**
     * @return string
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param string $billingAddress
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return string
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param string $shippingAddress
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @return string
     */
    public function getLcNo()
    {
        return $this->lcNo;
    }

    /**
     * @param string $lcNo
     */
    public function setLcNo($lcNo)
    {
        $this->lcNo = $lcNo;
    }

    /**
     * @return string
     */
    public function getTransportInfo()
    {
        return $this->transportInfo;
    }

    /**
     * @param string $transportInfo
     */
    public function setTransportInfo($transportInfo)
    {
        $this->transportInfo = $transportInfo;
    }

    /**
     * @return string
     */
    public function getBillOfEntryNo()
    {
        return $this->billOfEntryNo;
    }

    /**
     * @param string $billOfEntryNo
     */
    public function setBillOfEntryNo($billOfEntryNo)
    {
        $this->billOfEntryNo = $billOfEntryNo;
    }

    /**
     * @return \DateTime
     */
    public function getBillOfEntryDate()
    {
        return $this->billOfEntryDate;
    }

    /**
     * @param \DateTime $billOfEntryDate
     */
    public function setBillOfEntryDate($billOfEntryDate)
    {
        $this->billOfEntryDate = $billOfEntryDate;
    }

    /**
     * @return string
     */
    public function getVehicleInfo()
    {
        return $this->vehicleInfo;
    }

    /**
     * @param string $vehicleInfo
     */
    public function setVehicleInfo($vehicleInfo)
    {
        $this->vehicleInfo = $vehicleInfo;
    }

    /**
     * @return string
     */
    public function getClearForwardingFirm()
    {
        return $this->clearForwardingFirm;
    }

    /**
     * @param string $clearForwardingFirm
     */
    public function setClearForwardingFirm($clearForwardingFirm)
    {
        $this->clearForwardingFirm = $clearForwardingFirm;
    }

    /**
     * @return \DateTime
     */
    public function getLcDate()
    {
        return $this->lcDate;
    }

    /**
     * @param \DateTime $lcDate
     */
    public function setLcDate($lcDate)
    {
        $this->lcDate = $lcDate;
    }

    /**
     * @return float
     */
    public function getTotalQuantity()
    {
        return $this->totalQuantity;
    }

    /**
     * @param float $totalQuantity
     */
    public function setTotalQuantity($totalQuantity)
    {
        $this->totalQuantity = $totalQuantity;
    }

    /**
     * @return float
     */
    public function getTotalItem()
    {
        return $this->totalItem;
    }

    /**
     * @param float $totalItem
     */
    public function setTotalItem($totalItem)
    {
        $this->totalItem = $totalItem;
    }

    /**
     * @return float
     */
    public function getCustomsDuty()
    {
        return $this->customsDuty;
    }

    /**
     * @param float $customsDuty
     */
    public function setCustomsDuty($customsDuty)
    {
        $this->customsDuty = $customsDuty;
    }

    /**
     * @return float
     */
    public function getSupplementaryDuty()
    {
        return $this->supplementaryDuty;
    }

    /**
     * @param float $supplementaryDuty
     */
    public function setSupplementaryDuty($supplementaryDuty)
    {
        $this->supplementaryDuty = $supplementaryDuty;
    }

    /**
     * @return float
     */
    public function getValueAddedTax()
    {
        return $this->valueAddedTax;
    }

    /**
     * @param float $valueAddedTax
     */
    public function setValueAddedTax($valueAddedTax)
    {
        $this->valueAddedTax = $valueAddedTax;
    }

    /**
     * @return float
     */
    public function getAdvanceIncomeTax()
    {
        return $this->advanceIncomeTax;
    }

    /**
     * @param float $advanceIncomeTax
     */
    public function setAdvanceIncomeTax($advanceIncomeTax)
    {
        $this->advanceIncomeTax = $advanceIncomeTax;
    }

    /**
     * @return float
     */
    public function getRegulatoryDuty()
    {
        return $this->regulatoryDuty;
    }

    /**
     * @param float $regulatoryDuty
     */
    public function setRegulatoryDuty($regulatoryDuty)
    {
        $this->regulatoryDuty = $regulatoryDuty;
    }

    /**
     * @return float
     */
    public function getAdvanceTradeVat()
    {
        return $this->advanceTradeVat;
    }

    /**
     * @param float $advanceTradeVat
     */
    public function setAdvanceTradeVat($advanceTradeVat)
    {
        $this->advanceTradeVat = $advanceTradeVat;
    }

    /**
     * @return float
     */
    public function getAdvanceTax()
    {
        return $this->advanceTax;
    }

    /**
     * @param float $advanceTax
     */
    public function setAdvanceTax($advanceTax)
    {
        $this->advanceTax = $advanceTax;
    }

    /**
     * @return float
     */
    public function getTaxTariffCalculation()
    {
        return $this->taxTariffCalculation;
    }

    /**
     * @param float $taxTariffCalculation
     */
    public function setTaxTariffCalculation($taxTariffCalculation)
    {
        $this->taxTariffCalculation = $taxTariffCalculation;
    }

    /**
     * @return float
     */
    public function getTotalTaxIncidence()
    {
        return $this->totalTaxIncidence;
    }

    /**
     * @param float $totalTaxIncidence
     */
    public function setTotalTaxIncidence($totalTaxIncidence)
    {
        $this->totalTaxIncidence = $totalTaxIncidence;
    }

    /**
     * @return float
     */
    public function getTaxableVat()
    {
        return $this->taxableVat;
    }

    /**
     * @param float $taxableVat
     */
    public function setTaxableVat($taxableVat)
    {
        $this->taxableVat = $taxableVat;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getRebate()
    {
        return $this->rebate;
    }

    /**
     * @param float $rebate
     */
    public function setRebate($rebate)
    {
        $this->rebate = $rebate;
    }

    /**
     * @return float
     */
    public function getVatDeductionSource()
    {
        return $this->vatDeductionSource;
    }

    /**
     * @param float $vatDeductionSource
     */
    public function setVatDeductionSource($vatDeductionSource)
    {
        $this->vatDeductionSource = $vatDeductionSource;
    }

    /**
     * @return float
     */
    public function getVdsAmount()
    {
        return $this->vdsAmount;
    }

    /**
     * @param float $vdsAmount
     */
    public function setVdsAmount($vdsAmount)
    {
        $this->vdsAmount = $vdsAmount;
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





}

