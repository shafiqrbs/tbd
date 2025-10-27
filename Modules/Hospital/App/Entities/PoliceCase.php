<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * PoliceCase
 *
 * @ORM\Table( name = "hms_police_case")
 * @ORM\Entity()
 */
class PoliceCase
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
     * @ORM\OneToOne(targetEntity="Invoice")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $hmsInvoice;


     /**
     * @var string
     *
     * @ORM\Column(type="text",  nullable =true)
     */
    private $permanentAddress;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable =true)
     */
    private $caseNo;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable =true)
     */
    private $thana;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable =true)
     */
    private $dutyOfficer;


     /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable =true)
     */
    private $mobile;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable =true)
     */
    private $case_details;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable =true)
     */
    private $comment;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=30, nullable=true)
     */
    private $process ='In-progress';

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="approved_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $approvedBy;


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

