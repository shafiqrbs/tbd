<?php

namespace Modules\Hospital\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * HmsCategory
 *
 * @ORM\Table(name="hms_insulin_chart")
 * @ORM\Entity()
 */
class InsulinChart
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
     * @ORM\Column(name="fbs", type="string", length=50)
     */
    private $fbs;


     /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    private $fbsInsulin;

    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    private $hafb;

    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    private $bl;


    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    private $blInsulin;


     /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    private $hal;


     /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    private $bd;

    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    private $bdInsulin;


    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    private $had;


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

