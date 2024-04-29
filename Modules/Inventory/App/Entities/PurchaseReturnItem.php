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
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $product;

    /**
     * @ORM\ManyToOne(targetEntity="WearHouse")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $wearHouse;



    /**
     * @var integer
     *
     * @ORM\Column(type="integer",nullable=true)
     */
    private $quantity;

     /**
     * @var integer
     *
     * @ORM\Column(type="integer",nullable=true)
     */
    private $spoilQnt;


     /**
     * @var integer
     *
     * @ORM\Column(type="integer",nullable=true)
     */
    private $damageQnt;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer",nullable=true)
     */
    private $deliverQnt;


     /**
     * @var integer
     *
     * @ORM\Column(type="integer",nullable=true)
     */
    private $remainingQnt;


     /**
     * @var integer
     *
     * @ORM\Column(type="float",nullable=true)
     */
    private $salesInvoiceItem;

    /**
     * @var float
     *
     * @ORM\Column(type="float",nullable=true)
     */
    private $purchasePrice;

    /**
     * @var float
     *
     * @ORM\Column(type="float",nullable=true)
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

