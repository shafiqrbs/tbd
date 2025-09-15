<?php

namespace Modules\Core\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Warehouse
 * @ORM\Table(name="cor_warehouses" , indexes={
 *     @ORM\Index(name="allowIndex", columns={"name"}),
 *     @ORM\Index(name="settingIdIndex", columns={"setting_id"}),
 *     @ORM\Index(name="locationIndex", columns={"location"}),
 *     @ORM\Index(name="contractPersonIndex", columns={"contract_person"}),
 *     @ORM\Index(name="mobileIndex", columns={"mobile"}),
 *     @ORM\Index(name="emailIndex", columns={"email"}),
 *     @ORM\Index(name="addressIndex", columns={"address"}),
 *     @ORM\Index(name="statusIndex", columns={"status"}),
 *     @ORM\Index(name="createdIndex", columns={"created_at"}),
 *     @ORM\Index(name="updatedIndex", columns={"updated_at"})
 * })
 * @ORM\Entity()
 */
class Warehouse
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
     * @ORM\OneToOne(targetEntity="Modules\Core\App\Entities\Setting")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected $setting;


    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @var string
     * @ORM\Column(name="contract_person", type="string", length=255, nullable=true)
     */
    private $contractPerson;

    /**
     * @var string
     * @ORM\Column(name="mobile", type="string", length=255, nullable=true)
     */
    private $mobile;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var boolean
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    private $status = true;

    /**
     * @var boolean
     * @ORM\Column(name="is_delete", type="boolean", nullable=true)
     */
    private $isDelete = false;

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
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected $domain;


    /**
     * @var boolean
     * @ORM\Column(name="is_default", type="boolean", options={"default":0})
     */
    private $isDefault = false;


    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getSetting()
    {
        return $this->setting;
    }

    /**
     * @param mixed $setting
     */
    public function setSetting($setting): void
    {
        $this->setting = $setting;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function getContractPerson(): string
    {
        return $this->contractPerson;
    }

    public function setContractPerson(string $contractPerson): void
    {
        $this->contractPerson = $contractPerson;
    }

    public function getMobile(): string
    {
        return $this->mobile;
    }

    public function setMobile(string $mobile): void
    {
        $this->mobile = $mobile;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function isStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    public function isDelete(): bool
    {
        return $this->isDelete;
    }

    public function setIsDelete(bool $isDelete): void
    {
        $this->isDelete = $isDelete;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
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
    public function setDomain($domain): void
    {
        $this->domain = $domain;
    }

}

