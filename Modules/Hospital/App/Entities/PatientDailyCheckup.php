<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoiceParticular
 *
 * @ORM\Table( name = "hms_admission_patient_daily_checkup")
 * @ORM\Entity()
 */
class PatientDailyCheckup
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
     * @var string
     *
     * @ORM\Column(name="diabetes", type="string",length=30, nullable = true)
     */
    private $diabetes;

    /**
     * @var string
     *
     * @ORM\Column(name="weight", type="string",length=50, nullable = true)
     */
    private $weight;

    /**
     * @var string
     *
     * @ORM\Column(name="height", type="string",length=50, nullable = true)
     */
    private $height;

    /**
     * @var string
     *
     * @ORM\Column(name="bp", type="string",length=50, nullable = true)
     */
    private $bp;

    /**
     * @var string
     *
     * @ORM\Column(name="oxygen", type="string",length=50, nullable = true)
     */
    private $oxygen;

    /**
     * @var string
     *
     * @ORM\Column(name="temperature", type="string",length=50, nullable = true)
     */
    private $temperature;

    /**
     * @var string
     *
     * @ORM\Column(name="sat_with_O2", type="string",length=50, nullable = true)
     */
    private $satWithO2;

    /**
     * @var string
     *
     * @ORM\Column(name="sat_without_O2", type="string",length=50, nullable = true)
     */
    private $satWithoutO2;

    /**
     * @var string
     *
     * @ORM\Column(name="respiration", type="string",length=50, nullable = true)
     */
    private $respiration;

    /**
     * @var string
     *
     * @ORM\Column(name="pulse", type="string",length=50, nullable = true)
     */
    private $pulse;

    /**
     * @var string
     *
     * @ORM\Column(name="blood_sugar", type="string",length=50, nullable = true)
     */
    private $bloodSugar;

    /**
     * @var string
     *
     * @ORM\Column(type="text",  nullable =true)
     */
    private $comment;

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

