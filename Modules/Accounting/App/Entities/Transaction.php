<?php

namespace Modules\Accounting\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Transaction
 *
 * @ORM\Table(name="acc_transaction")
 * @ORM\Entity(repositoryClass="Modules\Accounting\App\Repositories\TransactionRepository")
 */
class Transaction
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;




}

