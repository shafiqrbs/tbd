<?php


namespace Modules\Core\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Category
 *
 * @Gedmo\Tree(type="materializedPath")
 * @ORM\Table(name="cor_locations")
 * @ORM\Entity(repositoryClass="Modules\Core\App\Repositories\LocationRepository")
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="children")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * })
     */
    private $parent;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="level", type="integer", nullable=true)
     */
    private $level;

    /**
     * @ORM\OneToMany(targetEntity="Location" , mappedBy="parent")
     * @ORM\OrderBy({"name" = "ASC"})
     **/
    private $children;

    /**
     * @Gedmo\TreePath(separator="/")
     * @ORM\Column(name="path", type="string", length=3000, nullable=true)
     */
    private $path;


    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Location
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Location
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getNestedLabel()
    {
        if ($this->getLevel() > 1) {
            return $this->formatLabel($this->getLevel() - 1, $this->getName());
        } else {
            return $this->getName();
        }
    }

    public function getParentIdByLevel($level = 1)
    {
        $parentsIds = explode("/", $this->getPath());

        return isset($parentsIds[$level - 1]) ? $parentsIds[$level - 1] : null;

    }

    private function formatLabel($level, $value)
    {
        return str_repeat("-", $level * 3) . str_repeat(">", $level) . $value;
    }

    /**
     * @return mixed
     */
    public function getEducations()
    {
        return $this->educations;
    }

    /**
     * @return mixed
     */
    public function getScholarships()
    {
        return $this->scholarships;
    }

    /**
     * @return mixed
     */
    public function getTutors()
    {
        return $this->tutors;
    }

    /**
     * @return GlobalOption
     */
    public function getGlobalOptions()
    {
        return $this->globalOptions;
    }

    /**
     * @return Order
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @return Profile
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

    /**
     * @return Customer
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    /**
     * @return PreOrder
     */
    public function getPreOrders()
    {
        return $this->preOrders;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return Particular
     */
    public function getParticulars()
    {
        return $this->particulars;
    }


    /**
     * @return DpsParticular
     */
    public function getDpsParticulars()
    {
        return $this->dpsParticulars;
    }

	/**
	 * @return ElectionLocation
	 */
	public function getElectionLocations() {
		return $this->electionLocations;
	}

}
