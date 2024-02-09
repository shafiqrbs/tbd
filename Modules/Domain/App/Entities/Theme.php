<?php

namespace Modules\Domain\App\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/**
 * Theme
 *
 * @ORM\Table(name="dom_theme")
 * @ORM\Entity(repositoryClass="Modules\Domain\App\Repositories\ThemeRepository")
 */
class Theme
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
     * @ORM\OneToMany(targetEntity="Modules\Domain\App\Entities\SiteSetting", mappedBy="theme")
     **/

    private $siteSetting;


    /**
     * @ORM\ManyToMany(targetEntity="Modules\Domain\App\Entities\Syndicate", inversedBy="themes")
     **/

    private $syndicates;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\AppModule", inversedBy="themes")
     **/

    private $app;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="folderName", type="string", length=255)
     */
    private $folderName;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status = true;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;

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
     *
     * @return Theme
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
     * Set folderName
     *
     * @param string $folderName
     *
     * @return Theme
     */
    public function setFolderName($folderName)
    {
        $this->folderName = $folderName;

        return $this;
    }

    /**
     * Get folderName
     *
     * @return string
     */
    public function getFolderName()
    {
        return $this->folderName;
    }

    /**
     * Set status
     *
     * @param boolean $status
     *
     * @return Theme
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
     * @return mixed
     */
    public function getSyndicates()
    {
        return $this->syndicates;
    }

    /**
     * @param mixed $syndicates
     */
    public function setSyndicates($syndicates)
    {
        $this->syndicates = $syndicates;
    }

    /**
     * Sets file.
     *
     * @param WebTheme $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return WebTheme
     */
    public function getFile()
    {
        return $this->file;
    }

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'uploads/setting/theme/';
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }


    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->getFile()->getClientOriginalName()
        );

        // set the path property to the filename where you've saved the file
        $this->path = $this->getFile()->getClientOriginalName();

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    /**
     * @return mixed
     */
    public function getSiteSetting()
    {
        return $this->siteSetting;
    }

    /**
     * @return AppModule
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param AppModule $app
     */
    public function setApp($app)
    {
        $this->app = $app;
    }

}

