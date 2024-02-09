<?php

namespace Appstore\Bundle\BusinessBundle\Entity;

use Appstore\Bundle\AccountingBundle\Entity\AccountVendor;
use Core\UserBundle\Entity\User;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * BusinessPurchaseReturn
 *
 * @ORM\Table( name ="business_purchase_return")
 * @ORM\Entity(repositoryClass="Appstore\Bundle\BusinessBundle\Repository\BusinessPurchaseReturnRepository")
 */
class BusinessPurchaseReturn
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessConfig", inversedBy="businessPurchasesReturns" , cascade={"detach","merge"} )
     **/
    private  $businessConfig;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\AccountingBundle\Entity\AccountVendor", inversedBy="businessPurchasesReturns" , cascade={"detach","merge"} )
     **/
    private  $vendor;

    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessPurchaseReturnItem", mappedBy="businessPurchaseReturn")
     **/
    private  $businessPurchaseReturnItems;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User")
     **/
    private  $createdBy;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice", type="string", length=255, nullable=true)
     */
    private $invoice;

    /**
     * @var integer
     *
     * @ORM\Column(name="salesInvoice", type="integer", nullable=true)
     */
    private $salesInvoice;

     /**
     * @var integer
     *
     * @ORM\Column(name="purchaseInvoice", type="integer", nullable=true)
     */
    private $purchaseInvoice;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;

    /**
     * @var float
     *
     * @ORM\Column(name="subTotal", type="float", nullable=true)
     */
    private $subTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="spoilQuantity", type="float", nullable=true)
     */
    private $spoilQuantity;

    /**
     * @var float
     *
     * @ORM\Column(name="damageQuantity", type="float", nullable=true)
     */
    private $damageQuantity;

     /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float", nullable=true)
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", nullable=true)
     */
    private $process = "created";

     /**
     * @var boolean
     *
     * @ORM\Column(name="mode", type="boolean", nullable=true)
     */
    private $mode = false;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }


    /**
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return string
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param string $process
     * created
     * progress
     * complete
     * approved
     */
    public function setProcess($process)
    {
        $this->process = $process;
    }


    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }



    /**
     * @return float
     */
    public function getSubTotal()
    {
        return $this->subTotal;
    }

    /**
     * @param float $subTotal
     */
    public function setSubTotal($subTotal)
    {
        $this->subTotal = $subTotal;
    }

    /**
     * @return string
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param string $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return AccountVendor
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param AccountVendor $vendor
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
    }

    /**
     * @return mixed
     */
    public function getBusinessConfig()
    {
        return $this->businessConfig;
    }

    /**
     * @param mixed $businessConfig
     */
    public function setBusinessConfig($businessConfig)
    {
        $this->businessConfig = $businessConfig;
    }

    /**
     * @return mixed
     */
    public function getBusinessPurchaseReturnItems()
    {
        return $this->businessPurchaseReturnItems;
    }

    /**
     * @param mixed $businessPurchaseReturnItems
     */
    public function setBusinessPurchaseReturnItems($businessPurchaseReturnItems)
    {
        $this->businessPurchaseReturnItems = $businessPurchaseReturnItems;
    }

    /**
     * @return int
     */
    public function getSalesInvoice()
    {
        return $this->salesInvoice;
    }

    /**
     * @param int $salesInvoice
     */
    public function setSalesInvoice($salesInvoice)
    {
        $this->salesInvoice = $salesInvoice;
    }

    /**
     * @return int
     */
    public function getPurchaseInvoice()
    {
        return $this->purchaseInvoice;
    }

    /**
     * @param int $purchaseInvoice
     */
    public function setPurchaseInvoice($purchaseInvoice)
    {
        $this->purchaseInvoice = $purchaseInvoice;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param float $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return float
     */
    public function getDamageQuantity()
    {
        return $this->damageQuantity;
    }

    /**
     * @param float $damageQuantity
     */
    public function setDamageQuantity($damageQuantity)
    {
        $this->damageQuantity = $damageQuantity;
    }

    /**
     * @return float
     */
    public function getSpoilQuantity()
    {
        return $this->spoilQuantity;
    }

    /**
     * @param float $spoilQuantity
     */
    public function setSpoilQuantity($spoilQuantity)
    {
        $this->spoilQuantity = $spoilQuantity;
    }

    /**
     * @return bool
     */
    public function isMode()
    {
        return $this->mode;
    }

    /**
     * @param bool $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }


}

