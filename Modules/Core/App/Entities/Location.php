<?php


namespace Modules\Core\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Domain\App\Entities\GlobalOption;


/**
 * Location
 *
 * @ORM\Table(name="cor_locations")
 * @ORM\Entity()
 */
class Location
{
    /**
     * @var integer
     *
     * @Gedmo\TreePathSource
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;


    /**
     * @var string
     * @ORM\Column(name="upazila", type="string", length=100)
     */
    private $upazila;

    /**
     * @var string
     * @ORM\Column(name="upazila_code", type="string", length=4)
     */
    private $upazilaCode;

    /**
     * @var string
     * @ORM\Column(name="district", type="string", length=100)
     */
    private $district;

    /**
     * @var string
     * @ORM\Column(name="district_code", type="string", length=4)
     */
    private $districtCode;

    /**
     * @var string
     * @ORM\Column(name="division", type="string", length=100)
     */
    private $division;

    /**
     * @var string
     * @ORM\Column(name="division_code", type="string", length=4)
     */
    private $divisionCode;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;




}
