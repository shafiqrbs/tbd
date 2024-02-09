<?php

namespace Modules\Utility\App\Entities;

use Appstore\Bundle\AccountingBundle\Entity\Expenditure;
use Appstore\Bundle\AccountingBundle\Entity\ExpenseAndroidProcess;
use Appstore\Bundle\BusinessBundle\Entity\BusinessAndroidProcess;
use Appstore\Bundle\InventoryBundle\Entity\InventoryAndroidProcess;
use Appstore\Bundle\MedicineBundle\Entity\MedicineAndroidProcess;
use Appstore\Bundle\MedicineBundle\Entity\MedicinePurchase;
use Appstore\Bundle\MedicineBundle\Entity\MedicineSales;
use Appstore\Bundle\RestaurantBundle\Entity\RestaurantAndroidProcess;
use Doctrine\ORM\Mapping as ORM;


/**
 * Icon
 *
 * @ORM\Table("uti_android_device_setup")
 * @ORM\Entity()
 */
class AndroidDeviceSetup
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
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption", inversedBy="androids")
     **/

    private $globalOption;


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
     * @ORM\Column(name="device", type="string", length=255)
     */
    private $device;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status = true;


    /**
     * @return mixed
     */
    public function getGlobalOption()
    {
        return $this->globalOption;
    }

    /**
     * @param mixed $globalOption
     */
    public function setGlobalOption($globalOption)
    {
        $this->globalOption = $globalOption;
    }

    /**
     * @return mixed
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param mixed $device
     */
    public function setDevice($device)
    {
        $this->device = $device;
    }

    /**
     * @return bool
     */
    public function isStatus()
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return MedicineSales
     */
    public function getMedicineSales()
    {
        return $this->medicineSales;
    }

    /**
     * @return MedicinePurchase
     */
    public function getMedicinePurchases()
    {
        return $this->medicinePurchases;
    }

    /**
     * @return Expenditure
     */
    public function getExpenditure()
    {
        return $this->expenditure;
    }

    /**
     * @return MedicineAndroidProcess
     */
    public function getMedicineAndroidProcess()
    {
        return $this->medicineAndroidProcess;
    }

    /**
     * @return BusinessAndroidProcess
     */
    public function getBusinessAndroidProcess()
    {
        return $this->businessAndroidProcess;
    }

    /**
     * @return InventoryAndroidProcess
     */
    public function getInventoryAndroidProcess()
    {
        return $this->inventoryAndroidProcess;
    }

    /**
     * @return RestaurantAndroidProcess
     */
    public function getRestaurantAndroidProcess()
    {
        return $this->restaurantAndroidProcess;
    }

    /**
     * @return ExpenseAndroidProcess
     */
    public function getExpenseAndroidProcess()
    {
        return $this->expenseAndroidProcess;
    }


}

