<?php

namespace Modules\Domain\App\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;



/**
 * @ORM\Entity
 * @ORM\Table(name="dom_module")
 * @ORM\Entity(repositoryClass="Modules\Domain\App\Entities\ModuleRepository")
 */
class Module
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
     * @ORM\Column(name="name", type="string", length=255 , unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="moduleClass", type="string", length=255 , unique=true)
     */
    private $moduleClass;

    /**
     * @var string
     *
     * @ORM\Column(name="menu", type="string", length=255)
     */
    private $menu;

    /**
     * @Gedmo\Slug(fields={"menu"})
     * @ORM\Column(length=128, unique=true)
     */
    private $menuSlug;

    /**
     * @Gedmo\Slug(fields={"moduleClass"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float" , nullable = true)
     */
    private $price;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isSingle", type="boolean" , nullable = true)
     */
    private $isSingle = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isHome", type="boolean" , nullable = true)
     */
    private $isHome;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text" , nullable = true)
     */
    private $description;



    protected  $nav;

    public function __construct() {


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
     * @return Module
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
     * Set menu
     *
     * @param string $menu
     * @return Module
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * Get menu
     *
     * @return string
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Set menuSlug
     *
     * @param string $menuSlug
     * @return Module
     */
    public function setMenuSlug($menuSlug)
    {
        $this->menuSlug = $menuSlug;

        return $this;
    }

    /**
     * Get menuSlug
     *
     * @return string
     */
    public function getMenuSlug()
    {
        return $this->menuSlug;
    }

    /**
     * Set status
     *
     * @param boolean $status
     * @return Module
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Module
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $siteSetting
     */
    public function setSiteSetting($siteSetting)
    {
        $this->siteSetting = $siteSetting;
    }

    /**
     * @return mixed
     */
    public function getSiteSettings()
    {
        return $this->siteSettings;
    }

    /**
     * @param string $moduleClass
     */
    public function setModuleClass($moduleClass)
    {
        $this->moduleClass = $moduleClass;
    }

    /**
     * @return string
     */
    public function getModuleClass()
    {
        return $this->moduleClass;
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
    public function getHomePages()
    {
        return $this->homePages;
    }

    /**
     * @return boolean
     */
    public function isIsHome()
    {
        return $this->isHome;
    }

    /**
     * @param boolean $isHome
     */
    public function setIsHome($isHome)
    {
        $this->isHome = $isHome;
    }

    /**
     * @return boolean
     */
    public function isIsSingle()
    {
        return $this->isSingle;
    }

    /**
     * @param boolean $isSingle
     */
    public function setIsSingle($isSingle)
    {
        $this->isSingle = $isSingle;
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
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }


    /**
     * @return PageModule
     */
    public function getPageModules()
    {
        return $this->pageModules;
    }

    /**
     * @return ModuleCategory
     */
    public function getModuleCategory()
    {
        return $this->moduleCategory;
    }

    /**
     * @return PhotoGallery
     */
    public function getPhotoGallery()
    {
        return $this->photoGallery;
    }

    /**
     * @return Page
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @return FeatureWidget
     */
    public function getFeatureWidgets()
    {
        return $this->featureWidgets;
    }

    /**
     * @return SidebarWidgetPanel
     */
    public function getSidebarWidgetPanel()
    {
        return $this->sidebarWidgetPanel;
    }

    public function getModuleBasePostCategory($globalOption){

        $cats = array();
        foreach($this->getModuleCategory() AS $cat) {
            if ($cat->getGlobalOption()->getId() == $globalOption ){
                $cats[] = $cat->getName(); //$recipecost now $this->recipecost.
            }
        }
        return $cats ;
    }



}
