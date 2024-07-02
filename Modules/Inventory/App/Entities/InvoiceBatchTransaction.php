<?php

namespace Modules\Inventory\App\Entities;



use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Sales
 *
 * @ORM\Table( name ="inv_invoice_batch_transaction")
 * @ORM\Entity()
 */
class InvoiceBatchTransaction
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
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;

    /**
     * @ORM\ManyToOne(targetEntity="InvoiceBatch")
     * @ORM\JoinColumn(name="invoice_batch_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private $invoiceBatch;


    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private $salesBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $approvedBy;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=50, nullable=true)
     */
    private $process ='Created';

    /**
     * @var string
     *
     * @ORM\Column(name="invoice", type="string", length=50, nullable=true)
     */
    private $invoice;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $discountType ='';

    /**
     * @var float
     *
     * @ORM\Column(type="float" , nullable=true)
     */
    private $discountCalculation;

     /**
     * @var float
     *
     * @ORM\Column( type="float", nullable=true)
     */
    private $provisionDiscount;


    /**
     * @var string
     *
     * @ORM\Column( type="string", nullable=true)
     */
    private $provisionMode = 'item';


    /**
     * @var float
     *
     * @ORM\Column(name="discount", type="float", nullable=true)
     */
    private $discount;



    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", nullable=true)
     */
    private $amount;


    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isReversed;


    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $printPreviousDue = true;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $invoiceDate;

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

