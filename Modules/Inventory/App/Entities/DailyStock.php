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
     * @ORM\Column(name="price", type="float", nullable = true)
     */
    private $price = 0;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $purchasePrice = 0;


    /**
     * @var float
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
     * @ORM\Column(type="float", nullable = true)
     */
    private $discountPrice = 0;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $openingQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $openingBalance= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $productionQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $purchaseQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesReturnQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $assetInQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $totalInQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $damageQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $purchaseReturnQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $productionExpenseQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $assetOutQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $totalOutQuantity = 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $closingQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
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

