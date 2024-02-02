<?php

namespace Domain\DomainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MedicineBrand
 *
 * @ORM\Table(name="medicine_brand",
 *      indexes={
 *     @ORM\Index(name="IDX_8DAFD128F7A6EA0", columns={"medicineCompany_id"}),
 *     @ORM\Index(name="IDX_8DAFD12A010EB10", columns={"medicineGeneric_id"}),
 *     @ORM\Index(name="IDX_8DAFD12DC938C82", columns={"globalOption_id"})
 * })
 * @ORM\Entity
 */
class MedicineBrand
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="generic_id", type="string", length=255, nullable=true)
     */
    private $genericId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="company_id", type="string", length=255, nullable=true)
     */
    private $companyId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="medicineForm", type="string", length=255, nullable=true)
     */
    private $medicineform;

    /**
     * @var string|null
     *
     * @ORM\Column(name="strength", type="string", length=100, nullable=true)
     */
    private $strength;

    /**
     * @var float|null
     *
     * @ORM\Column(name="price", type="float", precision=10, scale=0, nullable=true)
     */
    private $price;

    /**
     * @var string|null
     *
     * @ORM\Column(name="packSize", type="string", length=100, nullable=true)
     */
    private $packsize;

    /**
     * @var string|null
     *
     * @ORM\Column(name="dar", type="string", length=100, nullable=true)
     */
    private $dar;

    /**
     * @var string|null
     *
     * @ORM\Column(name="useFor", type="string", length=50, nullable=true)
     */
    private $usefor;

    /**
     * @var string|null
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    private $status;

    /**
     * @var \DomGlobalOption
     *
     * @ORM\ManyToOne(targetEntity="DomGlobalOption")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="globalOption_id", referencedColumnName="id")
     * })
     */
    private $globaloption;

    /**
     * @var \MedicineCompany
     *
     * @ORM\ManyToOne(targetEntity="MedicineCompany")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="medicineCompany_id", referencedColumnName="id")
     * })
     */
    private $medicinecompany;


    /**
     * @var \MedicineGeneric
     *
     * @ORM\ManyToOne(targetEntity="MedicineGeneric")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="medicineGeneric_id", referencedColumnName="id")
     * })
     */
    private $medicinegeneric;


}
