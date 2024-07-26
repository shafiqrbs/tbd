<?php

namespace Modules\Production\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * ProductionElement
 *
 * @ORM\Table(name ="pro_element")
 * @ORM\Entity(repositoryClass="Modules\Production\App\Repositories\ProductionElementRepository")
 */
class ProductionElement
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
     * @ORM\ManyToOne(targetEntity="Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="ProductionItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $productionItem;

     /**
     * @ORM\ManyToOne(targetEntity="ProductionItemAmendment")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $productionItemAmendment;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $material;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $materialQuantity;


    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float")
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $uom;


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
    private $price;


    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable = true)
     */
    private $subTotal;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $wastageQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $totalQuantity;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $wastagePercent;


    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable = true)
     */
    private $wastageAmount;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $wastageSubTotal;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", options={"default"="false"}, nullable=true)
     */
    private $status = true;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime", nullable = true)
     */
    private $updatedAt;



}

