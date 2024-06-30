<?php

namespace Modules\Production\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Production\App\Entities\ProductionWorkOrder;


/**
 * InvoiceKeyValue
 *
 * @ORM\Table(name="pro_key_value")
 * @ORM\Entity(repositoryClass="Modules\Production\App\Repositories\InvoiceKeyValueRepository")
 */
class InvoiceKeyValue
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
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;


    /**
     * @var $workorder ProductionWorkOrder
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionWorkOrder", inversedBy="invoiceKeyValues" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $workorder;


    /**
     * @var $batch ProductionBatch
     * @ORM\ManyToOne(targetEntity="Modules\Production\App\Entities\ProductionBatch", inversedBy="invoiceKeyValues" )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $batch;


    /**
     * @var string
     *
     * @ORM\Column(name="metaKey", type="string", length=255, nullable = true)
     */
    private $metaKey;

    /**
     * @var string
     *
     * @ORM\Column(name="metaValue", type="string", length=255 , nullable = true)
     */
    private $metaValue;

    /**
     * @var Integer
     *
     * @ORM\Column(name="sorting", type="smallint", length=2, nullable = true)
     */
    private $sorting;



}

