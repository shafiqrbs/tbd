<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoiceParticular
 *
 * @ORM\Table(name = "hms_invoice_particular_refund")
 * @ORM\Entity()
 */

class InvoiceParticularRefund
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
     * @ORM\ManyToOne(targetEntity="InvoiceTransactionRefund")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $invoiceTransactionRefund;

     /**
     * @ORM\OneToOne(targetEntity="InvoiceParticular")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $invoiceParticular;


    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="particular_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $particular;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="smallint", nullable=true)
     */
    private $quantity = 1;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $subTotal;

     /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updated;


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

