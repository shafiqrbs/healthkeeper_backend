<?php

namespace Modules\Medicine\App\Entities;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Dispense
 *
 * @ORM\Table(name ="hms_dispense")
 * @ORM\Entity()
 */
class Dispense
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $uid;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Hospital\App\Entities\Config" , cascade={"detach","merge"} )
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $config;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\Warehouse")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $warehouse;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private  $approvedBy;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $approvedDate;

    /**
     * @var string
     * @ORM\Column(name="invoice", type="string", length=255, nullable=true)
     */
    private $invoice;

    /**
     * @var string
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @var string
     * @ORM\Column(name="dispense_type", type="string")
     */
     private $dispenseType;

    /**
     * @var string
     * @ORM\Column( type="string", length=20, nullable=true)
     */
    private $dispenseNo;

    /**
     * @var string
     * @ORM\Column(name="remark", type="text", nullable=true)
     */
    private $remark;

    /**
     * @var string
     * @ORM\Column(name="process", type="string")
     */
    private $process = "Created";

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

