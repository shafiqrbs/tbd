<?php

namespace Appstore\Bundle\BusinessBundle\Entity;

use Appstore\Bundle\AccountingBundle\Entity\AccountVendor;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * BusinessVendorStock
 *
 * @ORM\Table(name="business_vendor_stock")
 * @ORM\Entity(repositoryClass="Appstore\Bundle\BusinessBundle\Repository\BusinessVendorStockRepository")
 */
class BusinessVendorStock
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
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessConfig", inversedBy="businessVendorStocks" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $businessConfig;


     /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\AccountingBundle\Entity\AccountVendor", inversedBy="businessVendorStocks")
     **/
    private $vendor;


    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessVendorStockItem", mappedBy="businessVendorStock", cascade={"remove"}))
     **/
    private $businessVendorStockItems;

    /**
     * @var float
     *
     * @ORM\Column(name="subTotal", type="float", nullable=true)
     */
    private $subTotal;

     /**
     * @var float
     *
     * @ORM\Column(name="stockIn", type="float", nullable=true)
     */
    private $stockIn;

    /**
     * @var float
     *
     * @ORM\Column(name="stockOut", type="float", nullable=true)
     */
    private $stockOut;

    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", nullable=true)
     */
    private $process = "Created";

    /**
     * @var string
     *
     * @ORM\Column(name="grn", type="string", nullable=true)
     */
    private $grn;

    /**
     * @var float
     *
     * @ORM\Column(name="commission", type="float", nullable=true)
     */
    private $commission = 50;

    /**
     * @var string
     *
     * @ORM\Column(name="remark", type="text", nullable=true)
     */
    private $remark;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;


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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    


    /**
     * @return BusinessConfig
     */
    public function getBusinessConfig()
    {
        return $this->businessConfig;
    }

    /**
     * @param BusinessConfig $businessConfig
     */
    public function setBusinessConfig($businessConfig)
    {
        $this->businessConfig = $businessConfig;
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
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
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
     * @return BusinessVendorStockItem
     */
    public function getBusinessVendorStockItems()
    {
        return $this->businessVendorStockItems;
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
    public function setProcess($process)
    {
        $this->process = $process;
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
    public function getGrn()
    {
        return $this->grn;
    }

    /**
     * @param string $grn
     */
    public function setGrn($grn)
    {
        $this->grn = $grn;
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
    public function setRemark($remark)
    {
        $this->remark = $remark;
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
     * @return float
     */
    public function getStockOut()
    {
        return $this->stockOut;
    }

    /**
     * @param float $stockOut
     */
    public function setStockOut($stockOut)
    {
        $this->stockOut = $stockOut;
    }

    /**
     * @return float
     */
    public function getStockIn()
    {
        return $this->stockIn;
    }

    /**
     * @param float $stockIn
     */
    public function setStockIn($stockIn)
    {
        $this->stockIn = $stockIn;
    }

    /**
     * @return float
     */
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * @param float $commission
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;
    }

}

