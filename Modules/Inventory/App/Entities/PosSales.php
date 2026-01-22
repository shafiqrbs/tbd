<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BusinessWearHouse
 *
 * @ORM\Table( name="inv_pos_sales")
 * @ORM\Entity()
 */
class PosSales
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
     * @ORM\Column( type="string",nullable = true)
     */
    private $deviceId;

    /**
     * @var string
     * @ORM\Column( type="string",nullable = true)
     */
    private $syncBatchId;

    /**
     * @var string
     * @ORM\Column( type="json",nullable = true)
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $createdBy;

    /**
     * @var boolean
     * @ORM\Column(name="process", type="string",length=20, options={"default"="new"})
     */
    private $process;

    /**
     * @var boolean
     * @ORM\Column(name="status", type="boolean", options={"default"="false"})
     */
    private $status;

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
     * @var integer
     * @ORM\Column( type="integer",nullable = true)
     */
    private $total;


    /**
     * @var integer
     * @ORM\Column( type="integer",nullable = true)
     */
    private $failed;


}

