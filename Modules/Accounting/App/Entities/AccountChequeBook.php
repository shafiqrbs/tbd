<?php

namespace Modules\Accounting\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AccountChequeBook
 *
 * @ORM\Table(name="acc_cheque_book")
 * @ORM\Entity()
 *
 */
class AccountChequeBook
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
     * @ORM\OneToOne(targetEntity="AccountHead", mappedBy="chequeBooks")
     **/
     protected $ledger;


    /**
     * @var float
     *
     * @ORM\Column(type="float",nullable=true)
     */
    private $fromNumber;

    /**
     * @var float
     *
     * @ORM\Column(type="float",nullable=true)
     */
    private $toNumber;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer",nullable=true)
     */
    private $numberOfCheque;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status = true;


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
}

