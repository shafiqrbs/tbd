<?php

namespace Modules\Inventory\App\Entities;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;



/**
 * SalesItem
 *
 * @ORM\Table(name ="inv_production_issue")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\ProductionIssueRepository")
 */
class ProductionIssue
{

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;

    /**
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="productionItems" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $item;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="uom", type="string", nullable=true)
     */
    private $uom;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=50, nullable=true)
     */
    private $process = "In-progress";


    /**`
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer",nullable=true)
     */
    private $quantity;


    /**
     * @var integer
     *
     * @ORM\Column(type="integer",nullable=true)
     */
    private $salesReturnQuantity;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $purchasePrice;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $actualSalesPrice;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesPrice;


    /**
     * @var float
     *
     * @ORM\Column(name="length", type="float", nullable=true)
     */
    private $length;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $subQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $totalQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer", nullable = true)
     */
    private $code;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bonusQuantity = 0;


    /**
     * @var string
     *
     * @ORM\Column(name="barcode", type="string",  nullable = true)
     */
    private $barcode;


    /**
     * @var float
     *
     * @ORM\Column(name="height", type="float", nullable=true)
     */
    private $height;


    /**
     * @var float
     *
     * @ORM\Column(name="width", type="float", nullable=true)
     */
    private $width;


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
     * @ORM\Column(type="float", nullable=true)
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
     * @ORM\Column(type="float", nullable=true)
     */
    private $advanceTax = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $advanceTaxPercent = 0.00;


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
     * @ORM\Column(type="float", nullable=true)
     */
    private $regulatoryDuty = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $regulatoryDutyPercent = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $advanceTradeVat = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $advanceTradeVatPercent = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $totalTaxIncidence = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $rebatePercent = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="rebate", type="float", nullable=true)
     */
    private $rebate = 0.00;


    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable = true)
     */
    private $subTotal;


    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable = true)
     */
    private $purchaseTotal;


    /**
     * @var float
     *
     * @ORM\Column(name="total", type="float", nullable = true)
     */
    private $total;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $issueDate;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;


}

