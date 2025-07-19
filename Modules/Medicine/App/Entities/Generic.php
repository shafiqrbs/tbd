<?php

namespace Modules\Medicine\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BrandCompany
 *
 * @ORM\Table("medi_generic")
 * @ORM\Entity()
 */
class Generic
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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="generic_id", type="string", length=255, nullable=true)
     */
    private $genericId;


    /**
     * @var string
     *
     * @ORM\Column(name="precaution", type="string", length=255, nullable=true)
     */
    private $precaution;


    /**
     * @var string
     *
     * @ORM\Column(name="indication", type="string", length=255, nullable=true)
     */
    private $indication;


    /**
     * @var string
     *
     * @ORM\Column(name="contraIndication", type="string", length=255, nullable=true)
     */
    private $contraIndication;


    /**
     * @var string
     *
     * @ORM\Column(name="dose", type="string", length=255, nullable=true)
     */
    private $dose;


    /**
     * @var string
     *
     * @ORM\Column(name="sideEffect", type="string", length=255, nullable=true)
     */
    private $sideEffect;


    /**
     * @var string
     *
     * @ORM\Column(name="modeOfAction", type="string", length=255, nullable=true)
     */
    private $modeOfAction;

    /**
     * @var string
     *
     * @ORM\Column(name="interaction", type="string", length=255, nullable=true)
     */
    private $interaction;


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

