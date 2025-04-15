<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Domain\App\Entities\GlobalOption;

/**
 * B2BCategoryPriceMatrix
 *
 * @ORM\Table("inv_b2b_category_price_matrix")
 * @ORM\Entity()
 */
class B2BCategoryPriceMatrix
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;

    /**
     * @var GlobalOption $subDomain
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption")
     **/
    private $subDomain;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Category" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(name="domain_category_id", onDelete="CASCADE")
     **/
    private  $domainStockItem;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Category" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(name="sub_domain_category_id", onDelete="CASCADE")
     **/
    private  $subDomainStockItem;


    /**
     * @var float
     *
     * @ORM\Column(name="percent_mode",type="float" , nullable=true)
     */
    private $percentMode;

    /**
     * @var float
     *
     * @ORM\Column(name="price_percent",type="float" , nullable=true)
     */
    private $pricePercent;

    /**
     * @var float
     *
     * @ORM\Column(name="sales_price_percent",type="float" , nullable=true)
     */
    private $salesPricePercent;


    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;


    /**
     * @var boolean
     * @ORM\Column(type="boolean" )
     */
    private $status;


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

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="string",  nullable=true)
     */
    private $notes;

    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", nullable=true)
     */
    private $process = "Created";


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
    public function getCategoryGroup()
    {
        return $this->categoryGroup;
    }

    /**
     * @param mixed $categoryGroup
     */
    public function setCategoryGroup($categoryGroup)
    {
        $this->categoryGroup = $categoryGroup;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
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




}

