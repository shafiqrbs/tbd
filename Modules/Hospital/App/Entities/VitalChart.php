<?php

namespace Modules\Hospital\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * VitalChart
 *
 * @ORM\Table(name="hms_vital_chart")
 * @ORM\Entity()
 */
class VitalChart
{
    /**
     * @var integer
     *
     * @Gedmo\TreePathSource
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


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
     * @var int
     *
     * @ORM\Column(name="sat_liter", type="integer",length=4, nullable = true)
     */
    private $satLiter;

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
     * @ORM\Column(name="time", type="string" , length=20)
     */
    private $time;

    /**
     * @var string
     *
     * @ORM\Column(name="am_pm", type="string" , length=20)
     */
    private $ampm;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $createdDate;


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

