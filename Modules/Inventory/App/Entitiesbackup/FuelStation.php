<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * FuelStation
 *
 * @ORM\Table( name ="inv_fuelstation")
 * @ORM\Entity()
 */
class FuelStation
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config", cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\WearHouse")
     **/
    private $waerhouse;

    /**
     * @ORM\ManyToOne(targetEntity="Product")
     **/
    private $particular;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private $salesBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private $verifiedBy;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private $approvedBy;


     /**
     * @var string
     *
     * @ORM\Column(name="process", type="string",nullable=true)
     */
     private $process = 'New';

     /**
     * @var float
     *
     * @ORM\Column(name="price", type="float",nullable=true)
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(name="total", type="float",nullable=true)
     */
    private $total;

     /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float",nullable=true)
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(name="startReader", type="float",nullable=true)
     */
    private $startReader;

    /**
     * @var float
     *
     * @ORM\Column(name="endReader", type="float",nullable=true)
     */
    private $endReader;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime",nullable=true)
     */
    private $created;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime",nullable=true)
     */
    private $updated;

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



}

