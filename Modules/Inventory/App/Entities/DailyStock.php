<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * StockDailyInventory
 *
 * @ORM\Table( name="inv_daily_stock")
 * @ORM\Entity()
 */
class DailyStock
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create_at")
     * @ORM\Column(type="date")
     */
    private $invDate;

    /**
     * @ORM\ManyToOne(targetEntity="Config" , cascade={"detach","merge"} )
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Warehouse")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $warehouse;

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
     * @var string
     * @ORM\Column(type="string", nullable = true)
     */
    protected $uom;

    /**
     * @var float
     * @ORM\Column(name="price", type="float", nullable = true,options={"default":0})
     */
    private $price = 0;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $purchasePrice = 0;


    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $salesPrice = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $actualPrice = 0;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $discountPrice = 0;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $openingQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $stockTransferIn= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $stockTransferOut= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $openingBalance= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $productionQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $purchaseQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $salesReturnQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $assetInQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $totalInQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $salesQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $damageQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $purchaseReturnQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $productionExpenseQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $assetOutQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $totalOutQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $closingQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true,options={"default":0})
     */
    private $closingBalance= 0.00;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create_at")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update_at")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

}

