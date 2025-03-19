<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoiceTable
 *
 * @ORM\Table( name = "inv_location_printer_setup")
 * @ORM\Entity()
 */
class LocationPrinterSetup
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
     * @ORM\ManyToOne(targetEntity="Config", cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;


    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private $itemLocation;


    /**
     * @var string
     *
     * @ORM\Column(name="interface", type="string", nullable=true)
     */
    private $interface;


     /**
     * @var string
     *
     * @ORM\Column(name="printer_type", type="string", nullable=true)
     */
    private $printerType;


    /**
     * @var string
     *
     * @ORM\Column(name="type_tont", type="string", nullable=true)
     * TypeFontA|TypeFontB
     */
    private $typeFont;


     /**
     * @var boolean
     *
     * @ORM\Column(name="isActive", type="boolean", nullable=true)
     */
    private $isActive = false;

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


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }



}

