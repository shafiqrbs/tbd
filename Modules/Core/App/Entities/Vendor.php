<?php

namespace Modules\Core\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Customer
 * @ORM\Table(name="cor_vendors" , indexes={
 *     @ORM\Index(name="allowIndex", columns={"name"}),
 *     @ORM\Index(name="customerIdIndex", columns={"company_name"}),
 *     @ORM\Index(name="vendorCodeIndex", columns={"vendor_code"}),
 *     @ORM\Index(name="mobileIndex", columns={"mobile"}),
 *     @ORM\Index(name="statusIndex", columns={"status"}),
 *     @ORM\Index(name="createdIndex", columns={"created_at"}),
 *     @ORM\Index(name="updatedIndex", columns={"updated_at"})
 * })
 * @ORM\Entity()
 */
class Vendor
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
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption", inversedBy="customers")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/

    protected $domain;


	/**
	 * @ORM\OneToOne(targetEntity="Modules\Core\App\Entities\Customer", inversedBy="accountVendor")
	 * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true, onDelete="cascade")
	 */
	protected $customer;

	/**
     * @var string
     *
     * @ORM\Column(name="module", type="string", length = 50, nullable=true)
     */
    private $module;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;


     /**
     * @var string
     *
     * @ORM\Column(name="unique_id", type="string", length=255, nullable=true)
     */
    private $uniqueId;


    /**
     * @var string
     *
     * @ORM\Column(name="vendor_code", type="string", length=50, nullable=true)
     */
    private $vendorCode;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer", nullable=true)
     */
    private $code;


     /**
     * @var float
     *
     * @ORM\Column(name="opening_balance", type="float", nullable=true)
     */
    private $openingBalance;


    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=255, nullable=true)
     */
    private $companyName;

    /**
     * @Gedmo\Slug(fields={"companyName"})
     * @Doctrine\ORM\Mapping\Column(length=255)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text", nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=255, nullable=true)
     */
    private $mobile;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255 , nullable=true)
     */
    private $email;

     /**
     * @var string
     *
     * @ORM\Column(name="binno", type="string", length=255 , nullable=true)
     */
    private $binno;

     /**
     * @var string
     *
     * @ORM\Column(name="tinno", type="string", length=255 , nullable=true)
     */
    private $tinno;

    /**
     * @var integer
     *
     * @ORM\Column(name="oldId", type="integer", length=10 , nullable=true)
     */
    private $oldId;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="mode", type="string", length= 50, nullable=true)
	 */
	private $mode;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    private $status = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isNew", type="boolean", nullable=true)
     */
    private $isNew = true;

    /**
     * @var boolean
     *
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



}

