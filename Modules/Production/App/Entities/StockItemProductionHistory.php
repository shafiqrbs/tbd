<?php

namespace Modules\Production\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * StockItemProductionHistory
 *
 * @ORM\Table( name="pro_stock_production_history")
 * @ORM\Entity()
 */
class StockItemProductionHistory
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
     * @ORM\OneToOne(targetEntity="Modules\Inventory\App\Entities\StockItemHistory")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $stockItemHistory;

    /**
     * @ORM\ManyToOne(targetEntity="ProductionIssue")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionIssue;

    /**
     * @ORM\ManyToOne(targetEntity="ProductionIssueItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionIssueItem;

    /**
     * @ORM\ManyToOne(targetEntity="ProductionInventory")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionInventoryReturn;


    /**
     * @ORM\ManyToOne(targetEntity="ProductionBatchItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionBatchItem;

    /**
     * @ORM\ManyToOne(targetEntity="ProductionBatch")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionBatch;

    /**
     * @ORM\ManyToOne(targetEntity="ProductionBatchItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionBatchItemReturn;

    /**
     * @ORM\ManyToOne(targetEntity="ProductionReceiveBatchItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionReceiveItem;

    /**
     * @ORM\ManyToOne(targetEntity="ProductionReceiveBatch")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionReceive;

    /**
     * @ORM\ManyToOne(targetEntity="ProductionExpense")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionExpense;

    /**
     * @ORM\ManyToOne(targetEntity="ProductionExpense")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected  $productionExpenseReturn;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $productionIssueQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $productionInventoryReturnQuantity= 0.00;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $productionBatchItemQuantity= 0.00;


    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $productionBatchItemReturnQuantity= 0.00;


    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $productionExpenseQuantity= 0.00;


    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $productionExpenseReturnQuantity = 0.00;


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

