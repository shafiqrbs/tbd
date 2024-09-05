<?php

namespace Modules\Production\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
/**
 * Damage
 *
 * @ORM\Table("pro_batch")
 * @ORM\Entity(repositoryClass="Modules\Production\App\Repositories\ProductionBatchRepository")
 * @Gedmo\Uploadable(filenameGenerator="SHA1", allowOverwrite=true, appendNumber=true)
 */
class ProductionBatch
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
     * @ORM\ManyToOne(targetEntity="Config")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Vendor")
     **/
     private  $vendor;


    /**
     * @var integer
     *
     * @ORM\Column(type="integer" , nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(type="string" , nullable=true, length=20)
     */
    private $mode = 'inhouse';


    /**
     * @var string
     *
     * @ORM\Column(type="string" , nullable=true)
     */
    private $invoice;


     /**
     * @var string
     *
     * @ORM\Column(type="string" , nullable=true)
     */
     private $remark;


    /**
     * @var string
     *
     * @ORM\Column(type="string" , nullable=true)
     */
     private $requisitionNo;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $createdBy;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $checkedBy;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private  $approvedBy;


    /**
     * @var string
     *
     * @ORM\Column(name="process", type="string", length=50, nullable = true)
     */
    private $process="created";

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", options={"default"="false"}, nullable = true)
     */
    private $status = false;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $address;


    /**
     * @var string
     *
     * @ORM\Column(type="string",nullable=true)
     */
    private $issuePerson;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $issueDesignation;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $issueTime;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $issueDate;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $receiveDate;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="updated_at", type="datetime", nullable = true)
     */
    private $updatedAt;


    /**
     * @ORM\Column(name="path", type="string", nullable=true)
     * @Gedmo\UploadableFilePath
     */
    protected $path;

    /**
     * @Assert\File(maxSize="8388608")
     */
    protected $file;




}

