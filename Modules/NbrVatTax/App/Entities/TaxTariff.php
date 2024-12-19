<?php

namespace Modules\NbrVatTax\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;



/**
 * TaxTariff
 *
 * @ORM\Table("nbr_tax_tariff")
 * @ORM\Entity()
 */
class TaxTariff
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", length=255)
     */
    private $name;


    /**
     * @var float
     *
     * @ORM\Column(name="customs_duty", type="float", nullable=true)
     */
    private $customsDuty = 0.00;


     /**
     * @var float
     *
     * @ORM\Column(name="supplementary_duty", type="float", nullable=true)
     */
    private $supplementaryDuty = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(name="value_added_tax", type="float", nullable=true)
     */
    private $valueAddedTax = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advance_income_tax", type="float", nullable=true)
     */
    private $advanceIncomeTax = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="recurring_deposit", type="float", nullable=true)
     */
    private $recurringDeposit = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name= "regulatory_duty", type="float", nullable=true)
     */
    private $regulatoryDuty = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advance_trade_vat", type="float", nullable=true)
     */
    private $advanceTradeVat = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advance_tax", type="float", nullable=true)
     */
    private $advanceTax = 0.00;



    /**
     * @var float
     *
     * @ORM\Column(name="total_tax_incidence", type="float", nullable=true)
     */
    private $totalTaxIncidence = 0.00;



    /**
     * @Gedmo\Slug(fields={"name"})
     * @Doctrine\ORM\Mapping\Column(length=255)
     */
    private $slug;


    /**
     * @var integer
     *
     * @ORM\Column(name="hs_code", type="string", length=50, nullable = true)
     */
    private $hsCode;



    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status = true;

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

