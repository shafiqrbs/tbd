<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Invoice
 *
 * @ORM\Table( name ="hms_invoice_transaction_refund")
 * @ORM\Entity()
 */
class InvoiceTransactionRefund
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
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $uid;


    /**
     * @ORM\ManyToOne(targetEntity="Invoice")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $hmsInvoice;

    /**
     * @ORM\OneToOne(targetEntity="InvoiceTransaction")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $invoiceTransaction;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="approved_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $approvedBy;


    /**
     * @var string
     *
     * @ORM\Column(name="amount", type="decimal", nullable=true)
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_total", type="decimal", nullable=true)
     */
    private $subTotal= 0;

     /**
     * @var string
     *
     * @ORM\Column(name="total", type="decimal", nullable=true)
     */
    private $total= 0;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=50, nullable=true,options={"default"="New"})
     */
    private $process;

    /**
     * @var string
     *
     * @ORM\Column(name="mode", type="string", length=30, nullable=true)
     */
    private $mode;


    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
     private $code;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

}

