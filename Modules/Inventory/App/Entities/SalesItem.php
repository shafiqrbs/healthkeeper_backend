<?php

namespace Modules\Inventory\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * SalesItem
 *
 * @ORM\Table( name = "inv_sales_item")
 * @ORM\Entity(repositoryClass="Modules\Inventory\App\Repositories\SalesItemRepository")
 */
class SalesItem
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Sales")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $sale;

    /**
     * @ORM\OneToOne(targetEntity="PurchaseItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $childPurchaseItem;

    /**
     * @ORM\ManyToOne(targetEntity="PurchaseItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $purchaseItem;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Config" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;


    /**
     * @ORM\ManyToOne(targetEntity="Discount")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private  $discount;



    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="rack_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private  $rack;



     /**
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $stockItem;

    /**
     * @ORM\ManyToOne(targetEntity="Particular")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private  $unit;

    /**
     * @ORM\ManyToOne(targetEntity="ProductUnitMeasurement")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private  $measurementUnit;

     /**
     * @ORM\ManyToOne(targetEntity="StockItemPriceMatrix")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private  $stockItemPriceMatrix;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=225, nullable=true)
     */
    private $measurementUnitString;

     /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $uom;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

     /**
     * @var float
     *
     * @ORM\Column(name="receivable_discount_percent", type="float",  nullable=true)
     */
    private $receivableDiscountPercent = 0;


     /**
     * @var float
     *
     * @ORM\Column(name="receivable_discount_amount", type="float",  nullable=true)
     */
    private $receivableDiscountAmount = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float",  nullable=true)
     */
    private $quantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $returnQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $damageQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $spoilQuantity= 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $bonusQuantity = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $salesPrice = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchasePrice = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $percent;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $discountPrice;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $tloPrice;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $srCommission;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $srCommissionTotal;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $tloTotal;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $tloMode;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $height = 0;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $width = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $subQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $totalQuantity = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="assuranceType", type="string", length=50, nullable = true)
     */
    private $assuranceType;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="production_date", type="datetime", nullable=true)
     */
    private $productionDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expired_date", type="datetime", nullable=true)
     */
    private $expiredDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="issueDate", type="datetime", nullable=true)
     */
    private $issueDate;

    /**
     * @var array
     *
     * @ORM\Column(name="internalSerial", type="simple_array",  nullable = true)
     */
    private $internalSerial;

    /**
     * @var string
     *
     * @ORM\Column(name="externalSerial", type="text",  nullable = true)
     */
    private $externalSerial;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", options={"default":0})
     */
    private $isDelete;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $subTotal = 0;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Warehouse")
     **/
    private  $warehouse;

}

