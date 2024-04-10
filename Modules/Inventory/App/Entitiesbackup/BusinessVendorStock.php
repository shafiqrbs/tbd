<?php

namespace Modules\Inventory\App\Entities;

use Modules\Core\App\Entities\Vendor;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * BusinessVendorStock
 *
 * @ORM\Table(name="inv_vendor_stock")
 * @ORM\Entity()
 */
class BusinessVendorStock
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
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Vendor")
     **/
    private $vendor;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $subTotal;

     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $stockIn;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $stockOut;

    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", nullable=true)
     */
    private $process = "Created";

    /**
     * @var string
     *
     * @ORM\Column(name="grn", type="string", nullable=true)
     */
    private $grn;

    /**
     * @var float
     *
     * @ORM\Column(name="commission", type="float", nullable=true)
     */
    private $commission = 50;

    /**
     * @var string
     *
     * @ORM\Column(name="remark", type="text", nullable=true)
     */
    private $remark;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;


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
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $updatedAt;


}

