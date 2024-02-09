<?php

namespace Modules\Domain\App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Templating
 *
 * @ORM\Table(name="dom_templating")
 * @ORM\Entity
 */
class Templating
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
     * @ORM\Column(name="backgroundColor", type="string", length=100)
     */
    private $backgroundColor;

    /**
     * @var string
     *
     * @ORM\Column(name="headerColor", type="string", length=50)
     */
    private $headerColor;

    /**
     * @var string
     *
     * @ORM\Column(name="footerColor", type="string", length=50)
     */
    private $footerColor;

    /**
     * @var string
     *
     * @ORM\Column(name="bodyColor", type="string", length=50)
     */
    private $bodyColor;

    /**
     * @var string
     *
     * @ORM\Column(name="menuColor", type="string", length=50)
     */
    private $menuColor;

    /**
     * @var string
     *
     * @ORM\Column(name="menuLiColor", type="string", length=50)
     */
    private $menuLiColor;

    /**
     * @var string
     *
     * @ORM\Column(name="menuLiHoverColor", type="string", length=50)
     */
    private $menuLiHoverColor;

    /**
     * @var integer
     *
     * @ORM\Column(name="siteFontSize", type="smallint")
     */
    private $siteFontSize;

    /**
     * @var string
     *
     * @ORM\Column(name="siteFontFamily", type="text")
     */
    private $siteFontFamily;

    /**
     * @var string
     *
     * @ORM\Column(name="anchorColor", type="string", length=50)
     */
    private $anchorColor;

    /**
     * @var string
     *
     * @ORM\Column(name="anchorHoverColor", type="string", length=50)
     */
    private $anchorHoverColor;


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
     * Set backgroundColor
     *
     * @param string $backgroundColor
     *
     * @return Templating
     */
    public function setBackgroundColor($backgroundColor)
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    /**
     * Get backgroundColor
     *
     * @return string
     */
    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * Set headerColor
     *
     * @param string $headerColor
     *
     * @return Templating
     */
    public function setHeaderColor($headerColor)
    {
        $this->headerColor = $headerColor;

        return $this;
    }

    /**
     * Get headerColor
     *
     * @return string
     */
    public function getHeaderColor()
    {
        return $this->headerColor;
    }

    /**
     * Set footerColor
     *
     * @param string $footerColor
     *
     * @return Templating
     */
    public function setFooterColor($footerColor)
    {
        $this->footerColor = $footerColor;

        return $this;
    }

    /**
     * Get footerColor
     *
     * @return string
     */
    public function getFooterColor()
    {
        return $this->footerColor;
    }

    /**
     * Set bodyColor
     *
     * @param string $bodyColor
     *
     * @return Templating
     */
    public function setBodyColor($bodyColor)
    {
        $this->bodyColor = $bodyColor;

        return $this;
    }

    /**
     * Get bodyColor
     *
     * @return string
     */
    public function getBodyColor()
    {
        return $this->bodyColor;
    }

    /**
     * Set menuColor
     *
     * @param string $menuColor
     *
     * @return Templating
     */
    public function setMenuColor($menuColor)
    {
        $this->menuColor = $menuColor;

        return $this;
    }

    /**
     * Get menuColor
     *
     * @return string
     */
    public function getMenuColor()
    {
        return $this->menuColor;
    }

    /**
     * Set menuLiColor
     *
     * @param string $menuLiColor
     *
     * @return Templating
     */
    public function setMenuLiColor($menuLiColor)
    {
        $this->menuLiColor = $menuLiColor;

        return $this;
    }

    /**
     * Get menuLiColor
     *
     * @return string
     */
    public function getMenuLiColor()
    {
        return $this->menuLiColor;
    }

    /**
     * Set menuLiHoverColor
     *
     * @param string $menuLiHoverColor
     *
     * @return Templating
     */
    public function setMenuLiHoverColor($menuLiHoverColor)
    {
        $this->menuLiHoverColor = $menuLiHoverColor;

        return $this;
    }

    /**
     * Get menuLiHoverColor
     *
     * @return string
     */
    public function getMenuLiHoverColor()
    {
        return $this->menuLiHoverColor;
    }

    /**
     * Set siteFontSize
     *
     * @param integer $siteFontSize
     *
     * @return Templating
     */
    public function setSiteFontSize($siteFontSize)
    {
        $this->siteFontSize = $siteFontSize;

        return $this;
    }

    /**
     * Get siteFontSize
     *
     * @return integer
     */
    public function getSiteFontSize()
    {
        return $this->siteFontSize;
    }

    /**
     * Set siteFontFamily
     *
     * @param string $siteFontFamily
     *
     * @return Templating
     */
    public function setSiteFontFamily($siteFontFamily)
    {
        $this->siteFontFamily = $siteFontFamily;

        return $this;
    }

    /**
     * Get siteFontFamily
     *
     * @return string
     */
    public function getSiteFontFamily()
    {
        return $this->siteFontFamily;
    }

    /**
     * Set anchorColor
     *
     * @param string $anchorColor
     *
     * @return Templating
     */
    public function setAnchorColor($anchorColor)
    {
        $this->anchorColor = $anchorColor;

        return $this;
    }

    /**
     * Get anchorColor
     *
     * @return string
     */
    public function getAnchorColor()
    {
        return $this->anchorColor;
    }

    /**
     * Set anchorHoverColor
     *
     * @param string $anchorHoverColor
     *
     * @return Templating
     */
    public function setAnchorHoverColor($anchorHoverColor)
    {
        $this->anchorHoverColor = $anchorHoverColor;

        return $this;
    }

    /**
     * Get anchorHoverColor
     *
     * @return string
     */
    public function getAnchorHoverColor()
    {
        return $this->anchorHoverColor;
    }
}

