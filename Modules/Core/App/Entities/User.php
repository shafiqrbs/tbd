<?php

namespace Modules\Core\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Domain\App\Entities\GlobalOption;


/**
 * @ORM\Table(name="users")
 * @ORM\Entity()
 */
class User
{


	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;


	protected $enabled = true;

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
	 * @ORM\Column(type="string", length = 30, nullable=true)
	 */
	private $userGroup = "user";

	/**
	 * @var string
	 *
	 * @ORM\Column(name="username", type="string", length = 30, nullable=true)
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
	 * @ORM\Column(name="email", type="string", length = 30, nullable=true)
	 */
	private $email = "";


	/**
	 * @var string
	 *
	 * @ORM\Column(name="name", type="string", length = 30, nullable=true)
	 */
	private $name = "";


	/**
	 * @var string
	 *
	 * @ORM\Column(name="mobile", type="string", length = 30, nullable=true)
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
	 * @var array
	 *
	 * @ORM\Column(name="roles", type="array", nullable=true)
	 */
	private $roles;


	/**
	 * @var array
	 *
	 * @ORM\Column( type="array", nullable=true)
	 */
	private $appRoles;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $avatar;

	/**
	 * @ORM\ManyToMany(targetEntity="Group", inversedBy="users")
	 * @ORM\JoinTable(name="user_user_group",
	 *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
	 * )
	 */
	protected $groups;


	/**
     * @var GlobalOption
	 * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 **/
	protected $domain;


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


}
