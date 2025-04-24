<?php

namespace Modules\Core\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Domain\App\Entities\GlobalOption;


/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="Modules\Core\App\Repositories\UserRepository")
 */
class User
{


	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Profile", mappedBy="user")
     */

    protected $profile;



    /**
     * @ORM\ManyToMany(targetEntity="Warehouse", inversedBy="users")
     */
    protected $warehouses;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $enabled = true;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	private $isDelete = 0;

	/**
	 * @var int
	 *
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	private $domainOwner = 0;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true , options={"default"="user"})
	 */
	private $userGroup = "user";

	/**
	 * @var string
	 *
	 * @ORM\Column(name="username", type="string",nullable=true)
	 */
	private $username = "";


	/**
	 * @var string
	 *
	 * @ORM\Column(name="password", type="string", nullable=true)
	 */
	private $password = "";


	/**
	 * @var string
	 *
	 * @ORM\Column(name="email", type="string",nullable=true)
	 */
	private $email = "";


	/**
	 * @var string
	 *
	 * @ORM\Column(name="name", type="string",nullable=true)
	 */
	private $name = "";


	/**
	 * @var string
	 *
	 * @ORM\Column(name="mobile", type="string", nullable=true)
	 */
	private $mobile = "";

	/**
	 * @var string
	 *
	 * @ORM\Column(name="remember_token", type="string", length = 30, nullable=true)
	 */
	private $remember_token = "";

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length = 30, nullable=true)
	 */
	private $appPassword = "@123456";

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $avatar;

	/**
	 * @ORM\OneToOne(targetEntity="UserRole", inversedBy="user")
	 */
	protected $userRole;


	/**
     * @var GlobalOption
	 * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 **/
	protected $domain;


	/**
     * @var GlobalOption
	 * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Setting")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 **/
	protected $employeeGroup;


    /**
     * @Column(type="integer", name="login_count", nullable=false, options={"unsigned":true, "default":0})
     */
    protected $loginCount;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="email_verified_at", type="datetime")
     */
    private $emailVerifiedAt;

     /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="deleted_at", type="datetime",nullable=true)
     */
    private $deletedAt;

     /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="updated_at", type="datetime" , nullable=true)
     */
    private $updatedAt;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        return $this->isDelete;
    }

    /**
     * @param bool $isDelete
     */
    public function setIsDelete($isDelete)
    {
        $this->isDelete = $isDelete;
    }

    /**
     * @return int
     */
    public function getDomainOwner()
    {
        return $this->domainOwner;
    }

    /**
     * @param int $domainOwner
     */
    public function setDomainOwner($domainOwner)
    {
        $this->domainOwner = $domainOwner;
    }

    /**
     * @return string
     */
    public function getUserGroup()
    {
        return $this->userGroup;
    }

    /**
     * @param string $userGroup
     */
    public function setUserGroup($userGroup)
    {
        $this->userGroup = $userGroup;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param string $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    /**
     * @return string
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * @param string $remember_token
     */
    public function setRememberToken($remember_token)
    {
        $this->remember_token = $remember_token;
    }

    /**
     * @return string
     */
    public function getAppPassword()
    {
        return $this->appPassword;
    }

    /**
     * @param string $appPassword
     */
    public function setAppPassword($appPassword)
    {
        $this->appPassword = $appPassword;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    /**
     * @return array
     */
    public function getAppRoles()
    {
        return $this->appRoles;
    }

    /**
     * @param array $appRoles
     */
    public function setAppRoles($appRoles)
    {
        $this->appRoles = $appRoles;
    }

    /**
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param mixed $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @return mixed
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param mixed $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
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
     * @return mixed
     */
    public function getLoginCount()
    {
        return $this->loginCount;
    }

    /**
     * @param mixed $loginCount
     */
    public function setLoginCount($loginCount)
    {
        $this->loginCount = $loginCount;
    }

    /**
     * @return \DateTime
     */
    public function getEmailVerifiedAt()
    {
        return $this->emailVerifiedAt;
    }

    /**
     * @param \DateTime $emailVerifiedAt
     */
    public function setEmailVerifiedAt($emailVerifiedAt)
    {
        $this->emailVerifiedAt = $emailVerifiedAt;
    }

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime $deletedAt
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
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
