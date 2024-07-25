<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * BusinessStockHistory
 *
 * @ORM\Table( name="inv_stock_history")
 * @ORM\Entity()
 */
class StockItemHistory
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
     * @ORM\ManyToOne(targetEntity="Config" , cascade={"detach","merge"} )
     **/
    private  $config;



     /**
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $stockItem;


    /**
     * @var string
     * @ORM\Column(type="string", nullable = true)
     */
    protected $itemName;


    /**
     * @ORM\ManyToOne(targetEntity="PurchaseItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $purchaseItem;

    /**
     * @ORM\ManyToOne(targetEntity="Purchase")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $purchase;

    /**
     * @ORM\ManyToOne(targetEntity="PurchaseReturnItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $purchaseReturnItem;

    /**
     * @ORM\ManyToOne(targetEntity="PurchaseReturn")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $purchaseReturn;

    /**
     * @ORM\ManyToOne(targetEntity="Sales", inversedBy="stockItems")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $sales;

    /**
     * @ORM\ManyToOne(targetEntity="SalesItem", inversedBy="stockItems")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $salesItem;

    /**
     * @ORM\ManyToOne(targetEntity="SalesReturnItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $salesReturnItem;

    /**
     * @ORM\ManyToOne(targetEntity="SalesReturn")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $salesReturn;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Setting")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $wearhouse;


    /**
     * @var string
     * @ORM\Column(type="string", nullable = true)
     */
    protected $damage;


    /**
     * @var string
     * @ORM\Column(type="string", nullable = true)
     */
    protected $createdBy;


    /**
     * @var string
     * @ORM\Column(type="string", nullable = true)
     */
    protected $vendor;

    /**
     * @var string
     * @ORM\Column(type="string", nullable = true)
     */
    protected $brand;


    /**
     * @var string
     * @ORM\Column(type="string", nullable = true)
     */
    private  $category;


    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable = true)
     */
    private $price = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $purchasePrice = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesPrice = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $actualPrice = 0;



     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $discountPrice = 0;



    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $subTotal = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="total", type="float", nullable = true)
     */
    private $total = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float", nullable = true)
     */
    private $quantity= 0.00;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $openingQuantity= 0.00;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $openingBalance= 0.00;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $closingQuantity= 0.00;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $closingBalance= 0.00;


    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $purchaseQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $purchaseReturnQuantity = 0.00;


    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $branchIssueQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $branchIssueReturnQuantity= 0.00;


    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesReturnQuantity= 0.00;


    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $assetsQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $assetsReturnQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $damageQuantity = 0.00;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", nullable = true)
     */
    private $process;

    /**
     * @var string
     *
     * @ORM\Column(name="mode", type="string", length = 40, nullable = true)
     */
    private $mode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $remark;

    /**
     * @var string
     *
     * @ORM\Column(name="serialNo", type="text", length=255, nullable = true)
     */
    private $serialNo;


    /**
     * @var DateTime
     *
     * @ORM\Column(name="expiredDate", type="datetime", nullable=true)
     */
    private $expiredDate;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=50, nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $sku;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="issueDate", type="datetime", nullable=true)
     */
    private $issueDate;



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

