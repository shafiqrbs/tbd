<?php

namespace Modules\Domain\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Setting\Bundle\MediaBundle\Entity\PhotoGallery;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * AppModule
 *
 * @ORM\Table("dom_app_device_setup")
 * @ORM\Entity(repositoryClass="Modules\Domain\App\Repositories\AppModuleRepository")
 */
class AppModule
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
     * @ORM\ManyToMany(targetEntity="Modules\Domain\App\Entities\SiteSetting", mappedBy="appModules")
     **/

    private $siteSettings;

     /**
     * @ORM\OneToMany(targetEntity="Modules\Domain\App\Entities\GlobalOption", mappedBy="mainApp")
     **/
    private $appDomains;

     /**
     * @ORM\OneToMany(targetEntity="Modules\Domain\App\Entities\Theme", mappedBy="app")
     **/
    private $themes;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="moduleClass", type="string", length=255, nullable=true)
     */
    private $moduleClass;

    /**
     * @Gedmo\Slug(fields={"moduleClass"})
     * @ORM\Column(length=255, unique=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text" , nullable = true)
     */
    private $content;

	/**
     * @var string
     *
     * @ORM\Column(name="applicationManual", type="text" , nullable = true)
     */
    private $applicationManual;

	/**
     * @var string
     *
     * @ORM\Column(name="shortContent", type="text" , nullable = true)
     */
    private $shortContent;


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
    private $status = true;


    /**
     * @var boolean
     *
     * @ORM\Column(name="androidStatus", type="boolean")
     */
    private $androidStatus = false;


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
     * @return AppModule
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return AppModule
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return AppModule
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set status
     *
     * @param boolean $status
     *
     * @return AppModule
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
     * @return string
     */
    public function getModuleClass()
    {
        return $this->moduleClass;
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
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getSiteSettings()
    {
        return $this->siteSettings;
    }

    /**
     * @param mixed $siteSettings
     */
    public function setSiteSettings($siteSettings)
    {
        $this->siteSettings = $siteSettings;
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
     * @return mixed
     */
    public function getInvoiceModuleItems()
    {
        return $this->invoiceModuleItems;
    }

    /**
     * @return mixed
     */
    public function getAccessRoles()
    {
        return $this->accessRoles;
    }

    /**
     * @param PhotoGallery $photoGallery
     */
    public function setPhotoGallery($photoGallery)
    {
        $this->photoGallery = $photoGallery;
    }

    /**
     * @return PhotoGallery
     */
    public function getPhotoGallery()
    {
        return $this->photoGallery;
    }

    /**
     * Sets file.
     *
     * @param AppModule $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return AppModule
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
            : $this->getUploadDir().'/' . $this->path;
    }



    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'uploads/admin/content';
    }

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
        $filename = date('YmdHmi') . "_" . $this->getFile()->getClientOriginalName();
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $filename
        );

        // set the path property to the filename where you've saved the file
        $this->path = $filename ;

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }



    /**
     * @return ApplicationTestimonial
     */
    public function getApplicationTestimonial()
    {
        return $this->applicationTestimonial;
    }

	/**
	 * @return string
	 */
	public function getShortContent(){
		return $this->shortContent;
	}

	/**
	 * @param string $shortContent
	 */
	public function setShortContent( string $shortContent ) {
		$this->shortContent = $shortContent;
	}

    /**
     * @return GlobalOption
     */
    public function getAppDomains()
    {
        return $this->appDomains;
    }

    /**
     * @return string
     */
    public function getApplicationManual()
    {
        return $this->applicationManual;
    }

    /**
     * @param string $applicationManual
     */
    public function setApplicationManual($applicationManual)
    {
        $this->applicationManual = $applicationManual;
    }

    /**
     * @return Theme
     */
    public function getThemes()
    {
        return $this->themes;
    }

    /**
     * @return bool
     */
    public function isAndroidStatus()
    {
        return $this->androidStatus;
    }

    /**
     * @param bool $androidStatus
     */
    public function setAndroidStatus($androidStatus)
    {
        $this->androidStatus = $androidStatus;
    }
}

