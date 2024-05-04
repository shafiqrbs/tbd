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

use Doctrine\ORM\Mapping as ORM;

/**

 * @ORM\Table(name="cor_global_api")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class GlobalApi
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}
