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
 * @ORM\Table(name ="inv_requisition_item")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\RequisitionItemRepository")
 */
class RequisitionItem
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
     * @ORM\ManyToOne(targetEntity="Requisition")
     * @ORM\JoinColumn(name="requisition_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private  $requisition;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(name="vendor_config_id", onDelete="CASCADE")
     **/
    private  $vendorConfig;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(name="customer_config_id", onDelete="CASCADE")
     **/
    private  $customerConfig;

    /**
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $vendorStockItem;

    /**
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $customerStockItem;

    /**
     * @var string
     * @ORM\Column(name="barcode", type="string",  nullable = true)
     */
    private $barcode;

    /**
     * @var string
     * @ORM\Column(name="unit_name", type="string",  nullable = true)
     */
    private $unitName;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private  $unit;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Warehouse")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $warehouse;


    /**
     * @var float
     * @ORM\Column(name="quantity", type="float",nullable=true)
     */
    private $quantity;

    /**
     * @var float
     * @ORM\Column(name="display_name", type="string",nullable=true)
     */
    private $displayName;

    /**
     * @var float
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchasePrice;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesPrice;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $subTotal;

	/**
	 * @var boolean
	 * @ORM\Column(name="status", type="boolean", nullable=true)
	 */
	private $status=false;

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
}

