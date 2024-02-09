<?php

/*
 * This file is part of the Docudex project.
 *
 * (c) Devnet Limited <http://www.devnetlimited.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Modules\Core\App\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="cor_user_group")
 */
class Group
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $description;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="groups")
     **/
    protected $users;


    /**
     * Set description
     *
     * @param mixed $description
     * @return Group
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }
}
