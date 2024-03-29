<?php

namespace Modules\Inventory\App\Entities;

use Modules\Core\App\Entities\Vendor;
use Doctrine\ORM\Mapping as ORM;


/**
 * BusinessInvoiceParticular
 *
 * @ORM\Table( name = "inv_batch_particular")
 * @ORM\Entity()
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessBatch")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"id" = "ASC"})
     **/
    private $businessBatch;

    /**
     * @ORM\ManyToOne(targetEntity="Product")
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


}

