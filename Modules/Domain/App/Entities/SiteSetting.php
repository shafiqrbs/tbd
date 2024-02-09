<?php

namespace Modules\Domain\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * SiteSetting
 * @ORM\Entity
 * @ORM\Table(name="dom_site_setting")
 * @ORM\Entity(repositoryClass="Modules\Domain\App\Repositories\SiteSettingRepository")
 */
class SiteSetting
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
     * @ORM\OneToOne(targetEntity="Modules\Core\App\Entities\User", inversedBy="siteSetting")
     **/

    protected $user;

    /**
     * @ORM\OneToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption", inversedBy="siteSetting")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/

    protected $globalOption;








    /**
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\Theme", inversedBy="siteSetting")
     **/

    protected $theme = null;


    /**
     * @var string
     *
     * @ORM\Column(name="uniqueCode", type="string", length=255, nullable=true)
     */
    private $uniqueCode;


    public function _constructor()
    {
        $this->syndicates = new ArrayCollection();
        $this->modules = new ArrayCollection();
        $this->syndicateModules = new ArrayCollection();
        if(!$this->getId()){

            $passcode =substr(str_shuffle(str_repeat('0123456789',5)),0,4);
            $t = microtime(true);
            $micro = ($passcode + floor($t));
            $this->uniqueCode = $micro;
        }

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
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * @param mixed $mobileTheme
     */
    public function setMobileTheme($mobileTheme)
    {
        $this->mobileTheme = $mobileTheme;
    }

    /**
     * @return mixed
     */
    public function getMobileTheme()
    {
        return $this->mobileTheme;
    }

    /**
     * @param mixed $webTheme
     */
    public function setWebTheme($webTheme)
    {
        $this->webTheme = $webTheme;
    }

    /**
     * @return mixed
     */
    public function getWebTheme()
    {
        return $this->webTheme;
    }


    /**
     * @param mixed $syndicates
     */
    public function setSyndicates($syndicates)
    {
        $this->syndicates = $syndicates;
    }

    /**
     * @return mixed
     */
    public function getSyndicates()
    {
        return $this->syndicates;
    }

    /**
     * @param Module $modules
     */
    public function setModules($modules)
    {
        $this->modules = $modules;
    }

    /**
     * @return Module
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * @return mixed
     */
    public function hasModule($module)
    {
        if(empty($this->modules)) {
            return false;
        }
        return $this->modules->contains($module);
    }


    /**
     * @return mixed
     */
    public function getModuleIds()
    {
        $arr =array();
        if(!empty($this->getModules())) {
            foreach ($this->getModules() as $mod ){
                $arr[]= $mod->getId();
            }
        }
        return $arr;

    }



    /**
     * @param mixed $syndicateModules
     */
    public function setSyndicateModules($syndicateModules)
    {
        $this->syndicateModules = $syndicateModules;
    }

    /**
     * @return mixed
     */
    public function getSyndicateModules()
    {
        return $this->syndicateModules;
    }

    /**
     * @return mixed
     */
    public function hasSyndicateModule($syndicateModule)
    {
        if(empty($this->syndicateModules)) {
            return false;
        }

        return $this->syndicateModules->contains($syndicateModule);
    }


    /**
     * @return string
     */
    public function getUniqueCode()
    {
        return $this->uniqueCode;
    }

    /**
     * @param string $uniqueCode
     */
    public function setUniqueCode($uniqueCode)
    {
        $this->uniqueCode = $uniqueCode;
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
    public function getGlobalOption()
    {
        return $this->globalOption;
    }

    /**
     * @param mixed $globalOption
     */
    public function setGlobalOption($globalOption)
    {
        $this->globalOption = $globalOption;
    }

    /**
     * @return mixed
     */
    public function getAppModules()
    {
        return $this->appModules;
    }

    /**
     * @return mixed
     */
    public function hasAppModule($appModule)
    {
        if(empty($this->appModules)) {
            return false;
        }

        return $this->appModules->contains($appModule);
    }


    /**
     * @param mixed $appModules
     */
    public function setAppModules($appModules)
    {
        $this->appModules = $appModules;
    }

    /**
     * @return mixed
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param mixed $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }


}
