<?php

namespace Modules\Domain\App\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category
 *
 * @Gedmo\Tree(type="materializedPath")
 * @ORM\Table(name="dom_syndicate")
 * @ORM\Entity(repositoryClass="Modules\Domain\App\Repositories\SyndicateRepository")
 */
class Syndicate
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
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;


    /**
     * @var string
     * @ORM\Column(name="entityName", type="string", length=255, nullable=true)
     */
     private $entityName;


    /**
     * @var string
     * @ORM\Column(name="domainProperty", type="string", length=255, nullable=true)
     */
     private $domainProperty;


    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Syndicate", inversedBy="children")
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
     * @ORM\OneToMany(targetEntity="Syndicate" , mappedBy="parent"  , cascade={"remove"})
     * @ORM\OrderBy({"name" = "ASC"})
     **/
    private $children;

    /**
     * @Gedmo\TreePath(separator="/")
     * @ORM\Column(name="path", type="string", length=3000, nullable=true)
     */
    private $path;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status = true;



    public function __construct(){
        $this->syndicateModules = new ArrayCollection();
    }

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
     * @return Syndicate
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
     * @return Syndicate
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Syndicate $parent
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
    public function getNav()
    {
        return $this->nav;
    }

    /**
     * @param mixed $nav
     */
    public function setNav($nav)
    {
        $this->nav = $nav;
    }


    /**
     * @return mixed
     */
    public function getSiteSettings()
    {
        return $this->siteSettings;
    }

    /**
     * @return mixed
     */
    public function getHomePages()
    {
        return $this->homePages;
    }

    /**
     * @return mixed
     */
    public function getMobileThemes()
    {
        return $this->mobileThemes;
    }

    /**
     * @return mixed
     */
    public function getWebThemes()
    {
        return $this->webThemes;
    }


    /**
     * @return mixed
     */
    public function getSyndicateContent()
    {
        return $this->syndicateContent;
    }

    /**
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param boolean $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param string $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @return mixed
     */
    public function getMegaMenus()
    {
        return $this->megaMenus;
    }

    /**
     * @return mixed
     */
    public function getStudyAbroads()
    {
        return $this->studyAbroads;
    }

    /**
     * @return mixed
     */
    public function getTutors()
    {
        return $this->tutors;
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
    public function getGlobalOption()
    {
        return $this->globalOption;
    }

    /**
     * @return mixed
     */
    public function getDomainProperty()
    {
        return $this->domainProperty;
    }

    /**
     * @param mixed $domainProperty
     */
    public function setDomainProperty($domainProperty)
    {
        $this->domainProperty = $domainProperty;
    }

    /**
     * @return mixed
     */
    public function getThemes()
    {
        return $this->themes;
    }

    /**
     * @return mixed
     */
    public function getSyndicateModules()
    {
        return $this->syndicateModules;
    }

    /**
     * @param SyndicateModule $syndicateModule
     */
    public function addSyndicateModules($syndicateModule)
    {
        if($this->syndicateModules->contains($syndicateModule)) {
            $this->syndicateModules->add($syndicateModule);
        }
    }
}
