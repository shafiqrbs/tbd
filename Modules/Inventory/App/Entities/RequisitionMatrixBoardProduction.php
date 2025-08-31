<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * RequisitionMatrixBoardProduction
 *
 * @ORM\Table(name ="inv_requisition_production_item_matrix")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\RequisitionMatrixBoardProductionRepository")
 */
class RequisitionMatrixBoardProduction
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
     * @ORM\ManyToOne(targetEntity="RequisitionBoard" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(name="requisition_board_id", onDelete="CASCADE")
     **/
    private  $requisitionBoard;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\Config" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(name="config_id", onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $item;

    /**
     * @var float
     * @ORM\Column(name="name", type="string",nullable=true)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $proItem;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionBatchItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $proBatchItem;


    /**
     * @var float
     * @ORM\Column(name="quantity", type="float",nullable=true)
     */
    private $quantity;



    /**
     * @var float
     * @ORM\Column(name="stock_quantity", type="float",nullable=true)
     */
    private $stockQuantity;

    /**
     * @var float
     * @ORM\Column(name="demand_quantity", type="float",nullable=true)
     */
    private $demandQuantity;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $updatedAt;
}

