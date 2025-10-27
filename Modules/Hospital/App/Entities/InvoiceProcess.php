<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoiceParticular
 *
 * @ORM\Table( name = "hms_invoice_process_comment")
 * @ORM\Entity()
 */
class InvoiceProcess
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
     * @ORM\OneToOne(targetEntity="Invoice")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $hmsInvoice;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true, options={"default"="false"})
     */
    private $medicineDelivered;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="deleted_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $medicineDeliveredBy;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $medicineDeliveredComment;


     /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
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

