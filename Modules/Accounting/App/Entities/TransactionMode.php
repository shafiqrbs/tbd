<?php

namespace Modules\Accounting\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AccountTransactionMode
 *
 * @ORM\Table(name ="acc_transaction_mode")
 * @ORM\Entity()
 */
class TransactionMode
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
     * @ORM\ManyToOne(targetEntity="Config", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\Setting")
     **/
    private $method;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $accountOwner;


    /**
     * @var string
     *
     * @ORM\Column(name="authorised", type="string", length=255, nullable=true)
     */
    private $authorised;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $serviceName;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shortName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=255, nullable=true)
     */
    private $mobile;


     /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $serviceCharge = 0;


     /**
     * @var float
     *
     * @ORM\Column(type="boolean",options={"default"="0"})
     */
    private $isSelected;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status = true;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\Setting")
     **/
    private $authorisedMode;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\Setting")
     **/
    private $accountMode;



    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\Setting")
     **/
    private $accountTypeMode;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $accountType;



    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="updated_at", type="datetime",nullable=true)
     */
    private $updatedAt;


}

