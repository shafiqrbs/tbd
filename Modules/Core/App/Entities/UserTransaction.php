<?php


namespace Modules\Core\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Domain\App\Entities\GlobalOption;


/**
 * UserTransaction
 *
 * @Gedmo\Tree(type="materializedPath")
 * @ORM\Table(name="cor_user_transaction")
 * @ORM\Entity()
 */

class UserTransaction
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
     * @var User
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected $user;

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
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $maxDiscount = 0;

     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $discountPercent = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $salesTarget = 0;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return float
     */
    public function getMaxDiscount()
    {
        return $this->maxDiscount;
    }

    /**
     * @param float $maxDiscount
     */
    public function setMaxDiscount($maxDiscount)
    {
        $this->maxDiscount = $maxDiscount;
    }

    /**
     * @return float
     */
    public function getDiscountPercent()
    {
        return $this->discountPercent;
    }

    /**
     * @param float $discountPercent
     */
    public function setDiscountPercent($discountPercent)
    {
        $this->discountPercent = $discountPercent;
    }

    /**
     * @return float
     */
    public function getSalesTarget()
    {
        return $this->salesTarget;
    }

    /**
     * @param float $salesTarget
     */
    public function setSalesTarget($salesTarget)
    {
        $this->salesTarget = $salesTarget;
    }

}
