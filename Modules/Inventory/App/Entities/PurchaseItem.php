<?php

namespace Modules\Inventory\App\Entities;

use Modules\Inventory\App\Entities\Product;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Utility\App\Entities\ProductUnit;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * PurchaseItem
 *
 * @ORM\Table(name ="inv_purchase_item")
 * @ORM\Entity()
 */
class PurchaseItem
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
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $approvedBy;

    /**
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $product;

    /**
     * @ORM\ManyToOne(targetEntity="Purchase")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $purchase;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\ProductUnit")
     **/
    private  $unit;

    /**
     * @ORM\ManyToOne(targetEntity="WearHouse")
     **/
    private  $wearhouse;

    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float",nullable=true)
     */
    private $quantity;

     /**
     * @var float
     *
     * @ORM\Column( type="float",nullable=true)
     */
    private $openingQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="float",nullable=true)
     */
    private $salesQuantity = 0;

    /**
     * @var integer
     *
     * @ORM\Column( type="float",nullable=true)
     */
    private $salesReturnQuantity;

    /**
     * @var integer
     *
     * @ORM\Column( type="float",nullable=true)
     */
    private $salesReplaceQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="float",nullable=true)
     */
    private $purchaseReturnQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="float",nullable=true)
     */
    private $damageQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $bonusQuantity = 0;


    /**
     * @var integer
     *
     * @ORM\Column( type="float",nullable=true)
     */
    private $remainingQuantity;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchasePrice;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $actualPurchasePrice;


    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer", nullable = true)
     */
    private $code;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesPrice;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $subTotal;


    /**
     * @var string
     *
     * @ORM\Column(name="barcode", type="string",  nullable = true)
     */
    private $barcode;


    /**
     * @var string
     *
     * @ORM\Column(type="string",  nullable = true)
     */
    private $mode="purchase";


	/**
	 * @var float
	 *
	 * @ORM\Column(name="height", type="float", nullable=true)
	 */
	private $height;


	/**
	 * @var float
	 *
	 * @ORM\Column(name="width", type="float", nullable=true)
	 */
	private $width;


	/**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $particularType;


	/**
	 * @var float
	 *
	 * @ORM\Column(name="length", type="float", nullable=true)
	 */
	private $length;


	/**
	 * @var float
	 *
	 * @ORM\Column( type="float", nullable=true)
	 */
	private $subQuantity;

	/**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $totalQuantity;

	/**
	 * @var boolean
	 *
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
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;




}

