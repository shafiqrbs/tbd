<?php

namespace Modules\NbrVatTax\App\Entities;

use App\Entity\Admin\SettingType;
use App\Entity\Application\Inventory;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BusinessParticular
 *
 * @ORM\Table( name ="nbr_setting")
 * @ORM\Entity()
 */
class Setting
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
     private $config;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="nameBn", type="string", length=255, nullable=true)
     */
    private $nameBn;

    /**
     * @var string
     *
     * @ORM\Column(name="shortCode", type="string", length=50, nullable=true)
     */
    private $shortCode;

    /**
     * @var string
     *
     * @ORM\Column(type="string",nullable=true)
     */
    private $accountCode;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer",nullable=true)
     */
    private $noteNo;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=25, nullable=true)
     */
     private $mode;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true )
     */
    private $subForm = false;


    /**
     * @var boolean
     *
     * @ORM\Column(name="isInput", type="boolean" )
     */
    private $isInput = false;


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

