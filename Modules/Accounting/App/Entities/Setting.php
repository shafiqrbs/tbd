<?php

namespace Modules\Accounting\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Setting
 *
 * @ORM\Table( name = "acc_setting")
 * @ORM\Entity()
 */
class Setting
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
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\GlobalOption")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    protected $domain;

    /**
     * @ORM\ManyToOne(targetEntity="SettingType")
     **/
    private $settingType;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=255, unique=true)
     */
    private $slug;

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
     * @ORM\Column(name="status", type="boolean" )
     */
    private $status= true;



}

