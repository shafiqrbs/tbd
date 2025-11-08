<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * MedicineDamage
 *
 * @ORM\Table("inv_stock_transfer")
 * @ORM\Entity()
 */
class StockTransfer
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
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $uid;

    /**
     * @var string
     * @ORM\Column(name="invoice", type="string", length=255, nullable=true)
     */
    private $invoice;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config")
     **/
    private $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Warehouse")
     **/
    private  $fromWarehouse;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Warehouse")
     **/
    private  $toWarehouse;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $approvedBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $issuedBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $receivedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="string",  nullable=true)
     */
    private $notes;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="process", type="string", nullable=true)
	 */
	private $process = "Created";


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
     * @var \DateTime
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $approvedDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $issuedDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $receivedDate;





}

