<?php

namespace Appstore\Bundle\BusinessBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BusinessWearHouse
 *
 * @ORM\Table( name ="business_product_transfer")
 * @ORM\Entity(repositoryClass="Appstore\Bundle\BusinessBundle\Repository\ProductTransferRepository")
 */
class ProductTransfer
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
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessConfig", inversedBy="wearHouses" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $businessConfig;

    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\BusinessParticular")
     **/
    private $businessParticular;


	/**
	 * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\WearHouse")
	 **/
	private $fromWearHouse;


    /**
     * @ORM\ManyToOne(targetEntity="Appstore\Bundle\BusinessBundle\Entity\WearHouse")
     **/
    private $toWearHouse;


    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User")
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User")
     **/
    private  $approvedBy;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer", length=10, nullable=true)
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="remark", type="text",nullable=true)
     */
    private $remark;

    /**
     * @var string
     *
     * @ORM\Column(name="process", type="text",nullable=true)
     */
    private $process="Created";

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
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;


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
	 * @return int
	 */
	public function getQuantity(){
		return $this->quantity;
	}

	/**
	 * @param int $quantity
	 */
	public function setQuantity($quantity ) {
		$this->quantity = $quantity;
	}

	/**
	 * @return bool
	 */
	public function isStatus(): bool {
		return $this->status;
	}

	/**
	 * @param bool $status
	 */
	public function setStatus($status ) {
		$this->status = $status;
	}

    /**
     * @return mixed
     */
    public function getBusinessParticular()
    {
        return $this->businessParticular;
    }

    /**
     * @param mixed $businessParticular
     */
    public function setBusinessParticular($businessParticular)
    {
        $this->businessParticular = $businessParticular;
    }

    /**
     * @return mixed
     */
    public function getFromWearHouse()
    {
        return $this->fromWearHouse;
    }

    /**
     * @param mixed $fromWearHouse
     */
    public function setFromWearHouse($fromWearHouse)
    {
        $this->fromWearHouse = $fromWearHouse;
    }

    /**
     * @return mixed
     */
    public function getToWearHouse()
    {
        return $this->toWearHouse;
    }

    /**
     * @param mixed $toWearHouse
     */
    public function setToWearHouse($toWearHouse)
    {
        $this->toWearHouse = $toWearHouse;
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
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param mixed $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return mixed
     */
    public function getApprovedBy()
    {
        return $this->approvedBy;
    }

    /**
     * @param mixed $approvedBy
     */
    public function setApprovedBy($approvedBy)
    {
        $this->approvedBy = $approvedBy;
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

}

