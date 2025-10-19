<?php

namespace Modules\Inventory\App\Entities;

use Modules\Inventory\App\Entities\Product;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Utility\App\Entities\ProductUnit;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * RequisitionItem
 *
 * @ORM\Table(name ="inv_requisition_board")
 * @ORM\Entity()
 */
class RequisitionBoard
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $approvedBy;


    /**
     * @var string
     * @ORM\Column(type="string",  nullable = true)
     */
    private $batchNo;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;

    /**
     * @var string
     * @ORM\Column(name="total", type="string",  nullable = true)
     */
    private $total;


	/**
	 * @var boolean
	 * @ORM\Column(name="status", type="boolean", nullable=true)
	 */
	private $status = false;

    /**
     * @var string
     * @ORM\Column(name="process", type="string", nullable=true)
     */
    private $process;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $updatedAt;


    /**
     * @var \Date
     * @ORM\Column(type="date", nullable=true)
     */
    private $generateDate;

    /**
     * @var boolean
     * @ORM\Column(name="production_process", type="string", nullable=true)
     */
    private $productionProcess;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $productionApprovedBy;

    /**
     * @var \Date
     * @ORM\Column(type="date", nullable=true)
     */
    private $productionApprovedDate;

    /**
     * @var boolean
     * @ORM\Column(name="is_warehouse_board", type="boolean", options={"default"="false"})
     */
    private $isWarehouseBoard;

}

