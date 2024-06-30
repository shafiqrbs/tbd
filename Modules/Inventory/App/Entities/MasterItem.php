<?php

namespace Modules\Inventory\App\Entities;


use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Terminalbd\NbrvatBundle\Entity\TaxTariff;

/**
 * Product
 *
 * @ORM\Table("inv_master_item")
 * @UniqueEntity(fields="name",message="Item name already existing,Please try another.")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\MasterItemRepository")
 */
class MasterItem
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;



    /**
	 * @ORM\ManyToOne(targetEntity="Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
	 **/
	private  $config;


    /**
	 * @ORM\ManyToOne(targetEntity="Particular")
     * @Assert\NotBlank(message="Item group not blank")
	 **/
	private  $productGroup;


    /**
	 * @ORM\ManyToOne(targetEntity="Category", inversedBy="items" )
	 **/
	private  $category;


	/**
	 * @ORM\ManyToOne(targetEntity="Particular", inversedBy="brandItems" )
	 **/
	private  $brand;

    /**
	 * @ORM\ManyToOne(targetEntity="Particular", inversedBy="typeItems" )
	 **/
	private  $productType;

    /**
	 * @ORM\ManyToOne(targetEntity="Particular")
	 **/
	private  $taxNature;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\ProductUnit")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $unit;

     /**
	 * @ORM\ManyToOne(targetEntity="Modules\NbrVatTax\App\Entities\Setting")
	 **/
	private  $supplyOutputTax;

    /**
	 * @ORM\ManyToOne(targetEntity="Modules\NbrVatTax\App\Entities\Setting")
	 **/
	private  $inputTax;


    /**
	 * @ORM\ManyToOne(targetEntity="Modules\NbrVatTax\App\Entities\Setting")
	 **/
	private  $inputImportTax;


    /**
	 * @ORM\ManyToOne(targetEntity="Particular")
	 **/
	private  $priceMethod;



    /**
     * @ORM\ManyToOne(targetEntity="Modules\NbrVatTax\App\Entities\TaxTariff")
     **/
    private  $taxTariff;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $appModule;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Product name must be required")
     */
    private $name;


    /**
     * @Gedmo\Slug(fields={"name"}, updatable=false, separator="-")
     * @ORM\Column(length=255, unique=true)
     */
    private $slug;


     /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $vatName;


     /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $hsCode;



    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $assuranceType;


     /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $warningLabel;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50,nullable = true)
     */
    private $sku;

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=100,nullable = true)
     */
    private $manufacture;


    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=50, nullable=true)
     */
    private $code;


    /**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $quantity;

	 /**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $unitPrice;

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
	private $salesPrice;

    /**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $discountPrice;

	/**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $minPrice;

    /**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $productionPrice;


    /**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $salesAvgPrice;


     /**
	 * @var float
	 *
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $purchaseAvgPrice;


    /**
	 * @var float
	 *
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $openingQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minQuantity = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $productionQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $productionReturnQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bonusQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bonusPurchaseQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bonusSalesQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $transferQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $spoilQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ongoingQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $orderCreateItem;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $remainingQuantity = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $purchaseQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $purchaseReturnQuantity=0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $salesQuantity = 0;

    /**
     * @var integer
     *
     * @ORM\Column( type="integer", nullable=true)
     */
    private $salesReturnQuantity = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $damageQuantity = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $disposalQuantity = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $assetsQuantity = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $assetsReturnQuantity = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $barcode;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $status = true;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $itemPrefix;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private $serialGeneration = 'auto';

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", length=5)
     */
    private $serialFormat = 2;

    /**
     * @var float
     *
     * @ORM\Column(name="customsDuty", type="float", nullable=true)
     */
    private $customsDuty = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $vatDeductionSource = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="supplementaryDuty", type="float", nullable=true)
     */
    private $supplementaryDuty = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="recurringDeposit", type="float", nullable=true)
     */
    private $recurringDeposit = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="valueAddedTax", type="float", nullable=true)
     */
    private $valueAddedTax = 0.00;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $minValueAddedTax = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceIncomeTax", type="float", nullable=true)
     */
    private $advanceIncomeTax = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceTradeVat", type="float", nullable=true)
     */
    private $advanceTradeVat = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="regulatoryDuty", type="float", nullable=true)
     */
    private $regulatoryDuty = 0.00;


    /**
     * @var float
     *
     * @ORM\Column(name="advanceTax", type="float", nullable=true)
     */
    private $advanceTax = 0.00;



    /**
     * @var float
     *
     * @ORM\Column(name="rebate", type="float", nullable=true)
     */
    private $rebate = 0.00;



    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @Assert\File(maxSize="8388608")
     */
    protected $file;



}

