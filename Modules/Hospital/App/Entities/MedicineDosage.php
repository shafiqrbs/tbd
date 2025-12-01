<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Particular
 *
 * @ORM\Table( name ="hms_medicine_dosage")
 * @ORM\Entity()
 */
class MedicineDosage
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
     * @ORM\ManyToOne(targetEntity="Config" , cascade={"detach","merge"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     **/
    private  $createdBy;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="name_bn", type="string", length=255, nullable=true)
     */
    private $nameBn;


    /**
     * @var string
     *
     * @ORM\Column(name="dosage_form", type="string", length=100, nullable=true)
     */
    private $dosageForm;


    /**
     * @var string
     *
     * @ORM\Column(name="mode", type="string", length=20, nullable=true)
     */
    private $mode;

    /**
     * @var string
     *
     * @ORM\Column(name="continue_mode", type="string", length=20, nullable=true,options={"default"="Stat"})
     * Stat|sos
     */
    private $continueMode;

    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="duration", type="string", options={"default"="1"})
     */
    private $duration;

    /**
     * @var float
     *
     * @ORM\Column(name="duration_mode", type="string",options={"default"="day"})
     */
    private $durationMode;

    /**
     * @var int
     *
     * @ORM\Column(type="integer",options={"default"="999"})
     */
    private $ordering;

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

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $status;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isPrivate;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $isDelete;


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

