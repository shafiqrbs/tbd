<?php

namespace Modules\Utility\App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bank
 *
 * @ORM\Table(name="uti_banks")
 * @ORM\Entity
 */
class Bank
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


	/**
	 * @var string
	 *
	 * @ORM\Column(name="name", type="string", length=255)
	 */
	private $name;



	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Bank
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
	 * @return mixed
	 */
	public function getAccountBanks()
	{
		return $this->accountBanks;
	}


	/**
	 * @return mixed
	 */
	public function getPortalBankAccount()
	{
		return $this->portalBankAccount;
	}

	/**
	 * @return Sales
	 */
	public function getSales()
	{
		return $this->sales;
	}

	/**
	 * @return PaymentSalary
	 */
	public function getPaymentSalaries()
	{
		return $this->paymentSalaries;
	}

	/**
	 * @return InvoiceTransaction
	 */
	public function getInvoiceTransactions()
	{
		return $this->invoiceTransactions;
	}

	/**
	 * @return DmsInvoice
	 */
	public function getDmsInvoices()
	{
		return $this->dmsInvoices;
	}

	/**
	 * @return MedicineSales
	 */
	public function getMedicineSales()
	{
		return $this->medicineSales;
	}

	/**
	 * @return DpsTreatmentPlan
	 */
	public function getDpsTreatmentPlans()
	{
		return $this->dpsTreatmentPlans;
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
     * @return EmployeePayroll
     */
    public function getEmployeePayroll()
    {
        return $this->employeePayroll;
    }
}

