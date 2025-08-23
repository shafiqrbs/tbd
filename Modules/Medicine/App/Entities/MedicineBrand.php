<?php

namespace Modules\Medicine\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * MedicineBrand
 *
 * @ORM\Table("medicine_brand")
 * @ORM\Entity()
 */
class MedicineBrand
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
     * @ORM\ManyToOne(targetEntity="Generic")
     * @ORM\JoinColumn(name="medicineGeneric_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $medicineGeneric;

    /**
     * @ORM\ManyToOne(targetEntity="MedicineCompany")

     * @ORM\JoinColumn(name="medicineCompany_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")**/
    private $medicineCompany;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255), nullable=true
     */
    private $name;

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
     * @ORM\Column(name="generic_id", type="string", length=255, nullable=true)
     */
    private $genericId;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


}

