<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * StockItemPriceMatrix
 *
 * @ORM\Table( name = "inv_stock_item_price_matrix")
 * @ORM\Entity()
 */
class  StockItemPriceMatrix
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
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumn(name="stock_item_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $stockItem;


    /**
     * @ORM\ManyToOne(targetEntity="setting")
     * @ORM\JoinColumn(name="price_unit_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $priceUnit;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $price = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;


}

