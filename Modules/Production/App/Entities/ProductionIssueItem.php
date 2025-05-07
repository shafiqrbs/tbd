<?php

namespace Modules\Production\App\Entities;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;



/**
 * SalesItem
 *
 * @ORM\Table(name="pro_issue_item")
 * @ORM\Entity()
 */
class ProductionIssueItem
{

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $stockItem;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionIssue")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $productionIssue;


    /**
     *
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Warehouse")
     * @ORM\JoinColumn(name="product_warehouse_id",onDelete="CASCADE")
     **/
    private  $warehouse;



    /**
     * @var string
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

    /**
     * @var float
     * @ORM\Column(name="quantity", type="float",nullable=true)
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="uom", type="string", nullable=true)
     */
    private $uom;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=50, nullable=true)
     */
    private $process = "In-progress";

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $purchasePrice;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesPrice;


    /**
     * @var DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $issueDate;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;


}

