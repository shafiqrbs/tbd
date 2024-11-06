<?php

namespace Modules\Accounting\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Config
 *
 * @ORM\Table(name="acc_config")
 * @ORM\Entity(repositoryClass="Modules\Accounting\App\Repositories\ConfigRepository")
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
     * @ORM\OneToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $domain;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="financial_start_date", type="datetime")
     */
    private $financialStartDate;

     /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="financial_end_date", type="datetime")
     */
    private $financialEndDate;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

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
     * @return GlobalOption
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param GlobalOption $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return \DateTime
     */
    public function getFinancialStartDate()
    {
        return $this->financialStartDate;
    }

    /**
     * @param \DateTime $financialStartDate
     */
    public function setFinancialStartDate($financialStartDate)
    {
        $this->financialStartDate = $financialStartDate;
    }

    /**
     * @return \DateTime
     */
    public function getFinancialEndDate()
    {
        return $this->financialEndDate;
    }

    /**
     * @param \DateTime $financialEndDate
     */
    public function setFinancialEndDate($financialEndDate)
    {
        $this->financialEndDate = $financialEndDate;
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




}

