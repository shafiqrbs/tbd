<?php

namespace Modules\Production\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ProductionWorkOrderItem
 *
 * @ORM\Table(name ="pro_batch_item")
 * @ORM\Entity(repositoryClass="Modules\Production\App\Repositories\ProductionBatchItemRepository")
 */
class ProductionBatchItem
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
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionItem", inversedBy="productionBatchItems" )
     **/
    private  $productionItem;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Item")
     **/
    private  $item;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionWorkOrderItem", inversedBy="productionWorkOrderItems" )
     **/
    private  $workorderItem;

    /**
     * @ORM\OneToMany(targetEntity="Modules\Production\App\Entities\ProductionReceiveBatchItem", mappedBy="batchItem" )
     **/
    private  $receiveItems;

    /**
     * @ORM\OneToMany(targetEntity="Modules\Inventory\App\Entities\SalesItem", mappedBy="productionBatchItem"))
     **/
    private  $salesItems;

    /**
     * @ORM\OneToMany(targetEntity="Modules\Production\App\Entities\ProductionExpense", mappedBy="productionBatchItem" )
     **/
    private  $productionExpenses;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionBatch", inversedBy="batchItems")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $batch;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $issueQuantity;


     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $price;


     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $receiveQuantity;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $damageQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $returnQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $remainingQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesReturnQuantity;

     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesDamageQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $stockQuantity;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $effectedDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $openingDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $issueDate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $status = "valid";

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $mode = "";

    /**
     * @var string
     *
     * @ORM\Column(type="string" , nullable=true)
     */
    private $process = 'created';

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="updated_at", type="datetime", nullable = true)
     */
    private $updatedAt;




}

