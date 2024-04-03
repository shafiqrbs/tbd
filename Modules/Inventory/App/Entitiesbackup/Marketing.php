<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BusinessWearHouse
 *
 * @ORM\Table( name ="inv_marketing")
 * @ORM\Entity()
 */
class Marketing
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="designation", type="string", length=100, nullable=true)
     */
    private $designation;

    /**
     * @var string
     *
     * @ORM\Column( type="string", length=100, nullable=true)
     */
    private $mobileNo;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $companyName;

     /**
     * @var float
     *
     * @ORM\Column( type="float",nullable=true)
     */
    private $monthlySales;

    /**
     * @var float
     *
     * @ORM\Column( type="float",nullable=true)
     */
    private $yearlySales;

     /**
     * @var float
     *
     * @ORM\Column(name="discount", type="float",nullable=true)
     */
    private $discount;

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

