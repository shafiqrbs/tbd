<?php

namespace Modules\Inventory\App\Entities;

use Modules\Core\App\Entities\Vendor;
use Modules\Core\App\Entities\User;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * BusinessPurchaseReturn
 *
 * @ORM\Table( name ="inv_purchase_return")
 * @ORM\Entity()
 *
 */
class PurchaseReturn
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config")
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Vendor")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
     private  $vendor;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $approvedBy;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $issueBy;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice", type="string", length=255, nullable=true)
     */
    private $invoice;

    /**
     * @var integer
     *
     * @ORM\Column(name="sales_invoice", type="integer", nullable=true)
     */
    private $salesInvoice;

     /**
     * @var integer
     *
     * @ORM\Column(name="purchase_invoice", type="integer", nullable=true)
     */
    private $purchaseInvoice;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;

    /**
     * @var float
     *
     * @ORM\Column(name="sub_total", type="float", nullable=true)
     */
    private $subTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="spoil_quantity", type="float", nullable=true)
     */
    private $spoilQuantity;

    /**
     * @var float
     *
     * @ORM\Column(name="damage_quantity", type="float", nullable=true)
     */
    private $damageQuantity;

     /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float", nullable=true)
     */
    private $quantity;

    /**
     * @var string
     * @ORM\Column(name="process", type="string", nullable=true)
     */
    private $process;

    /**
     * @var string
     * @ORM\Column(name="return_type", type="string")
     */
    private $returnType;

    /**
     * @var string
     * @ORM\Column(name="narration", type="string", nullable=true)
     */
    private $narration;

     /**
     * @var boolean
     *
     * @ORM\Column(name="mode", type="boolean", nullable=true)
     */
    private $mode = false;

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
     * @var \Date
     * @ORM\Column(type="date", nullable=true)
     */
    private $invoiceDate;

}

