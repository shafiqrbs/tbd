<?php

namespace Modules\Core\App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Country
 *
 * @ORM\Table(name="cor_countries")
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
     * Set countryCode
     *
     * @param string $countryCode
     *
     * @return Country
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get countryCode
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getVendors()
    {
        return $this->vendors;
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return mixed
     */
    public function getStockItems()
    {
        return $this->stockItems;
    }

    /**
     * @return mixed
     */
    public function getPurchaseVendorItems()
    {
        return $this->purchaseVendorItems;
    }


    /**
     * @return string
     */
    public function getIso3()
    {
        return $this->iso3;
    }

    /**
     * @param string $iso3
     */
    public function setIso3($iso3)
    {
        $this->iso3 = $iso3;
    }

    /**
     * @return string
     */
    public function getIso()
    {
        return $this->iso;
    }

    /**
     * @param string $iso
     */
    public function setIso($iso)
    {
        $this->iso = $iso;
    }

    /**
     * @return string
     */
    public function getNumcode()
    {
        return $this->numcode;
    }

    /**
     * @param string $numcode
     */
    public function setNumcode($numcode)
    {
        $this->numcode = $numcode;
    }

    /**
     * @return string
     */
    public function getPhonecode()
    {
        return $this->phonecode;
    }

    /**
     * @param string $phonecode
     */
    public function setPhonecode($phonecode)
    {
        $this->phonecode = $phonecode;
    }

    /**
     * @return string
     */
    public function getNicename()
    {
        return $this->nicename;
    }

    /**
     * @param string $nicename
     */
    public function setNicename($nicename)
    {
        $this->nicename = $nicename;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;
    }
}

