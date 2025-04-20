<?php

namespace Modules\Accounting\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * AccountJournal
 *
 * @ORM\Table(name="acc_daily_cash_flow")
 * @ORM\Entity()
 */

class AccountDailyCashFlow
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
     * @ORM\ManyToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected $accountLedger;

    /**
     * @ORM\ManyToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_head_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private  $accountHead;

    /**
     * @ORM\ManyToOne(targetEntity="AccountVoucher")
     **/
    protected $voucher;

    /**
     * @var string
     * @ORM\Column(type="string",nullable=true)
     */
    private $process='New';


    /**
     * @var float
     *
     * @ORM\Column(name="opening_amount", type="float",nullable=true)
     */
    private $openingAmount = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="closing_amount", type="float",nullable=true)
     */
    private $closingAmount = 0;

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

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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

    /**
     * @return mixed
     */
    public function getAccountLedger()
    {
        return $this->accountLedger;
    }

    /**
     * @param mixed $accountLedger
     */
    public function setAccountLedger($accountLedger)
    {
        $this->accountLedger = $accountLedger;
    }

    /**
     * @return mixed
     */
    public function getAccountHead()
    {
        return $this->accountHead;
    }

    /**
     * @param mixed $accountHead
     */
    public function setAccountHead($accountHead)
    {
        $this->accountHead = $accountHead;
    }

    /**
     * @return mixed
     */
    public function getVoucher()
    {
        return $this->voucher;
    }

    /**
     * @param mixed $voucher
     */
    public function setVoucher($voucher)
    {
        $this->voucher = $voucher;
    }

    /**
     * @return string
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param string $process
     */
    public function setProcess($process)
    {
        $this->process = $process;
    }

    /**
     * @return float
     */
    public function getOpeningAmount()
    {
        return $this->openingAmount;
    }

    /**
     * @param float $openingAmount
     */
    public function setOpeningAmount($openingAmount)
    {
        $this->openingAmount = $openingAmount;
    }

    /**
     * @return float
     */
    public function getClosingAmount()
    {
        return $this->closingAmount;
    }

    /**
     * @param float $closingAmount
     */
    public function setClosingAmount($closingAmount)
    {
        $this->closingAmount = $closingAmount;
    }

    /**
     * @return float
     */
    public function getDebit()
    {
        return $this->debit;
    }

    /**
     * @param float $debit
     */
    public function setDebit($debit)
    {
        $this->debit = $debit;
    }

    /**
     * @return float
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * @param float $credit
     */
    public function setCredit($credit)
    {
        $this->credit = $credit;
    }


}

