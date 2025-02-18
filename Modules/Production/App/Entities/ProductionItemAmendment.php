<?php

namespace Modules\Production\App\Entities;

use App\Entity\Application\Production;
use Modules\Core\App\Entities\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Damage
 *
 * @ORM\Table("pro_item_amendment")
 * @ORM\Entity(repositoryClass="Modules\Production\App\Repositories\ProductionItemAmendmentRepository")
 */
class ProductionItemAmendment
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
     * @ORM\ManyToOne(targetEntity="Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $item;

    /**
     * @ORM\ManyToOne(targetEntity="ProductionItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $productionItem;


    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $checkedBy;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $approvedBy;


    /**
     * @var string
     *
     * @ORM\Column(type="string" , nullable=true)
     */
    private $process = 'created';


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string" , nullable=true)
     */
    private $name;


    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime" , nullable=true)
     */
    private $licenseDate;


    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime" , nullable=true)
     */
    private $initiateDate;


    /**
     * @var string
     *
     * @ORM\Column(name="uom", type="string", nullable=true)
     */
    private $uom;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $remark;


     /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $issueBy;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $designation;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $wastePercent = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $wasteAmount = 0;


     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $materialAmount = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $materialQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $wasteMaterialQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $valueAddedAmount = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable = true)
     */
    private $subTotal = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float" , nullable=true)
     */
    private $quantity;


    /**
     * @var float
     *
     * @ORM\Column(type="float" , nullable=true)
     */
    private $issueQuantity;

    /**
     * @var float
     *
     * @ORM\Column(type="float" , nullable=true)
     */
    private $returnQuantity;


    /**
     * @var float
     *
     * @ORM\Column(type="float" , nullable=true)
     */
    private $damageQuantity;


    /**
     * @var float
     *
     * @ORM\Column(type="float" , nullable=true)
     */
    private $reminigQuantity;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" , nullable=true)
     */
    private $status = false;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     * @ORM\Column(name="updated", type="datetime", nullable = true)
     */
    private $updated;


    /**
     * Get id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getUom()
    {
        return $this->uom;
    }

    /**
     * @param string $uom
     */
    public function setUom($uom)
    {
        $this->uom = $uom;
    }

    /**
     * @return ProductionElement
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName( $name)
    {
        $this->name = $name;
    }


    /**
     * @return float
     */
    public function getMaterialAmount()
    {
        return $this->materialAmount;
    }

    /**
     * @param float $materialAmount
     */
    public function setMaterialAmount($materialAmount)
    {
        $this->materialAmount = $materialAmount;
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
     * @return mixed
     */
    public function getProductionValueAddeds()
    {
        return $this->productionValueAddeds;
    }

    /**
     * @param mixed $productionValueAddeds
     */
    public function setProductionValueAddeds($productionValueAddeds)
    {
        $this->productionValueAddeds = $productionValueAddeds;
    }

    /**
     * @return float
     */
    public function getWastePercent()
    {
        return $this->wastePercent;
    }

    /**
     * @param float $wastePercent
     */
    public function setWastePercent($wastePercent)
    {
        $this->wastePercent = $wastePercent;
    }

    /**
     * @return float
     */
    public function getWasteAmount()
    {
        return $this->wasteAmount;
    }

    /**
     * @param float $wasteAmount
     */
    public function setWasteAmount($wasteAmount)
    {
        $this->wasteAmount = $wasteAmount;
    }


    /**
     * @return \DateTime
     */
    public function getInitiateDate()
    {
        return $this->initiateDate;
    }

    /**
     * @param \DateTime $initiateDate
     */
    public function setInitiateDate(\DateTime $initiateDate)
    {
        $this->initiateDate = $initiateDate;
    }

    /**
     * @return \DateTime
     */
    public function getLicenseDate()
    {
        return $this->licenseDate;
    }

    /**
     * @param \DateTime $licenseDate
     */
    public function setLicenseDate( $licenseDate)
    {
        $this->licenseDate = $licenseDate;
    }

    /**
     * @return float
     */
    public function getValueAddedAmount()
    {
        return $this->valueAddedAmount;
    }

    /**
     * @param float $valueAddedAmount
     */
    public function setValueAddedAmount(float $valueAddedAmount)
    {
        $this->valueAddedAmount = $valueAddedAmount;
    }

    /**
     * @return float
     */
    public function getMaterialQuantity()
    {
        return $this->materialQuantity;
    }

    /**
     * @param float $materialQuantity
     */
    public function setMaterialQuantity(float $materialQuantity)
    {
        $this->materialQuantity = $materialQuantity;
    }

    /**
     * @return float
     */
    public function getWasteMaterialQuantity()
    {
        return $this->wasteMaterialQuantity;
    }

    /**
     * @param float $wasteMaterialQuantity
     */
    public function setWasteMaterialQuantity($wasteMaterialQuantity)
    {
        $this->wasteMaterialQuantity = $wasteMaterialQuantity;
    }

    /**
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param string $remark
     */
    public function setRemark( $remark)
    {
        $this->remark = $remark;
    }

    /**
     * @return string
     */
    public function getIssueBy()
    {
        return $this->issueBy;
    }

    /**
     * @param string $issueBy
     */
    public function setIssueBy( $issueBy)
    {
        $this->issueBy = $issueBy;
    }

    /**
     * @return string
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * @param string $designation
     */
    public function setDesignation( $designation)
    {
        $this->designation = $designation;
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
    public function setQuantity( $quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return float
     */
    public function getIssueQuantity()
    {
        return $this->issueQuantity;
    }

    /**
     * @param float $issueQuantity
     */
    public function setIssueQuantity( $issueQuantity)
    {
        $this->issueQuantity = $issueQuantity;
    }

    /**
     * @return float
     */
    public function getReturnQuantity()
    {
        return $this->returnQuantity;
    }

    /**
     * @param float $returnQuantity
     */
    public function setReturnQuantity( $returnQuantity)
    {
        $this->returnQuantity = $returnQuantity;
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
    public function setDamageQuantity( $damageQuantity)
    {
        $this->damageQuantity = $damageQuantity;
    }

    /**
     * @return float
     */
    public function getReminigQuantity()
    {
        return $this->reminigQuantity;
    }

    /**
     * @param float $reminigQuantity
     */
    public function setReminigQuantity( $reminigQuantity)
    {
        $this->reminigQuantity = $reminigQuantity;
    }

    /**
     * @return ProductionItem
     */
    public function getProductionItem()
    {
        return $this->productionItem;
    }

    /**
     * @param ProductionItem $productionItem
     */
    public function setProductionItem($productionItem)
    {
        $this->productionItem = $productionItem;
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
     */
    public function setProcess(string $process)
    {
        $this->process = $process;
    }

    /**
     * @return bool
     */
    public function isStatus()
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
     * @return User
     */
    public function getCheckedBy()
    {
        return $this->checkedBy;
    }

    /**
     * @param User $checkedBy
     */
    public function setCheckedBy($checkedBy)
    {
        $this->checkedBy = $checkedBy;
    }

    /**
     * @return User
     */
    public function getApprovedBy()
    {
        return $this->approvedBy;
    }

    /**
     * @param User $approvedBy
     */
    public function setApprovedBy($approvedBy)
    {
        $this->approvedBy = $approvedBy;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;
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
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }



}

