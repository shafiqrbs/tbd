<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BusinessWearHouse
 *
 * @ORM\Table( name="inv_wearhouse")
 * @ORM\Entity()
 */
class WearHouse
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
     * @ORM\Column(name="shortCode", type="string", length=5, nullable=true)
     */
    private $shortCode;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=50, nullable=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=10, nullable=true)
     */
    private $code;

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $wearHouseCode;

    /**
     * @var int
     *
     * @ORM\Column(name="sorting", type="smallint",  length=2, nullable=true)
     */
    private $sorting = 0;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;

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


}

