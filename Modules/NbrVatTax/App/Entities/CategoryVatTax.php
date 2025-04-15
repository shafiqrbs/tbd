<?php

namespace Modules\NbrVatTax\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * ItemVat
 *
 * @ORM\Table( name="nbr_category_vat_tax")
 * @ORM\Entity()
 */
class CategoryVatTax
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
     * @ORM\OneToOne(targetEntity="Modules\Inventory\App\Entities\Category")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $category;

    /**
     * @ORM\ManyToOne(targetEntity="TaxTariff")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $hscode;


    /**
     * @ORM\ManyToOne(targetEntity="Setting")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $taxReturnNote;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $customsDuty = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $supplementaryDuty = 0.00;

    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable=true)
     */
    private $valueAddedTax = 0.00;

    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable=true)
     */
    private $advanceTax = 0.00;

    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable=true)
     */
    private $advanceIncomeTax = 0.00;

    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable=true)
     */
    private $recurringDeposit = 0.00;

    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable=true)
     */
    private $advanceTradeVat = 0.00;

    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable=true)
     */
    private $rebate = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $totalTaxIncidence = 0.00;


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
    public function getHscode()
    {
        return $this->hscode;
    }

    /**
     * @param mixed $hscode
     */
    public function setHscode($hscode)
    {
        $this->hscode = $hscode;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getTaxReturnNote()
    {
        return $this->taxReturnNote;
    }

    /**
     * @param mixed $taxReturnNote
     */
    public function setTaxReturnNote($taxReturnNote)
    {
        $this->taxReturnNote = $taxReturnNote;
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
    public function getRecurringDeposit()
    {
        return $this->recurringDeposit;
    }

    /**
     * @param float $recurringDeposit
     */
    public function setRecurringDeposit($recurringDeposit)
    {
        $this->recurringDeposit = $recurringDeposit;
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

