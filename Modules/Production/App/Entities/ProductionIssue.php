<?php

namespace Modules\Production\App\Entities;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;



/**
 * SalesItem
 *
 * @ORM\Table(name ="pro_issue")
 * @ORM\Entity(repositoryClass="Modules\Production\App\Repositories\ProductionIssueRepository")
 */
class ProductionIssue
{

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Setting")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $factory;


     /**
     *
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Warehouse")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $wearhouse;


     /**
     *
     * @ORM\ManyToOne(targetEntity="ProductionBatch")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $productionBatch;

    /**
     * @var float
     *
     * @ORM\Column(type="float",  nullable=true)
     */
    private $amount;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=50, nullable=true)
     */
    private $process = "In-progress";

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $approvedBy;


    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $issueDate;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;


}

