<?php


namespace Modules\Utility\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AccountTransactionMode
 *
 * @ORM\Table(name ="uti_country")
 * @ORM\Entity()
 */

class Country
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="nicename", type="string", length=255)
     */
    private $nicename;

    /**
     * @var string
     *
     * @ORM\Column(name="countryCode", type="string", length=25)
     */
    private $countryCode;

    /**
     * @var string
     *
     * @ORM\Column(name="phonecode", type="string", length=25)
     */
    private $phonecode;

    /**
     * @var string
     *
     * @ORM\Column(name="numcode", type="string", length=25)
     */
    private $numcode;

    /**
     * @var string
     *
     * @ORM\Column(name="iso", type="string", length=25)
     */
    private $iso;

    /**
     * @var string
     *
     * @ORM\Column(name="iso3", type="string", length=25)
     */
    private $iso3;


    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=25)
     */
    private $code;


    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=25)
     */
    private $currency;


    /**
     * @var string
     *
     * @ORM\Column(name="symbol", type="string", length=25)
     */
    private $symbol;


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
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $updatedAt;


}
