<?php

namespace Modules\Utility\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * TransactionMethod
 *
 * @ORM\Table(name="uti_transaction_method")
 * @ORM\Entity()
 */
class TransactionMethod
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=255, unique=true)
     */
    private $slug;

    /**
     * @var array
     *
     * @ORM\Column(name="transactionFor", type="array", nullable=true)
     */
    private $transactionFor;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status = true;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return TransactionMethod
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set status
     *
     * @param boolean $status
     *
     * @return TransactionMethod
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getTransactionFor()
    {
        return $this->transactionFor;
    }

    /**
     * @param string $transactionFor
     * Inventory
     * Accounting
     * Online Payment
     * Others
     */

    public function setTransactionFor($transactionFor)
    {
        $this->transactionFor = $transactionFor;
    }

    /**
     * @return AccountCash
     */
    public function getAccountCashes()
    {
        return $this->accountCashes;
    }

    /**
     * @return PaymentSalary
     */
    public function getPaymentSalaries()
    {
        return $this->paymentSalaries;
    }

    /**
     * @return AccountPurchase
     */
    public function getAccountPurchases()
    {
        return $this->accountPurchases;
    }

    /**
     * @return AccountSales
     */
    public function getAccountSales()
    {
        return $this->accountSales;
    }


    /**
     * @return PettyCash
     */
    public function getPettyCash()
    {
        return $this->pettyCash;
    }

    /**
     * @return Expenditure
     */
    public function getExpendituries()
    {
        return $this->expendituries;
    }

    /**
     * @return Sales
     */
    public function getSales()
    {
        return $this->sales;
    }

    /**
     * @return mixed
     */
    public function getAccountPurchaseReturns()
    {
        return $this->accountPurchaseReturns;
    }


    /**
     * @return Purchase
     */
    public function getPurchase()
    {
        return $this->purchase;
    }

    /**
     * @return InvoiceSmsEmail
     */
    public function getInvoiceSmsEmails()
    {
        return $this->invoiceSmsEmails;
    }


    /**
     * @return Invoice
     */
    public function getHmsInvoices()
    {
        return $this->hmsInvoices;
    }

    /**
     * @return DoctorInvoice
     */
    public function getDoctorInvoices()
    {
        return $this->doctorInvoices;
    }

    /**
     * @return InvoiceTransaction
     */
    public function getInvoiceTransactions()
    {
        return $this->invoiceTransactions;
    }

    /**
     * @return PreOrderPayment
     */
    public function getPreOrderPayments()
    {
        return $this->preOrderPayments;
    }

    /**
     * @return OrderPayment
     */
    public function getOrderPayments()
    {
        return $this->orderPayments;
    }

    /**
     * @return DmsInvoice
     */
    public function getDmsInvoice()
    {
        return $this->dmsInvoice;
    }

    /**
     * @return InvoiceModule
     */
    public function getInvoiceModules()
    {
        return $this->invoiceModules;
    }

    /**
     * @return MedicineSales
     */
    public function getMedicineSales()
    {
        return $this->medicineSales;
    }

    /**
     * @return DmsPurchase
     */
    public function getDmsPurchase()
    {
        return $this->dmsPurchase;
    }

    /**
     * @return MedicinePurchase
     */
    public function getMedicinePurchase()
    {
        return $this->medicinePurchase;
    }

    /**
     * @return BusinessInvoice
     */
    public function getBusinessInvoice()
    {
        return $this->businessInvoice;
    }

	/**
	 * @return HotelInvoice
	 */
	public function getHotelInvoice() {
		return $this->hotelInvoice;
	}

	/**
	 * @return HotelPurchase
	 */
	public function getHotelPurchase() {
		return $this->hotelPurchase;
	}

    /**
     * @return AccountPurchaseCommission
     */
    public function getAccountPurchaseCommissions()
    {
        return $this->accountPurchaseCommissions;
    }
}

