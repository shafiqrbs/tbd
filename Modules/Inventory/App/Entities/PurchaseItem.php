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
     * @ORM\Column(name="quantity", type="float")
     */
    private $quantity;



    /**
     * @var integer
     *
     * @ORM\Column(name="salesQuantity", type="integer",nullable=true)
     */
    private $salesQuantity = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="salesReturnQuantity", type="integer",nullable=true)
     */
    private $salesReturnQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="salesReplaceQuantity", type="integer",nullable=true)
     */
    private $salesReplaceQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="purchaseReturnQuantity", type="integer",nullable=true)
     */
    private $purchaseReturnQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="damageQuantity", type="integer",nullable=true)
     */
    private $damageQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="bonusQuantity", type="integer", nullable=true)
     */
    private $bonusQuantity = 0;


    /**
     * @var integer
     *
     * @ORM\Column(name="remainingQuantity", type="integer",nullable=true)
     */
    private $remainingQuantity;


    /**
     * @var float
     *
     * @ORM\Column(name="purchasePrice", type="float")
     */
    private $purchasePrice;


    /**
     * @var float
     *
     * @ORM\Column(name="actualPurchasePrice", type="float")
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
     * @ORM\Column(name="salesPrice", type="float", nullable = true)
     */
    private $salesPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="purchaseSubTotal", type="float", nullable = true)
     */
    private $purchaseSubTotal;


    /**
     * @var string
     *
     * @ORM\Column(name="barcode", type="string",  nullable = true)
     */
    private $barcode;


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
	 * @ORM\Column(name="particularType", type="float", nullable=true)
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
	 * @ORM\Column(name="subQuantity", type="float", nullable=true)
	 */
	private $subQuantity;

	/**
	 * @var float
	 *
	 * @ORM\Column(name="totalQuantity", type="float", nullable=true)
	 */
	private $totalQuantity;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="status", type="boolean")
	 */
	private $status=false;


}

