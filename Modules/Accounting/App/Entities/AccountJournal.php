<?php

namespace Modules\Accounting\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * AccountExpense
 *
 * @ORM\Table(name="acc_journal")
 * @ORM\Entity()
 */
class AccountJournal
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
     * @ORM\OneToMany(targetEntity="AccountJournalItem", mappedBy="accountJournal")
     * @ORM\OrderBy({"balanceMode" = "DESC"})
     **/
    protected $journalItems;

    /**
     * @ORM\ManyToOne(targetEntity="AccountVoucher")
     **/
    protected $voucher;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Sales")
     * @ORM\JoinColumn(name="sales_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $sales;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\SalesReturn")
     * @ORM\JoinColumn(name="sales_return_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $salesReturn;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Purchase")
     * @ORM\JoinColumn(name="purchase_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $purchase;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\SalesReturn")
     * @ORM\JoinColumn(name="purchase_return_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $purchaseReturn;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\ItemStockAdjustment")
     * @ORM\JoinColumn(name="stock_adjustment_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $stockAdjustment;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Damage")
     * @ORM\JoinColumn(name="damage_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $damage;




    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" , nullable=true)
     */
    private $isSignature = false;

    /**
     * @var integer
     * @ORM\Column(type="integer",nullable=true)
     */
    private $processOrdering = 0;

    /**
     * @var string
     * @ORM\Column(type="string",nullable=true)
     */
    private $entryMode='voucher';


    /**
     * @var string
     * @ORM\Column(type="string",nullable=true)
     */
    private $process='New';

    /**
     * @var string
     * @ORM\Column(type="string",nullable=true)
     */
    private $module ="account-journal";


    /**
     * @var string
     * @ORM\Column(type="string",nullable=true)
     */
    private $waitingProcess;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float",nullable=true)
     */
    private $amount = 0;

     /**
     * @var float
     *
     * @ORM\Column(name="debit", type="float",nullable=true)
     */
    private $debit = 0;

     /**
     * @var float
     *
     * @ORM\Column(name="credit", type="float",nullable=true)
     */
    private $credit = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="balance", type="float", nullable=true)
     */
    private $balance = 0;


    /**
     * @var string
     *
     * @ORM\Column(name="paymentMethod", type="string", length=50, nullable=true)
     */
    private $paymentMethod;


    /**
     * @var string
     *
     * @ORM\Column(name="accountRefNo", type="string", length=50, nullable=true)
     */
    private $accountRefNo;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;


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
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable = true)
     */
    private $description;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $reportTo;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;


    /**
     * @Assert\File(maxSize="8388608")
     */
    protected $file;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="issue_date", type="datetime", nullable=true)
     */
    private $issueDate;


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

