<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * StockDailyInventory
 *
 * @ORM\Table( name="inv_current_stock")
 * @ORM\Entity()
 */
class CurrentStock
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
     *
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
     * @var float
     * @ORM\Column(type="float")
     */
    private $quantity= 0.00;

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

