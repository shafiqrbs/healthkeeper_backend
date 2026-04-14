<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoiceParticular
 *
 * @ORM\Table(name = "hms_invoice_particular")
 * @ORM\Entity()
 */
class InvoiceParticular
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
    private $labNo;


     /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $uniqueId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $horibaResultId;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Inventory\App\Entities\Category")
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Invoice")
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $hmsInvoice;

    /**
     * @ORM\ManyToOne(targetEntity="Prescription")
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $prescription;

    /**
     * @ORM\ManyToOne(targetEntity="InvoiceTransaction")
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $invoiceTransaction;


    /**
     * @ORM\ManyToOne(targetEntity="InvoiceTransactionRefund")
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $invoiceTransactionRefund;


    /**
     * @ORM\ManyToOne(targetEntity="PatientWaiver")
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $patientWaiver;


    /**
     * @ORM\ManyToOne(targetEntity="InvoicePathologicalGroup")
     * @ORM\JoinColumn(nullable=true,onDelete="SET NULL")
     **/
    private $invoicePathologicalGroup;

     /**
     * @ORM\ManyToOne(targetEntity="AdmissionPatient")
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $admissionPatientParticular;


    /**
     * @ORM\ManyToOne(targetEntity="Particular", inversedBy="invoiceParticular")
     * @ORM\JoinColumn(name="particular_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    private $particular;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $assignDoctor;


    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private $assignLabuser;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="report_delivered_by_id", referencedColumnName="id", nullable=true)
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private  $reportDeliveredBy;

     /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="sample_collected_by_id", referencedColumnName="id", nullable=true)
     * @ORM\JoinColumn(onDelete="SET NULL")
     **/
    private  $sampleCollectedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string",nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="smallint", nullable=true, options={"default"=0})
     */
    private $quantity = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer",  nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="barcode", type="string",  nullable=true)
     */
    private $barcode;


    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=true, options={"default"=0})
     */
    private $price;

     /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true, options={"default"=0})
     */
    private $discountPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="commission", type="float", nullable=true, options={"default"=0})
     */
    private $commission;

    /**
     * @var string
     *
     * @ORM\Column( type="decimal", nullable=true, options={"default"=0})
     */
    private $estimatePrice;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",nullable=true, options={"default"=0})
     */
    private $customPrice = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",nullable=true, options={"default"=0})
     */
    private $isFree;


    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true, options={"default"=0})
     */
    private $subTotal;

    /**
     * @var string
     *
     * @ORM\Column(type="process" , type="string", length=30,options={"default"="New"})
     */
    private $process;


    /**
     * @var string
     *
     * @ORM\Column(type="mode" , type="string", length=30, nullable=true)
     */
    private $mode;

    /**
     * @var string
     *
     * @ORM\Column(type="report_mode" , type="string", length=30, nullable=true)
     */
    private $reportMode;


    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="json_report", type="json", nullable=true)
     */
    private $json_report;

     /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

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
     * @var \DateTime
     * @ORM\Column(name="collection_date", type="datetime", nullable=true)
     */
    private $collectionDate;

     /**
     * @var \DateTime
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=true, options={"default"="false"})
     */
    private $status;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_invoice", type="boolean", nullable=true, options={"default"="false"})
     */
     private $isInvoice;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_refund", type="boolean", nullable=true, options={"default"="false"})
     */
    private $isRefund;

    /**
     * @var integer
     *
     * @ORM\Column(name="refund_quantity", type="integer", nullable=true)
     */
    private $refundQuantity;


     /**
     * @var float
     *
     * @ORM\Column(name="refund_amount", type="float", nullable=true)
     */
    private $refundAmount;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true, options={"default"="false"})
     */
     private $isWaiver;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true, options={"default"="false"})
     */
     private $isWaiverApprove;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true, options={"default"="false"})
     */
    private $isAdmission;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true, options={"default"="false"})
     */
    private $isAvailable;

     /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updated;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $path;


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

