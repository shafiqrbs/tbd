<?php

namespace Modules\Hospital\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * DoctorInvoiceParticular
 *
 * @ORM\Table( name = "hms_doctor_invoice_particular")
 * @ORM\Entity()
 */
class DoctorInvoiceParticular
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
     * @ORM\ManyToOne(targetEntity="DoctorInvoice", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="config_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $doctorInvoice;


    /**
     * @ORM\ManyToOne(targetEntity="Particular", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="Particular_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $particular;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="smallint")
     */
    private $quantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(name="salesPrice", type="float")
     */
    private $salesPrice;


    /**
     * @var string
     *
     * @ORM\Column(name="estimatePrice", type="decimal")
     */
    private $estimatePrice;

    /**
     * @var string
     *
     * @ORM\Column(name="commission", type="decimal")
     */
    private $commission;


    /**
     * @var float
     *
     * @ORM\Column(name="subTotal", type="float")
     */
    private $subTotal;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="createAt")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="updateAt")
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

