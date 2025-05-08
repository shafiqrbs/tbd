<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ConfigVat
 *
 * @ORM\Table( name = "inv_config_vat")
 * @ORM\Entity()
 */
class ConfigVat
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
     * @ORM\OneToOne(targetEntity="Config", cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @var string
     *
     * @ORM\Column(type="string",  nullable=true)
     */
    private $vatRegNo;

    /**
     * @var string
     *
     * @ORM\Column(type="string",options={"default"="Including"})
     */
    private $vatMode;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $vatIntegration;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $hsCodeEnable;

    /**
     * @var smallint
     *
     * @ORM\Column(type="smallint",  nullable=true)
     */
    private $vatPercent;


    /**
     * @var smallint
     *
     * @ORM\Column(type="smallint",  nullable=true)
     */
    private $aitPercent;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $vatEnable;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $sdEnable;

    /**
     * @var float
     *
     * @ORM\Column(type="float",  nullable=true)
     */
    private $sdPercent;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $aitEnable;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $zakatEnable;

    /**
     * @var smallint
     *
     * @ORM\Column(type="smallint",  nullable=true)
     */
    private $zakatPercent;


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
     * @return string
     */
    public function getVatRegNo()
    {
        return $this->vatRegNo;
    }

    /**
     * @param string $vatRegNo
     */
    public function setVatRegNo($vatRegNo)
    {
        $this->vatRegNo = $vatRegNo;
    }

    /**
     * @return bool
     */
    public function isVatMode()
    {
        return $this->vatMode;
    }

    /**
     * @param bool $vatMode
     */
    public function setVatMode($vatMode)
    {
        $this->vatMode = $vatMode;
    }

    /**
     * @return bool
     */
    public function isVatIntegration()
    {
        return $this->vatIntegration;
    }

    /**
     * @param bool $vatIntegration
     */
    public function setVatIntegration($vatIntegration)
    {
        $this->vatIntegration = $vatIntegration;
    }

    /**
     * @return bool
     */
    public function isHsCodeEnable()
    {
        return $this->hsCodeEnable;
    }

    /**
     * @param bool $hsCodeEnable
     */
    public function setHsCodeEnable($hsCodeEnable)
    {
        $this->hsCodeEnable = $hsCodeEnable;
    }

    /**
     * @return smallint
     */
    public function getVatPercent()
    {
        return $this->vatPercent;
    }

    /**
     * @param smallint $vatPercent
     */
    public function setVatPercent($vatPercent)
    {
        $this->vatPercent = $vatPercent;
    }

    /**
     * @return smallint
     */
    public function getAitPercent()
    {
        return $this->aitPercent;
    }

    /**
     * @param smallint $aitPercent
     */
    public function setAitPercent($aitPercent)
    {
        $this->aitPercent = $aitPercent;
    }

    /**
     * @return bool
     */
    public function isVatEnable()
    {
        return $this->vatEnable;
    }

    /**
     * @param bool $vatEnable
     */
    public function setVatEnable($vatEnable)
    {
        $this->vatEnable = $vatEnable;
    }

    /**
     * @return bool
     */
    public function isSdEnable()
    {
        return $this->sdEnable;
    }

    /**
     * @param bool $sdEnable
     */
    public function setSdEnable($sdEnable)
    {
        $this->sdEnable = $sdEnable;
    }

    /**
     * @return float
     */
    public function getSdPercent()
    {
        return $this->sdPercent;
    }

    /**
     * @param float $sdPercent
     */
    public function setSdPercent($sdPercent)
    {
        $this->sdPercent = $sdPercent;
    }

    /**
     * @return bool
     */
    public function isAitEnable()
    {
        return $this->aitEnable;
    }

    /**
     * @param bool $aitEnable
     */
    public function setAitEnable($aitEnable)
    {
        $this->aitEnable = $aitEnable;
    }

    /**
     * @return bool
     */
    public function isZakatEnable()
    {
        return $this->zakatEnable;
    }

    /**
     * @param bool $zakatEnable
     */
    public function setZakatEnable($zakatEnable)
    {
        $this->zakatEnable = $zakatEnable;
    }

    /**
     * @return smallint
     */
    public function getZakatPercent()
    {
        return $this->zakatPercent;
    }

    /**
     * @param smallint $zakatPercent
     */
    public function setZakatPercent($zakatPercent)
    {
        $this->zakatPercent = $zakatPercent;
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

