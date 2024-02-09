<?php

namespace Appstore\Bundle\BusinessBundle\Entity;

use Core\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Setting\Bundle\ToolBundle\Entity\AndroidDeviceSetup;

/**
 * BusinessAndroidProcess
 *
 * @ORM\Table(name="business_android_process")
 * @ORM\Entity(repositoryClass="Appstore\Bundle\BusinessBundle\Repository\BusinessAndroidProcessRepository")
 */
class BusinessAndroidProcess
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
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessConfig", inversedBy="androidProcess")
     */
    protected $businessConfig;

    /**
     * @ORM\ManyToOne(targetEntity="Setting\Bundle\ToolBundle\Entity\AndroidDeviceSetup", inversedBy="businessAndroidProcess" )
     **/
    private  $androidDevice;


    /**
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User", inversedBy="businessAndroidProcess" )
     **/
    private  $createdBy;



    /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessInvoice", mappedBy="androidProcess" )
     **/
    private  $sales;



   /**
     * @ORM\OneToMany(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessPurchase", mappedBy="androidProcess" )
     **/
    private  $purchase;

    /**
     * @var string
     *
     * @ORM\Column(name="ipAddress", type="string", length = 100, nullable=true)
     */
    private $ipAddress;

    /**
     * @var float
     *
     * @ORM\Column(name="subTotal", type="float", nullable=true)
     */
    private $subTotal = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="netTotal", type="float", nullable=true)
     */
    private $netTotal = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="received", type="float", nullable=true)
     */
    private $received = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="vat", type="float", nullable=true)
     */
    private $vat = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="discount", type="float", nullable=true)
     */
    private $discount = 0;



    /**
     * @var string
     *
     * @ORM\Column(name="jsonItem", type="text",  nullable=true)
     */
    private $jsonItem;


     /**
     * @var string
     *
     * @ORM\Column(name="jsonSubItem", type="text",  nullable=true)
     */
    private $jsonSubItem;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length = 30, nullable=true)
     */
    private $process = 'sales';

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    private $status = false;

    /**
     * @var integer
     *
     * @ORM\Column(name="itemCount", type="integer", nullable=true)
     */
    private $itemCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="subItemCount", type="integer", nullable=true)
     */
    private $subItemCount = 0;

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
     * @return AndroidDeviceSetup
     */
    public function getAndroidDevice()
    {
        return $this->androidDevice;
    }

    /**
     * @param AndroidDeviceSetup $androidDevice
     */
    public function setAndroidDevice($androidDevice)
    {
        $this->androidDevice = $androidDevice;
    }

    /**
     * @return string
     */
    public function getJsonItem()
    {
        return $this->jsonItem;
    }

    /**
     * @param string $jsonItem
     */
    public function setJsonItem($jsonItem)
    {
        $this->jsonItem = $jsonItem;
    }

    /**
     * @return string
     */
    public function getJsonSubItem()
    {
        return $this->jsonSubItem;
    }

    /**
     * @param string $jsonSubItem
     */
    public function setJsonSubItem($jsonSubItem)
    {
        $this->jsonSubItem = $jsonSubItem;
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
     * @return int
     */
    public function getItemCount()
    {
        return $this->itemCount;
    }

    /**
     * @param int $itemCount
     */
    public function setItemCount($itemCount)
    {
        $this->itemCount = $itemCount;
    }

    /**
     * @return int
     */
    public function getSubItemCount()
    {
        return $this->subItemCount;
    }

    /**
     * @param int $subItemCount
     */
    public function setSubItemCount($subItemCount)
    {
        $this->subItemCount = $subItemCount;
    }

    /**
     * @return BusinessInvoice
     */
    public function getSales()
    {
        return $this->sales;
    }


    /**
     * @return BusinessPurchase
     */
    public function getPurchase()
    {
        return $this->purchase;
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
     * @return float
     */
    public function getNetTotal()
    {
        return $this->netTotal;
    }

    /**
     * @param float $netTotal
     */
    public function setNetTotal($netTotal)
    {
        $this->netTotal = $netTotal;
    }

    /**
     * @return float
     */
    public function getReceived()
    {
        return $this->received;
    }

    /**
     * @param float $received
     */
    public function setReceived($received)
    {
        $this->received = $received;
    }

    /**
     * @return float
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @param float $vat
     */
    public function setVat($vat)
    {
        $this->vat = $vat;
    }

    /**
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
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
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }


}

