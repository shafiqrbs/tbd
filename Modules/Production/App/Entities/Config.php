<?php

namespace Modules\Production\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Production\App\Entities\ProductionWorkOrder;


/**
 * InvoiceKeyValue
 *
 * @ORM\Table(name="pro_config")
 * @ORM\Entity(repositoryClass="Modules\Production\App\Repositories\ConfigRepository")
 */
class Config
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
     * @ORM\OneToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $domain;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isWarehouse;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isAutoApproved;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $issueWithWarehouse;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $issueByProductionBatch;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isMeasurement;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity="Setting")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $productionProcedure;

    /**
     * @ORM\OneToOne(targetEntity="Setting")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $consumptionMethod;


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
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param mixed $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
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
     * @return mixed
     */
    public function getProductionProcedure()
    {
        return $this->productionProcedure;
    }

    /**
     * @param mixed $productionProcedure
     */
    public function setProductionProcedure($productionProcedure)
    {
        $this->productionProcedure = $productionProcedure;
    }

    /**
     * @return mixed
     */
    public function getConsumptionMethod()
    {
        return $this->consumptionMethod;
    }

    /**
     * @param mixed $consumptionMethod
     */
    public function setConsumptionMethod($consumptionMethod)
    {
        $this->consumptionMethod = $consumptionMethod;
    }

    /**
     * @return bool
     */
    public function isWarehouse()
    {
        return $this->isWarehouse;
    }

    /**
     * @param bool $isWarehouse
     */
    public function setIsWarehouse($isWarehouse)
    {
        $this->isWarehouse = $isWarehouse;
    }

    /**
     * @return bool
     */
    public function isMeasurement()
    {
        return $this->isMeasurement;
    }

    /**
     * @param bool $isMeasurement
     */
    public function setIsMeasurement($isMeasurement)
    {
        $this->isMeasurement = $isMeasurement;
    }

    /**
     * @return bool
     */
    public function isAutoApproved()
    {
        return $this->isAutoApproved;
    }

    /**
     * @param bool $isAutoApproved
     */
    public function setIsAutoApproved($isAutoApproved)
    {
        $this->isAutoApproved = $isAutoApproved;
    }

    /**
     * @return bool
     */
    public function isIssueWithWarehouse()
    {
        return $this->issueWithWarehouse;
    }

    /**
     * @param bool $issueWithWarehouse
     */
    public function setIssueWithWarehouse($issueWithWarehouse)
    {
        $this->issueWithWarehouse = $issueWithWarehouse;
    }

    /**
     * @return bool
     */
    public function isIssueByProductionBatch()
    {
        return $this->issueByProductionBatch;
    }

    /**
     * @param bool $issueByProductionBatch
     */
    public function setIssueByProductionBatch($issueByProductionBatch)
    {
        $this->issueByProductionBatch = $issueByProductionBatch;
    }


}

