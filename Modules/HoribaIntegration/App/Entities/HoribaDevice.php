<?php

namespace Modules\HoribaIntegration\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * HoribaDevice
 *
 * @ORM\Table("horiba_devices")
 * @ORM\Entity()
 */
class HoribaDevice
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
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="manufacturer", type="string", length=100, nullable=true)
     */
    private $manufacturer;

    /**
     * @var string
     *
     * @ORM\Column(name="model", type="string", length=100, nullable=true)
     */
    private $model;

    /**
     * @var string
     *
     * @ORM\Column(name="serial_number", type="string", length=100, nullable=true)
     */
    private $serialNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="connection_type", type="string", length=20, options={"default"="bridge"})
     */
    private $connectionType = 'bridge';

    /**
     * @var string
     *
     * @ORM\Column(name="bridge_ip", type="string", length=45, nullable=true)
     */
    private $bridgeIp;

    /**
     * @var string
     *
     * @ORM\Column(name="protocol", type="string", length=30, options={"default"="micros60"})
     */
    private $protocol = 'micros60';

    /**
     * @var string
     *
     * @ORM\Column(name="api_token", type="string", length=128, nullable=true)
     */
    private $apiToken;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean", options={"default"=true})
     */
    private $isActive = true;

    /**
     * @var integer
     *
     * @ORM\Column(name="created_by", type="integer", nullable=true)
     */
    private $createdBy;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;
}
