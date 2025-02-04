<?php

namespace Modules\Utility\App\Entities;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * Theme
 *
 * @ORM\Table(name="uti_report_module")
 * @ORM\Entity()
 */
class ReportModule
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
     * @ORM\ManyToOne(targetEntity="Modules\Utility\App\Entities\Setting")
     **/

    private $module;


    /**
     * @var string
     *
     * @ORM\Column(name="name",unique=true, type="string", length=255)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="router_name", unique=true, type="string", length=255)
     */
    private $routerName;

    /**
     * @var string
     *
     * @ORM\Column(name="route_slug",unique=true, type="string", length=255)
     */
    private $routeSlug;


    /**
     * @Gedmo\Translatable
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=255, unique=true)
     */
    private $slug;


    /**
     * @var boolean
     *
     * @ORM\Column(name="status",options={"default":1}, type="boolean")
     */
    private $status = true;


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



}
