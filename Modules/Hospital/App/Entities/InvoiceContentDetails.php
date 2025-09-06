<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoiceContentDetails
 *
 * @ORM\Table( name = "hms_invoice_content_details")
 * @ORM\Entity()
 */
class InvoiceContentDetails
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
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $hmsInvoice;

    /**
     * @ORM\ManyToOne(targetEntity="Prescription")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $prescription;

    /**
     * @ORM\ManyToOne(targetEntity="ParticularType", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="particular_type_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $particularType;

    /**
     * @ORM\ManyToOne(targetEntity="Particular", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="particular_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $particular;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string",nullable=true)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text",nullable=true)
     */
    private $value;


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

