<?php

namespace Modules\Inventory\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Requisition
 *
 * @ORM\Table(name ="inv_requisition")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\RequisitionRepository")
 */
class Requisition
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(name="vendor_config_id", onDelete="CASCADE")
     **/
    private  $vendorConfig;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(name="customer_config_id", onDelete="CASCADE")
     **/
    private  $customerConfig;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Vendor")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $vendor;

    /**
     * @var string
     * @ORM\Column(name="invoice", type="string", length=255, nullable=true)
     */
    private $invoice;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Customer")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $customer;

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
     * @var \Date
     * @ORM\Column(type="date", nullable=true)
     */
    private $invoiceDate;

    /**
     * @var \Date
     * @ORM\Column(type="date", nullable=true)
     */
    private $expectedDate;


    /**
     * @var float
     * @ORM\Column(type="float", nullable=true)
     */
    private $subTotal;

    /**
     * @var float
     * @ORM\Column(name="discount", type="float", nullable=true)
     */
    private $discount;

    /**
     * @var string
     * @ORM\Column( type="string", length=20, nullable=true)
     */
    private $discountType ='flat';

    /**
     * @var float
     * @ORM\Column(type="float" , nullable=true)
     */
    private $discountCalculation;

    /**
     * @var float
     * @ORM\Column(name="total", type="float", nullable=true)
     */
    private $total;

    /**
     * @var float
     * @ORM\Column(name="payment", type="float", nullable=true)
     */
    private $payment;

    /**
     * @var float
     * @ORM\Column(name="due", type="float", nullable=true)
     */
    private $due;

    /**
     * @var boolean
     * @ORM\Column(name="status", type="boolean")
     */
    private $status=true;

	/**
     * @var string
     * @ORM\Column(name="process", type="string", nullable=true)
     */
    private $process = "Created";

    /**
     * @var string
     * @ORM\Column(name="remark", type="text", nullable=true)
     */
    private $remark;


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

