<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoiceTable
 *
 * @ORM\Table( name = "inv_invoice_table")
 * @ORM\Entity()
 */
class InvoiceTable
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
     * @ORM\ManyToOne(targetEntity="Config", cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $createdBy;


    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private $table;

     /**
      * @ORM\Column(name="invoice_mode", type="string", length=50, nullable=true)
      **/
    private $invoiceMode;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $salesBy;


    /**
     * @var string
     * @ORM\Column(name="process", type="string", length=50, nullable=true)
     */
    private $process = "Free";

     /**
     * @var boolean
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     */
    private $isActive = false;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $serveBy;

    /**
     * @var \DateTime
     * @ORM\Column(name="order_date", type="datetime", nullable=true)
     */
    private $orderDate;


    /**
     * @var float
     * @ORM\Column(name="sub_total", type="float", nullable=true)
     */
    private $subTotal;

    /**
     * @var float
     * @ORM\Column(name="payment", type="float", nullable=true)
     */
    private $payment;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Accounting\App\Entities\TransactionMode" ,cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     **/
    private $transactionMode;


    /**
     * @var array
     * @ORM\Column(name="table_nos", type="text", nullable=true)
     */
    private $tableNos;


    /**
     * @var string
     *
     * @ORM\Column(name="discount_type", type="string", length=30, nullable=true)
     */
    private $discountType;

    /**
     * @var float
     *
     * @ORM\Column(name="total", type="float", nullable=true)
     */
    private $total;

     /**
     * @var float
     *
     * @ORM\Column(name="vat", type="float", nullable=true)
     */
    private $vat;

      /**
     * @var float
     *
     * @ORM\Column(name="sd", type="float", nullable=true)
     */
    private $sd;

     /**
     * @var float
     *
     * @ORM\Column(name="discount", type="float", nullable=true)
     */
    private $discount;

    /**
     * @var integer
     *
     * @ORM\Column(name="percentage", type="smallint" , length=3 , nullable=true)
     */
    private $percentage;

    /**
     * @var int
     *
     * @ORM\Column(name="discount_calculation", type="smallint", length = 2,  nullable=true)
     */
    private $discountCalculation;


    /**
     * @var string
     *
     * @ORM\Column(name="discount_coupon", type="string",  nullable=true)
     */
    private $discountCoupon;


    /**
     * @var string
     *
     * @ORM\Column(name="remark", type="text", length=255, nullable=true)
     */
    private $remark;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime",nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="updated_at", type="datetime",nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Customer" ,cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     **/
    private $customer;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }



}

