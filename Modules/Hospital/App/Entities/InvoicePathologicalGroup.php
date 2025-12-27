<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoicePathologicalGroup
 *
 * @ORM\Table( name = "hms_invoice_pathological_group")
 * @ORM\Entity()
 */
class InvoicePathologicalGroup
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
     * @ORM\ManyToOne(targetEntity="InvoiceTransaction")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $invoiceTransaction;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="report_name", type="string", nullable=true)
     */
    private $reportName;

    /**
     * @ORM\ManyToOne(targetEntity="Invoice")
     **/
    private $hmsInvoice;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private $assignDoctor;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     **/
    private $assignLabuser;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="report_delivered_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $reportDeliveredBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="sample_collected_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $sampleCollectedBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Category")
     **/
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="particular_delivered_by_id", referencedColumnName="id", nullable=true)
     **/
    private  $particularDeliveredBy;

    /**
     * @var string
     *
     * @ORM\Column(name="sample_collected_name", type="string", nullable=true)
     */
    private $sampleCollectedName;

    /**
     * @var string
     *
     * @ORM\Column(name="report_delivered_name", type="string", nullable=true)
     */
    private $reportDeliveredName;

    /**
     * @var string
     *
     * @ORM\Column(name="assign_labuser_name", type="string", nullable=true)
     */
    private $assignLabuserName;

    /**
     * @var string
     *
     * @ORM\Column(name="assign_doctor_name", type="string", nullable=true)
     */
    private $assignDoctorName;


    /**
     * @var string
     *
     * @ORM\Column(type="process" , type="string", length=30,options={"default"="New"})
     */
    private $process;

    /**
     * @var string
     *
     * @ORM\Column(name="barcode", type="string",  nullable=true)
     */
    private $barcode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $labNo;

    /**
     * @var text
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

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

