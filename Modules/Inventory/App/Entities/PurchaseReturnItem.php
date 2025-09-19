<?php

namespace Modules\Inventory\App\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BusinessPurchaseReturnItem
 *
 * @ORM\Table(name ="inv_purchase_return_item")
 * @ORM\Entity()
 *
 */
class PurchaseReturnItem
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
     * @ORM\ManyToOne(targetEntity="PurchaseReturn")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $purchaseReturn;


    /**
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $stockItem;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Warehouse")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $warehouse;

    /**
     * @ORM\ManyToOne(targetEntity="PurchaseItem")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $purchaseItem;

    /**
     * @var string
     * @ORM\Column(name="item_name", type="string", nullable=true)
     */
    private $itemName;

    /**
     * @var string
     * @ORM\Column(name="uom", type="string", nullable=true)
     */
    private $uom;


    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $quantity;

     /**
     * @var integer
     * @ORM\Column(type="integer",name="spoil_qnt",nullable=true)
     */
    private $spoilQnt;


     /**
     * @var integer
     * @ORM\Column(type="integer",name="damage_qnt",nullable=true)
     */
    private $damageQnt;

    /**
     * @var integer
     * @ORM\Column(type="integer",name="deliver_qnt",nullable=true)
     */
    private $deliverQnt;


     /**
     * @var integer
     * @ORM\Column(type="integer",name="remaining_qnt",nullable=true)
     */
    private $remainingQnt;


     /**
     * @var integer
     * @ORM\Column(type="float",name="sales_invoice_item",nullable=true)
     */
    private $salesInvoiceItem;

    /**
     * @var float
     * @ORM\Column(type="float",name="purchase_price",nullable=true)
     */
    private $purchasePrice;

    /**
     * @var float
     * @ORM\Column(type="float",name="sub_total",nullable=true)
     */
    private $subTotal;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $updatedAt;


}

