<?php

namespace Modules\Hospital\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoicePathologicalGroup
 *
 * @ORM\Table( name = "hms_invoice_pathological_group")
 * @ORM\Entity()
 */
class InvoicePathologicalGroup
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
     * @ORM\ManyToOne(targetEntity="Invoice")
     **/
    private $invoice;

     /**
     * @ORM\ManyToOne(targetEntity="Particular", inversedBy="invoiceParticularDoctor")
     **/
    private $assignDoctor;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     **/
    private $assignLabuser;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Category")
     **/
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="particular_delivered_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $particularDeliveredBy;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=30, nullable=true)
     */
    private $process ='In-progress';

    /**
     * @var text
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

}

