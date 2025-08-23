<?php

namespace Modules\Medicine\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * MedicineBrand
 *
 * @ORM\Table("medi_medicine")
 * @ORM\Entity()
 */
class Medicine
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
     * @ORM\Column(name="generic", type="string", length=255, nullable=true)
     */
    private $generic;


    /**
     * @var string
     *
     * @ORM\Column(name="display_name", type="string", length=255, nullable=true)
     */
    private $displayName;


    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     */
    private $company;


    /**
     * @var string
     *
     * @ORM\Column(name="formulation", type="string", length=100, nullable=true)
     */
    private $formulation;


    /**
     * @var string
     *
     * @ORM\Column(name="dose_details", type="string", length=100, nullable=true)
     */
    private $doseDetails;


    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $genericId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $dosesForm;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $dosesDetails;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $byMeal;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $durationMonth;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $durationDay;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", length=5, nullable=true)
     */
    private $priority;

    /**
     * @var string
     *
     * @ORM\Column(name="dar", type="string", length=100, nullable=true)
     */
    private $dar;

     /**
     * @var string
     *
     * @ORM\Column(name="useFor", type="string", length=50, nullable=true)
     */
    private $useFor;

    /**
     * @var string
     *
     * @ORM\Column(name="brand_id", type="string", length=255, nullable=true)
     */
    private $brandId;

    /**
     * @var string
     *
     * @ORM\Column(name="company_id", type="string", length=255, nullable=true)
     */
    private $companyId;


    /**
     * @var string
     *
     * @ORM\Column(name="medicineForm", type="string", length=255, nullable=true)
     */
    private $medicineForm;


    /**
     * @var string
     *
     * @ORM\Column(name="strength", type="string", length=100, nullable=true)
     */
    private $strength;


    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", length=255, nullable=true)
     */
    private $price;


    /**
     * @var string
     *
     * @ORM\Column(name="packSize", type="string", length=100, nullable=true)
     */
    private $packSize;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    private $status = true;


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

