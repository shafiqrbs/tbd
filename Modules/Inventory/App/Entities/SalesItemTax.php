<?php

namespace Modules\Inventory\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * BusinessInvoiceParticular
 *
 * @ORM\Table( name = "inv_sales_item_tax")
 * @ORM\Entity()
 */
class SalesItemTax
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
     * @ORM\OneToOne(targetEntity="SalesItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $salesItem;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\NbrVatTax\App\Entities\Setting")
     **/
    private  $supplyOutputTax;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\NbrVatTax\App\Entities\Setting")
     **/
    private  $nbrSupplyOutputTax;


    /**
     * @var float
     *
     * @ORM\Column(name="customsDuty", type="float", nullable=true)
     */
    private $customsDuty = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="customsDutyPercent", type="float", nullable=true)
     */
    private $customsDutyPercent = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="supplementaryDuty", type="float", nullable=true)
     */
    private $supplementaryDuty = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(name="supplementaryDutyPercent", type="float", nullable=true)
     */
    private $supplementaryDutyPercent = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(name="valueAddedTax", type="float", nullable=true)
     */
    private $valueAddedTax = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="valueAddedTaxPercent", type="float", nullable=true)
     */
    private $valueAddedTaxPercent = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceIncomeTax", type="float", nullable=true)
     */
    private $advanceIncomeTax = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceIncomeTaxPercent", type="float", nullable=true)
     */
    private $advanceIncomeTaxPercent = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceTax", type="float", nullable=true)
     */
    private $advanceTax = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceTaxPercent", type="float", nullable=true)
     */
    private $advanceTaxPercent = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="recurringDeposit", type="float", nullable=true)
     */
    private $recurringDeposit = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(name="recurringDepositPercent", type="float", nullable=true)
     */
    private $recurringDepositPercent = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="regulatoryDuty", type="float", nullable=true)
     */
    private $regulatoryDuty = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(name="regulatoryDutyPercent", type="float", nullable=true)
     */
    private $regulatoryDutyPercent = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceTradeVat", type="float", nullable=true)
     */
    private $advanceTradeVat = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceTradeVatPercent", type="float", nullable=true)
     */
    private $advanceTradeVatPercent = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="totalTaxIncidence", type="float", nullable=true)
     */
    private $totalTaxIncidence = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="rebatePercent", type="float", nullable=true)
     */
    private $rebatePercent = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(name="vatRefundForSales", type="float", nullable=true)
     */
    private $vatRefundForSales = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $rebateSd = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $rebateVat = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $rebateAt = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="rebate", type="float", nullable=true)
     */
    private $rebate = 0.00;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable = true)
     */
    private $vdsApplicable;


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


}

