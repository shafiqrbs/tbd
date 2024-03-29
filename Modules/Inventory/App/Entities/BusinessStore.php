<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Courier
 *
 * @ORM\Table( name ="inv_store")
 * @ORM\Entity()
 */
class BusinessStore
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config",cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Customer")
     **/
    private $customer;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Marketing")
     **/
    private $marketing;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessArea")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $area;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $mobileNo;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", nullable=true)
     */
    private $address;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime",  nullable=true)
     */
    private $joiningDate;

    /**
     * @var float
     *
     * @ORM\Column(name="balance", type="float",  nullable = true)
     */
    private $balance;



    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;

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



}

