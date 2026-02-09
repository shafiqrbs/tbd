<?php

namespace Modules\Inventory\App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="inv_pos_sales_failures")
 * @ORM\Entity()
 */
class PosSalesFailure
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="sync_batch_id", type="string", nullable=true)
     */
    private $syncBatchId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="device_id", type="string", nullable=true)
     */
    private $deviceId;

    /**
     * @var array
     *
     * @ORM\Column(name="sale_data", type="json")
     */
    private $saleData = [];

    /**
     * @var string
     *
     * @ORM\Column(name="error_message", type="text")
     */
    private $errorMessage;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }
}
