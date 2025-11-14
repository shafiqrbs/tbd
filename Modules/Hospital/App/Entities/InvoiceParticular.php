<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoiceParticular
 *
 * @ORM\Table(name = "hms_invoice_particular")
 * @ORM\Entity()
 */
class InvoiceParticular
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
     * @ORM\ManyToOne(targetEntity="Prescription")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $prescription;

    /**
     * @ORM\ManyToOne(targetEntity="InvoiceTransaction")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $invoiceTransaction;

    /**
     * @ORM\ManyToOne(targetEntity="PatientWaiver")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $patientWaiver;


    /**
     * @ORM\ManyToOne(targetEntity="InvoicePathologicalGroup")
     * @ORM\JoinColumn(nullable=true,onDelete="SET NULL")
     **/
    private $invoicePathologicalGroup;

     /**
     * @ORM\ManyToOne(targetEntity="AdmissionPatient")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $admissionPatientParticular;


    /**
     * @ORM\ManyToOne(targetEntity="Particular", inversedBy="invoiceParticular")
     * @ORM\JoinColumn(name="particular_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private $particular;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private $assignDoctor;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private $assignLabuser;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="report_delivered_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $reportDeliveredBy;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="sample_collected_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $sampleCollectedBy;

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
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="barcode", type="string",  nullable=true)
     */
    private $barcode;


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
    private $discountPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="commission", type="float", nullable=true)
     */
    private $commission;

    /**
     * @var string
     *
     * @ORM\Column( type="decimal", nullable=true)
     */
    private $estimatePrice;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",nullable=true)
     */
    private $customPrice = false;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $subTotal;

    /**
     * @var string
     *
     * @ORM\Column(type="process" , type="string", length=30,options={"default"="New"})
     */
    private $process;


    /**
     * @var string
     *
     * @ORM\Column(type="mode" , type="string", length=30,options={"default"="investigation"})
     */
    private $mode;


    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="json_report", type="json", nullable=true)
     */
    private $json_report;

     /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="sample_collected_name", type="string", nullable=true)
     */
    private $sampleCollectedName;

    /**
     * @var string
     *
     * @ORM\Column(name="report_delivered_name", type="string", nullable=true)
     */
    private $reportDeliveredName;

    /**
     * @var string
     *
     * @ORM\Column(name="assign_labuser_name", type="string", nullable=true)
     */
    private $assignLabuserName;

    /**
     * @var string
     *
     * @ORM\Column(name="assign_doctor_name", type="string", nullable=true)
     */
    private $assignDoctorName;


    /**
     * @var \DateTime
     * @ORM\Column(name="collection_date", type="datetime", nullable=true)
     */
    private $collectionDate;

     /**
     * @var \DateTime
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=true, options={"default"="false"})
     */
    private $status;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_invoice", type="boolean", nullable=true, options={"default"="false"})
     */
     private $isInvoice;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true, options={"default"="false"})
     */
     private $isWaver;

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
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $path;


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

