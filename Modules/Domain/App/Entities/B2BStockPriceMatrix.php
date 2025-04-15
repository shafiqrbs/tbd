<?php

namespace Modules\Domain\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * RequisitionItem
 *
 * @ORM\Table(name ="inv_b2b_stock_price_matrix")
 * @ORM\Entity()
 */
class B2BStockPriceMatrix
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
     * @var SubDomain $subDomain
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\SubDomain")
     **/
    private $subDomain;

    /**
     * @var SubDomain $subDomain
     * @ORM\ManyToOne(targetEntity="Modules\Domain\App\Entities\B2BCategoryPriceMatrix")
     **/
    private $categoryPriceMatrix;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItem" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(name="domain_stock_item_id", onDelete="CASCADE")
     **/
    private  $domainStockItem;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItem" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(name="sub_domain_stock_item_id", onDelete="CASCADE")
     **/
    private  $subDomainStockItem;

    /**
     * @var float
     *
     * @ORM\Column(name="mrp",type="float" , nullable=true)
     */
    private $mrp;


    /**
     * @var float
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchasePrice;

    /**
     * @var float
     * @ORM\Column(type="float", nullable = true)
     */
    private $salesPrice;

	/**
	 * @var boolean
	 * @ORM\Column(name="status", type="boolean", nullable=true)
	 */
	private $status=false;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $updatedAt;


}

