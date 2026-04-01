<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BusinessDamage
 *
 * @ORM\Table("inv_damage_item")
 * @ORM\Entity()
 */
class DamageItem
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
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Damage")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $damage;

    /**
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $stockItem;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\PurchaseItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $purchaseItem;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\SalesItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $salesItem;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Warehouse")
     * @ORM\OrderBy({"sorting" = "ASC"})
     **/
    private $warehouse;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float",nullable=true)
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(name="purchase_price", type="float",nullable=true)
     */
    private $purchasePrice;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_total", type="float",nullable=true)
     */
    private $subTotal;

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

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="string",  nullable=true)
     */
    private $notes;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true,options={"default"="Damage"})
     */
    private $damage_mode;

    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", nullable=true,options={"default"="Created"})
     */
    private $process = "Created";


}

