<?php

namespace Modules\NbrVatTax\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * BusinessStockHistory
 *
 * @ORM\Table( name="nbr_stock_vat_history")
 * @ORM\Entity()
 */
class StockItemVatHistory
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
     * @ORM\OneToOne(targetEntity="Modules\Inventory\App\Entities\StockItemHistory")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $stockItemHistory;

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
     * @ORM\Column( type="float", nullable=true)
     */
    private $customsDutyPercent = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $supplementaryDuty = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $supplementaryDutyPercent = 0.00;

    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable=true)
     */
    private $valueAddedTax = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $valueAddedTaxPercent = 0.00;


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
     * @ORM\Column(type="float", nullable=true)
     */
    private $advanceIncomeTaxPercent = 0.00;


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
    private $recurringDepositPercent = 0.00;


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
    private $advanceTradeVatPercent = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $rebatePercent = 0.00;


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

}

