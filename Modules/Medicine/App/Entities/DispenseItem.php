<?php

namespace Modules\Medicine\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * DispenseItem
 *
 * @ORM\Table(name ="hms_dispense_item")
 * @ORM\Entity()
 */
class DispenseItem
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Hospital\App\Entities\Config" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Dispense")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $dispense;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\StockItem")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $stockItem;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Particular")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private  $unit;

    /**
     * @var string
     * @ORM\Column(name="name", type="string",  nullable = true)
     */
    private $name;

    /**
     * @var float
     * @ORM\Column(name="quantity", type="float")
     */
    private $quantity;

	/**
	 * @var boolean
	 * @ORM\Column(name="status", type="boolean", nullable=true)
	 */
	private $status=true;


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


}

