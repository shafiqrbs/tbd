<?php

namespace Modules\Production\App\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * ProductionElement
 *
 * @ORM\Table(name ="pro_value_added")
 * @ORM\Entity(repositoryClass="Modules\Production\App\Repositories\ProductionValueAddedRepository")
 */
class ProductionValueAdded
{
    /**
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="ProductionItem", inversedBy="productionValueAddeds" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $productionItem;


     /**
     * @ORM\ManyToOne(targetEntity="ProductionItemAmendment", inversedBy="productionValueAddeds" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $productionItemAmendment;


    /**
     * @ORM\ManyToOne(targetEntity="Setting")
     **/
    private  $valueAdded;


    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", nullable = true)
     */
    private $amount = 0;

}

