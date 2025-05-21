<?php

namespace Modules\Accounting\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Modules\Domain\App\Entities\GlobalOption;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Config
 *
 * @ORM\Table(name="acc_config")
 * @ORM\Entity(repositoryClass="Modules\Accounting\App\Repositories\ConfigRepository")
 */
class Config
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
     * @ORM\OneToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $domain;

    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="capital_investment_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $capitalInvestment;

    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_cash_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $accountCash;

    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_bank_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $accountBank;

    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_mobile_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $accountMobile;

    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $accountUser;

    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_vendor_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $accountVendor;

    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_customer_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $accountCustomer;

    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_product_group_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $accountProductGroup;

    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_category_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $accountCategory;

    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_stock_opening_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $accountStockOpening;


    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_purchase_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $accountPurchase;


    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_purchase_discount_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $accountPurchaseDiscount;

    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_sales_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $accountSales;


    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_sales_discount_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $accountSalesDiscount;



     /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_vat_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $accountVat;

    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_ait_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $accountAit;


     /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_zakat_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $accountZakat;


    /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_sd_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $accountSd;


     /**
     * @ORM\OneToOne(targetEntity="AccountHead")
     * @ORM\JoinColumn(name="account_tds_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $accountTds;


    /**
     * @ORM\OneToOne(targetEntity="AccountVoucher")
     * @ORM\JoinColumn(name="voucher_stock_opening_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $voucherStockOpening;

    /**
     * @ORM\OneToOne(targetEntity="AccountVoucher")
     * @ORM\JoinColumn(name="voucher_purchase_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $voucherPurchase;

    /**
     * @ORM\OneToOne(targetEntity="AccountVoucher")
     * @ORM\JoinColumn(name="voucher_sales_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $voucherSales;

    /**
     * @ORM\OneToOne(targetEntity="AccountVoucher")
     * @ORM\JoinColumn(name="voucher_sales_return_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $voucherSalesReturn;

    /**
     * @ORM\OneToOne(targetEntity="AccountVoucher")
     * @ORM\JoinColumn(name="voucher_purchase_return_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $voucherPurchaseReturn;


    /**
     * @ORM\OneToOne(targetEntity="AccountVoucher")
     * @ORM\JoinColumn(name="voucher_stock_reconciliation_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private  $voucherStockReconciliation;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="financial_start_date", type="datetime")
     */
    private $financialStartDate;


     /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="financial_end_date", type="datetime")
     */
    private $financialEndDate;


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
     * @return \DateTime
     */
    public function getFinancialStartDate()
    {
        return $this->financialStartDate;
    }

    /**
     * @param \DateTime $financialStartDate
     */
    public function setFinancialStartDate($financialStartDate)
    {
        $this->financialStartDate = $financialStartDate;
    }

    /**
     * @return \DateTime
     */
    public function getFinancialEndDate()
    {
        return $this->financialEndDate;
    }

    /**
     * @param \DateTime $financialEndDate
     */
    public function setFinancialEndDate($financialEndDate)
    {
        $this->financialEndDate = $financialEndDate;
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
    public function getAccountCash()
    {
        return $this->accountCash;
    }

    /**
     * @param mixed $accountCash
     */
    public function setAccountCash($accountCash)
    {
        $this->accountCash = $accountCash;
    }

    /**
     * @return mixed
     */
    public function getAccountBank()
    {
        return $this->accountBank;
    }

    /**
     * @param mixed $accountBank
     */
    public function setAccountBank($accountBank)
    {
        $this->accountBank = $accountBank;
    }

    /**
     * @return mixed
     */
    public function getAccountMobile()
    {
        return $this->accountMobile;
    }

    /**
     * @param mixed $accountMobile
     */
    public function setAccountMobile($accountMobile)
    {
        $this->accountMobile = $accountMobile;
    }

    /**
     * @return mixed
     */
    public function getAccountUser()
    {
        return $this->accountUser;
    }

    /**
     * @param mixed $accountUser
     */
    public function setAccountUser($accountUser)
    {
        $this->accountUser = $accountUser;
    }

    /**
     * @return mixed
     */
    public function getAccountVendor()
    {
        return $this->accountVendor;
    }

    /**
     * @param mixed $accountVendor
     */
    public function setAccountVendor($accountVendor)
    {
        $this->accountVendor = $accountVendor;
    }

    /**
     * @return mixed
     */
    public function getAccountCustomer()
    {
        return $this->accountCustomer;
    }

    /**
     * @param mixed $accountCustomer
     */
    public function setAccountCustomer($accountCustomer)
    {
        $this->accountCustomer = $accountCustomer;
    }

    /**
     * @return mixed
     */
    public function getAccountProductGroup()
    {
        return $this->accountProductGroup;
    }

    /**
     * @param mixed $accountProductGroup
     */
    public function setAccountProductGroup($accountProductGroup)
    {
        $this->accountProductGroup = $accountProductGroup;
    }

    /**
     * @return mixed
     */
    public function getAccountCategory()
    {
        return $this->accountCategory;
    }

    /**
     * @param mixed $accountCategory
     */
    public function setAccountCategory($accountCategory)
    {
        $this->accountCategory = $accountCategory;
    }

    /**
     * @return mixed
     */
    public function getVoucherStockOpening()
    {
        return $this->voucherStockOpening;
    }

    /**
     * @param mixed $voucherStockOpening
     */
    public function setVoucherStockOpening($voucherStockOpening)
    {
        $this->voucherStockOpening = $voucherStockOpening;
    }

    /**
     * @return mixed
     */
    public function getVoucherPurchase()
    {
        return $this->voucherPurchase;
    }

    /**
     * @param mixed $voucherPurchase
     */
    public function setVoucherPurchase($voucherPurchase)
    {
        $this->voucherPurchase = $voucherPurchase;
    }

    /**
     * @return mixed
     */
    public function getVoucherSales()
    {
        return $this->voucherSales;
    }

    /**
     * @param mixed $voucherSales
     */
    public function setVoucherSales($voucherSales)
    {
        $this->voucherSales = $voucherSales;
    }

    /**
     * @return mixed
     */
    public function getVoucherSalesReturn()
    {
        return $this->voucherSalesReturn;
    }

    /**
     * @param mixed $voucherSalesReturn
     */
    public function setVoucherSalesReturn($voucherSalesReturn)
    {
        $this->voucherSalesReturn = $voucherSalesReturn;
    }

    /**
     * @return mixed
     */
    public function getVoucherPurchaseReturn()
    {
        return $this->voucherPurchaseReturn;
    }

    /**
     * @param mixed $voucherPurchaseReturn
     */
    public function setVoucherPurchaseReturn($voucherPurchaseReturn)
    {
        $this->voucherPurchaseReturn = $voucherPurchaseReturn;
    }

    /**
     * @return mixed
     */
    public function getVoucherStockReconciliation()
    {
        return $this->voucherStockReconciliation;
    }

    /**
     * @param mixed $voucherStockReconciliation
     */
    public function setVoucherStockReconciliation($voucherStockReconciliation)
    {
        $this->voucherStockReconciliation = $voucherStockReconciliation;
    }

    /**
     * @return mixed
     */
    public function getCapitalInvestment()
    {
        return $this->capitalInvestment;
    }

    /**
     * @param mixed $capitalInvestment
     */
    public function setCapitalInvestment($capitalInvestment)
    {
        $this->capitalInvestment = $capitalInvestment;
    }

    /**
     * @return mixed
     */
    public function getAccountStockOpening()
    {
        return $this->accountStockOpening;
    }

    /**
     * @param mixed $accountStockOpening
     */
    public function setAccountStockOpening($accountStockOpening)
    {
        $this->accountStockOpening = $accountStockOpening;
    }

    /**
     * @return mixed
     */
    public function getAccountPurchaseDiscount()
    {
        return $this->accountPurchaseDiscount;
    }

    /**
     * @param mixed $accountPurchaseDiscount
     */
    public function setAccountPurchaseDiscount($accountPurchaseDiscount)
    {
        $this->accountPurchaseDiscount = $accountPurchaseDiscount;
    }

    /**
     * @return mixed
     */
    public function getAccountSalesDiscount()
    {
        return $this->accountSalesDiscount;
    }

    /**
     * @param mixed $accountSalesDiscount
     */
    public function setAccountSalesDiscount($accountSalesDiscount)
    {
        $this->accountSalesDiscount = $accountSalesDiscount;
    }

    /**
     * @return mixed
     */
    public function getAccountVat()
    {
        return $this->accountVat;
    }

    /**
     * @param mixed $accountVat
     */
    public function setAccountVat($accountVat)
    {
        $this->accountVat = $accountVat;
    }

    /**
     * @return mixed
     */
    public function getAccountAit()
    {
        return $this->accountAit;
    }

    /**
     * @param mixed $accountAit
     */
    public function setAccountAit($accountAit)
    {
        $this->accountAit = $accountAit;
    }

    /**
     * @return mixed
     */
    public function getAccountZakat()
    {
        return $this->accountZakat;
    }

    /**
     * @param mixed $accountZakat
     */
    public function setAccountZakat($accountZakat)
    {
        $this->accountZakat = $accountZakat;
    }

    /**
     * @return mixed
     */
    public function getAccountSd()
    {
        return $this->accountSd;
    }

    /**
     * @param mixed $accountSd
     */
    public function setAccountSd($accountSd)
    {
        $this->accountSd = $accountSd;
    }

    /**
     * @return mixed
     */
    public function getAccountPurchase()
    {
        return $this->accountPurchase;
    }

    /**
     * @param mixed $accountPurchase
     */
    public function setAccountPurchase($accountPurchase)
    {
        $this->accountPurchase = $accountPurchase;
    }

    /**
     * @return mixed
     */
    public function getAccountSales()
    {
        return $this->accountSales;
    }

    /**
     * @param mixed $accountSales
     */
    public function setAccountSales($accountSales)
    {
        $this->accountSales = $accountSales;
    }

    /**
     * @return mixed
     */
    public function getAccountTds()
    {
        return $this->accountTds;
    }

    /**
     * @param mixed $accountTds
     */
    public function setAccountTds($accountTds)
    {
        $this->accountTds = $accountTds;
    }






}

