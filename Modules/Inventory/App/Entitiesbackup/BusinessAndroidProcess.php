<?php

namespace Modules\Inventory\App\Entities;

use Modules\Core\App\Entities\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Utility\App\Entities\AndroidDeviceSetup;

/**
 * BusinessAndroidProcess
 *
 * @ORM\Table(name="inv_android_process")
 * @ORM\Entity()
 */
class BusinessAndroidProcess
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config", inversedBy="androidProcess")
     */
    protected $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\AndroidDeviceSetup", inversedBy="businessAndroidProcess" )
     **/
    private  $androidDevice;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User", inversedBy="businessAndroidProcess" )
     **/
    private  $createdBy;



    /**
     * @ORM\OneToMany(targetEntity="Sales", mappedBy="androidProcess" )
     **/
    private  $sales;



   /**
     * @ORM\OneToMany(targetEntity="Modules\Inventory\App\Entities\BusinessPurchase", mappedBy="androidProcess" )
     **/
    private  $purchase;

    /**
     * @var string
     *
     * @ORM\Column(name="ipAddress", type="string", length = 100, nullable=true)
     */
    private $ipAddress;

    /**
     * @var float
     *
     * @ORM\Column(name="subTotal", type="float", nullable=true)
     */
    private $subTotal = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="netTotal", type="float", nullable=true)
     */
    private $netTotal = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="received", type="float", nullable=true)
     */
    private $received = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="vat", type="float", nullable=true)
     */
    private $vat = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="discount", type="float", nullable=true)
     */
    private $discount = 0;



    /**
     * @var string
     *
     * @ORM\Column(name="jsonItem", type="text",  nullable=true)
     */
    private $jsonItem;


     /**
     * @var string
     *
     * @ORM\Column(name="jsonSubItem", type="text",  nullable=true)
     */
    private $jsonSubItem;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length = 30, nullable=true)
     */
    private $process = 'sales';

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    private $status = false;

    /**
     * @var integer
     *
     * @ORM\Column(name="itemCount", type="integer", nullable=true)
     */
    private $itemCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="subItemCount", type="integer", nullable=true)
     */
    private $subItemCount = 0;

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



}

