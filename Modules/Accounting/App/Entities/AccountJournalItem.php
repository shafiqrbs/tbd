<?php

namespace Modules\Accounting\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * AccountJournal
 *
 * @ORM\Table(name="acc_journal_item")
 * @ORM\Entity()
 */
class AccountJournalItem
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
     * @ORM\ManyToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected $accountLedger;

      /**
     * @ORM\ManyToOne(targetEntity="AccountJournal")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected $accountJournal;

    /**
     * @ORM\ManyToOne(targetEntity="AccountHead")
     **/
    protected $accountHead;

    /**
     * @ORM\ManyToOne(targetEntity="AccountHead")
     **/
    protected $accountSubHead;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\Bank")
     **/
    protected $bank;

    /**
     * @ORM\ManyToOne(targetEntity="AccountJournalItem", inversedBy="children", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="parent", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="AccountJournalItem" , mappedBy="parent")
     **/
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Sales")
     * @ORM\JoinColumn(name="sales_id", referencedColumnName="id", nullable=true,onDelete="CASCADE")
     **/
    private  $sales;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\SalesReturn")
     * @ORM\JoinColumn(name="sales_return_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private  $salesReturn;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Purchase")
     * @ORM\JoinColumn(name="purchase_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private  $purchase;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\SalesReturn")
     * @ORM\JoinColumn(name="purchase_return_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private  $purchaseReturn;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\ItemStockAdjustment")
     * @ORM\JoinColumn(name="stock_adjustment_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private  $stockAdjustment;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Damage")
     * @ORM\JoinColumn(name="damage_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private  $damage;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $amount = 0;

     /**
     * @var float
     *
     * @ORM\Column(name="debit", type="float", nullable=true)
     */
    private $debit = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="credit", type="float", nullable=true)
     */
    private $credit = 0;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $mode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $balanceMode;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=50, nullable = true)
     */
    private $process;


    /**
     * @var string
     *
     * @ORM\Column(name="narration", type="text", length=50, nullable = true)
     */
    private $narration;


    /**
     * @var string
     *
     * @ORM\Column(type="string",nullable = true)
     */
    private $initiatNo;


    /**
     * @var string
     *
     * @ORM\Column(name="bankName", type="text",  nullable = true)
     */
    private $bankName;


    /**
     * @var string
     *
     * @ORM\Column(type="text",  nullable = true)
     */
    private $branchName;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $receivedFrom;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $accountMode;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $forwardingName;


     /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    private $initiatDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isParent", type="boolean" , nullable=true)
     */
    private $isParent = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="firstChild", type="boolean" , nullable=true)
     */
    private $firstChild = false;


     /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean" , nullable=true)
     */
    private $status = false;

    /**
     * @var \DateTime
     * @ORM\Column(name="reconcile_date", type="datetime", nullable=true)
     */
    private $reconcileDate;


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

