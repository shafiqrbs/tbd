<?php

namespace Modules\Inventory\App\Entities;

use Modules\Core\App\Entities\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
/**
 * BusinessReverse
 *
 * @ORM\Table(name="inv_reverse")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\BusinessReverseRepository")
 */
class BusinessReverse
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config", inversedBy="businessReverses")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $businessConfig;

    /**
     * @ORM\OneToOne(targetEntity="Modules\Inventory\App\Entities\BusinessInvoice", inversedBy="businessReverse")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $businessInvoice;


    /**
     * @ORM\OneToOne(targetEntity="Modules\Inventory\App\Entities\BusinessPurchase", inversedBy="businessReverse")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $businessPurchase;


    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=50, nullable=true)
     */
    private $process;



    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

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
     * Set name
     *
     * @param string $name
     *
     * @return BusinessReverse
     */
    public function setName($name)
    {
        return $this->name = $name;

    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     */
    public function setProcess($process)
    {
        $this->process = $process;
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
	 * @return BusinessPurchase
	 */
	public function getBusinessPurchase() {
		return $this->businessPurchase;
	}

	/**
	 * @param BusinessPurchase $businessPurchase
	 */
	public function setBusinessPurchase( $businessPurchase ) {
		$this->businessPurchase = $businessPurchase;
	}

	/**
	 * @return Config
	 */
	public function getBusinessConfig() {
		return $this->businessConfig;
	}

	/**
	 * @param Config $businessConfig
	 */
	public function setBusinessConfig( $businessConfig ) {
		$this->businessConfig = $businessConfig;
	}

	/**
	 * @return BusinessInvoice
	 */
	public function getBusinessInvoice() {
		return $this->businessInvoice;
	}

	/**
	 * @param BusinessInvoice $businessInvoice
	 */
	public function setBusinessInvoice( $businessInvoice ) {
		$this->businessInvoice = $businessInvoice;
	}


}

