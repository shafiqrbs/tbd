<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoiceParticularTestReport
 *
 * @ORM\Table(name = "hms_invoice_particular_test_report")
 * @ORM\Entity()
 */
class InvoiceParticularTestReport
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
     * @ORM\OneToOne(targetEntity="InvoiceParticular")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $invoiceParticular;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $technique;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $findings;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $parenchyma;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $mediastinumVessels;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $mediastinumTrachea;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $mediastinumOesophagus;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $mediastinumThymus;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $mediastinumLymphNodes;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $heart;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $pleura;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $bones;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $afterIvContrast;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $impression;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $trachea;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $diaphragm;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $lungs;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $bonyThorax;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $rifResistanceNotDetected;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
     private $rifResistanceDetected;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $rifResistanceIndeterminate;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
     private $MtbNotDetected;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
     private $invalid;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
     private $sarsCov;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
     private $dengueNs;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $TbHospital;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $TbRegistrationNumber;



      /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $DrTbRegistrationNumber;



      /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $ETbRegistrationNumber;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $diagnosis;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $followUpMonth;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $otherTestMicroscopy;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $otherGeneXpert;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $otherCulture;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $otherDst;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $firstLineInh;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $firstLineRif;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $secondLineSr;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $secondLineResistanceLevel;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $secondRecommendation;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $secondLineKmAmCm;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $secondLineKmCm;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $secondLineLowHeightKmCm;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $nonInterpretable;



     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $flqLowResistance;



     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $flqHighResistance;



     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $flqLowToHigh;



     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $islResistantKmAmCm;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $islResistantKmCm;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $islResistantLowKmToHighCm;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $specimenIdentificationNumber;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $referralCenter;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $typePatient;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $lastCovidTestCenter;

     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $specimen;

     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $preservative;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $testType;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $geneXpertHospital;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $referenceLaboratorySpecimenId;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $sarsCovPositive;



    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $presumptivePos;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $sarsCovnegative;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $covInvalid;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $afbNoFound;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $afbScanty;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $afbScantyOne;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $afbScantyTwo;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $afbScantyThree;




    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateSpecimenCollection;

     /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateSpecimenReceived;

     /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
     private $lastCovidTestDate;



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

