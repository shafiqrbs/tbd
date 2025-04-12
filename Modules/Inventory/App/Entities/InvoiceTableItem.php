<?php

namespace Modules\Inventory\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * RestaurantTableInvoiceItem
 *
 * @ORM\Table( name = "inv_invoice_table_item")
 * @ORM\Entity()
 */
class InvoiceTableItem
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
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $stockItem;


    /**
     * @ORM\ManyToOne(targetEntity="InvoiceTable", cascade={"detach","merge"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $invoice;


    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float")
     */
    private $quantity = 1;

    /**
     * @var float
     *
     * @ORM\Column(name="sales_price", type="float")
     */
    private $salesPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="purchase_price", type="float", nullable=true)
     */
    private $purchasePrice;


    /**
     * @var boolean
     *
     * @ORM\Column(name="custom_price", type="boolean")
     */
    private $customPrice = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_print", type="boolean")
     */
    private $isPrint = true;

    /**
     * @var float
     *
     * @ORM\Column(name="sub_total", type="float")
     */
    private $subTotal;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

}

