<?php

namespace Modules\Inventory\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Courier
 *
 * @ORM\Table( name ="inv_store_ledger")
 * @ORM\Entity()
 */
class BusinessStoreLedger
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config", cascade={"detach","merge"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected $config;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessInvoice")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"id" = "ASC"})
     **/
    protected $invoice;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\BusinessStore")
     **/
    protected $store;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\TransactionMethod")
     **/
    private  $transactionMethod;


    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float",  nullable = true)
     */
    private $amount;

    /**
     * @var float
     *
     * @ORM\Column(name="debit", type="float",  nullable = true)
     */
    private $debit;


    /**
     * @var float
     *
     * @ORM\Column(name="credit", type="float",  nullable = true)
     */
    private $credit;


    /**
     * @var float
     *
     * @ORM\Column(name="balance", type="float",  nullable = true)
     */
    private $balance;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=25, nullable = true)
     */
    private $transactionType = 'Debit';

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $approvedBy;



    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;


     /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean",  nullable=true)
     */
    private $status = false;


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



}

