<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BusinessStockHistory
 *
 * @ORM\Table( name = "inv_stock_history")
 * @ORM\Entity()
 */
class StockHistory
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config" , cascade={"detach","merge"} )
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="stockHistory" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $item;

     /**
     * @ORM\ManyToOne(targetEntity="PurchaseItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $purchaseItem;


     /**
      * @var integer
      * @ORM\Column(type="integer",nullable=true)
     **/
    private $purchaseReturnItem;


    /**
     * @ORM\ManyToOne(targetEntity="SalesItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $salesItem;


    /**
     * @var integer
     * @ORM\Column(type="integer",nullable=true)
     **/
    private $salesReturnItem;


    /**
     * @var integer
     * @ORM\Column(type="integer",nullable=true)
     **/
    private $damageItem;


    /**
     * @var integer
     * @ORM\Column(type="integer",nullable=true)
     **/
    private $marketing;

    /**
     * @var integer
     * @ORM\Column(type="integer",nullable=true)
     **/
    private $wearHouse;


    /**
     * @var integer
     * @ORM\Column(type="integer",nullable=true)
     **/
    private $itemTransfer;


    /**
     * @var integer
     * @ORM\Column(type="integer",nullable=true)
     **/
    private  $createdBy;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $process;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $opening = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $quantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $openingQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $closingQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchaseQuantity = 0;


     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchaseReturnQuantity = 0;


     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $salesQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $transferQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $salesReturnQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $damageQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $bonusQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $bonusSalesQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $spoilQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $salesPrice = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchasePrice = 0;

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

