<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * MedicineDamage
 *
 * @ORM\Table("inv_stock_transfer_item")
 * @ORM\Entity()
 */
class StockTransferItem
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config")
     **/
    private $config;


    /**
     * @ORM\ManyToOne(targetEntity="StockTransfer")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $stockTransfer;


    /**
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $stockItem;


    /**
     * @ORM\ManyToOne(targetEntity="PurchaseItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $purchaseItem;


    /**
     * @var integer
     * @ORM\Column(name="quantity", type="integer",nullable=true)
     */
    private $quantity;

    /**
     * @var integer
     * @ORM\Column(name="request_quantity", type="integer",nullable=true)
     */
    private $requestQuantity;

    /**
     * @var integer
     * @ORM\Column(name="stock_quantity", type="integer",nullable=true)
     */
    private $stockQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="bonus", type="integer",nullable=true)
     */
    private $bonus;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="string",  nullable=true)
     */
    private $notes;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="process", type="string", nullable=true)
	 */
	private $process = "Created";

    /**
     * @var string
     *
     * @ORM\Column(name="mode", type="string", nullable=true)
     */
    private $mode = "minus";

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
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $uom;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;





}

