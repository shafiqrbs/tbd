<?php

namespace Modules\NbrVatTax\App\Entities;

use App\Entity\Admin\SettingType;
use App\Entity\Application\Inventory;
use App\Entity\Application\Nbrvat;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BusinessParticular
 *
 * @ORM\Table( name = "nbr_taxsetup")
 * @ORM\Entity()
 */
class TaxSetup
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;


    /**
     * @ORM\ManyToOne(targetEntity="Setting", inversedBy="taxSetups")
     **/
    private $tax;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $inputMode = "dropdown";

    /**
     * @var string
     *
     * @ORM\Column(type="text",nullable=true)
     */
    private $inputValue ="0|0";


     /**
     * @var string
     *
     * @ORM\Column(type="string",nullable=true)
     */
    private $taxMode = "percent";


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

