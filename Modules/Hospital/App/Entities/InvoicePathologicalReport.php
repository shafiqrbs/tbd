<?php

namespace Modules\Hospital\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoicePathologicalReport
 *
 * @ORM\Table( name = "hms_invoice_pathological_report")
 * @ORM\Entity()
 */
class InvoicePathologicalReport
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
     * @ORM\ManyToOne(targetEntity="InvoiceParticular")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $invoiceParticular;

     /**
     * @ORM\ManyToOne(targetEntity="PathologicalReport")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $pathologicalReport;

    /**
     * @var string
     *
     * @ORM\Column(name="result", type="string", length=100, nullable=true)
     */
    private $result;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_key", type="string", length=255, nullable=true)
     */
    private $metaKey;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_value", type="text", nullable=true)
     */
    private $metaValue;

    /**
     * @var text
     *
     * @ORM\Column(name="remark", type="text", nullable=true)
     */
    private $remark;

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

