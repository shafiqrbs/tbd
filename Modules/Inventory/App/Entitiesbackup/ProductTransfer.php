<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BusinessWearHouse
 *
 * @ORM\Table( name ="inv_product_transfer")
 * @ORM\Entity()
 */
class ProductTransfer
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Product")
     **/
    private $product;


	/**
	 * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\WearHouse")
	 **/
	private $fromWearHouse;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\WearHouse")
     **/
    private $toWearHouse;


    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $approvedBy;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer", length=10, nullable=true)
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="remark", type="text",nullable=true)
     */
    private $remark;

    /**
     * @var string
     *
     * @ORM\Column(name="process", type="text",nullable=true)
     */
    private $process="Created";

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;

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


    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;



}

