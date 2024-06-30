<?php

namespace Modules\Production\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Production\App\Entities\ProductionWorkOrder;


/**
 * InvoiceKeyValue
 *
 * @ORM\Table(name="pro_config")
 * @ORM\Entity(repositoryClass="Modules\Production\App\Repositories\ConfigRepository")
 */
class Config
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
     * @var Integer
     *
     * @ORM\Column(name="sorting", type="smallint", length=2, nullable = true)
     */
    private $sorting;



}

