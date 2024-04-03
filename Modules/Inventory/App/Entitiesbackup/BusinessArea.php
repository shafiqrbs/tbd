<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Setting\Bundle\LocationBundle\Entity\Location;

/**
 * BusinessWearHouse
 *
 * @ORM\Table( name ="inv_area")
 * @ORM\Entity()
 */
class BusinessArea
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config", inversedBy="area" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Location", inversedBy="area")
     **/
    private $location;

    /**
     * @ORM\OneToMany(targetEntity="Sales", mappedBy="area")
      **/
    private $sales;

    /**
     * @ORM\OneToMany(targetEntity="Modules\Inventory\App\Entities\BusinessStore", mappedBy="area")
      **/
    private $stores;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;



}

