<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoiceParticular
 *
 * @ORM\Table( name = "hms_admission_patient_history")
 * @ORM\Entity()
 */
class AdmissionPatientHistory
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
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $uid;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $mode;


    /**
     * @ORM\ManyToOne(targetEntity="Invoice")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $hmsInvoice;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $createdBy;


    /**
     * @var string
     *
     * @ORM\Column( type="json", nullable = true)
     */
    private $content;


     /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create_at")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update_at")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updated;


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

