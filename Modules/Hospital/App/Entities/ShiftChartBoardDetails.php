<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Service
 *
 * @ORM\Table( name ="hms_shift_chart_board_details")
 * @ORM\Entity()
 */
class ShiftChartBoardDetails
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
     * @ORM\ManyToOne(targetEntity="Config", cascade={"detach","merge"})
     * @ORM\JoinColumn(name="config_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $config;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $shiftStaffMode='Doctor';

    /**
     * @ORM\ManyToOne(targetEntity="ShiftChartBoard")
     * @ORM\JoinColumn(name="shift_chart_board_id", referencedColumnName="id", nullable=true)
     **/
    private  $shiftChartBoard;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="assign_user_id", referencedColumnName="id", nullable=true)
     **/
    private  $assignUser;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="visiting_ropd_oom_id", referencedColumnName="id", nullable=true)
     **/
    private  $opdRoom;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="visiting_ipd_room_id", referencedColumnName="id", nullable=true)
     **/
    private  $ipdRoom;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="visiting_ipd_cabin_id", referencedColumnName="id", nullable=true)
     **/
    private  $ipdCabin;

    /**
     * @ORM\ManyToOne(targetEntity="ParticularMode")
     * @ORM\JoinColumn(name="ipd_unit_id", referencedColumnName="id", nullable=true)
     **/
    private  $ipdUnit;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="true"})
     */
    private $status;

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
     * @ORM\Column(name="issue_date_at", type="datetime")
     */
    private $issueDate;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }



}

