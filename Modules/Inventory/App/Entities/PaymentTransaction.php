<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Modules\Accounting\App\Entities\TransactionMode;

/**
 * PaymentTransaction
 *
 * @ORM\Table(name="inv_payment_transaction")
 * @ORM\Entity()
 */
class PaymentTransaction
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
     * @ORM\ManyToOne(targetEntity="Config", cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Sales", cascade={"detach","merge"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $sales;

   /**
     * @ORM\ManyToOne(targetEntity="Purchase", cascade={"detach","merge"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $purchase;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Accounting\App\Entities\TransactionMode" ,cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     **/
    private $transactionMode;

    /**
     * @var float
     *
     * @ORM\Column( type="float", nullable=true)
     */
    private $amount;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean" )
     */
    private $isMain= false;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }



}

