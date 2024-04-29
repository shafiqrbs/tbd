<?php

namespace Modules\Inventory\App\Entities;

use Modules\Core\App\Entities\Vendor;
use Modules\Core\App\Entities\User;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * BusinessInvoiceReturn
 *
 * @ORM\Table( name ="inv_sales_return")
 * @ORM\Entity()
 */
class SalesReturn
{
    /**
     * @var integer
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
     * @ORM\OneToOne(targetEntity="Sales")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $sales;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Accounting\App\Entities\AccountHead")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $customer;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $updatedAt;



    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;

     /**
     * @var string
     *
     * @ORM\Column(name="invoice", type="string",  nullable=true)
     */
    private $invoice;

    /**
     * @var string
     *
     * @ORM\Column(name="mode", type="string",  nullable=true)
     */
    private $mode = "adjustment";

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $subTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="adjustment", type="float", nullable=true)
     */
    private $adjustment=0;

    /**
     * @var float
     *
     * @ORM\Column(name="payment", type="float", nullable=true)
     */
    private $payment=0;

    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", nullable=true)
     */
    private $process = "created";


}

