<?php

namespace Modules\HoribaIntegration\App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * HoribaResult
 *
 * @ORM\Table(name="horiba_results",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="uq_device_lis_record", columns={"device_id", "lis_record_id"})
 *     },
 *     indexes={
 *         @ORM\Index(name="idx_sample_id", columns={"sample_id"}),
 *         @ORM\Index(name="idx_patient_id", columns={"patient_id"}),
 *         @ORM\Index(name="idx_is_mapped", columns={"is_mapped"}),
 *         @ORM\Index(name="idx_test_datetime", columns={"test_datetime"})
 *     }
 * )
 * @ORM\Entity()
 */
class HoribaResult
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
     * @var integer
     *
     * @ORM\Column(name="device_id", type="integer")
     */
    private $deviceId;

    /**
     * @var integer
     *
     * @ORM\Column(name="lis_record_id", type="integer")
     */
    private $lisRecordId;

    /**
     * @var string
     *
     * @ORM\Column(name="sample_id", type="string", length=50)
     */
    private $sampleId;

    /**
     * @var string
     *
     * @ORM\Column(name="lab_id", type="string", length=50, nullable=true)
     */
    private $labId;

    // --- Patient Info ---

    /**
     * @var string
     *
     * @ORM\Column(name="patient_id", type="string", length=50, nullable=true)
     */
    private $patientId;

    /**
     * @var string
     *
     * @ORM\Column(name="patient_name", type="string", length=150, nullable=true)
     */
    private $patientName;

    /**
     * @var string
     *
     * @ORM\Column(name="patient_gender", type="string", length=10, nullable=true)
     */
    private $patientGender;

    /**
     * @var integer
     *
     * @ORM\Column(name="patient_age_years", type="integer", nullable=true)
     */
    private $patientAgeYears;

    /**
     * @var integer
     *
     * @ORM\Column(name="patient_age_months", type="integer", nullable=true)
     */
    private $patientAgeMonths;

    // --- WBC Parameters ---

    /**
     * @var float
     *
     * @ORM\Column(name="wbc", type="float", nullable=true)
     */
    private $wbc;

    /**
     * @var float
     *
     * @ORM\Column(name="gra_pct", type="float", nullable=true)
     */
    private $graPct;

    /**
     * @var float
     *
     * @ORM\Column(name="lym_pct", type="float", nullable=true)
     */
    private $lymPct;

    /**
     * @var float
     *
     * @ORM\Column(name="mid_pct", type="float", nullable=true)
     */
    private $midPct;

    /**
     * @var float
     *
     * @ORM\Column(name="mon_pct", type="float", nullable=true)
     */
    private $monPct;

    /**
     * @var float
     *
     * @ORM\Column(name="eos_pct", type="float", nullable=true)
     */
    private $eosPct;

    /**
     * @var float
     *
     * @ORM\Column(name="bas_pct", type="float", nullable=true)
     */
    private $basPct;

    /**
     * @var float
     *
     * @ORM\Column(name="gra_count", type="float", nullable=true)
     */
    private $graCount;

    /**
     * @var float
     *
     * @ORM\Column(name="lym_count", type="float", nullable=true)
     */
    private $lymCount;

    /**
     * @var float
     *
     * @ORM\Column(name="mid_count", type="float", nullable=true)
     */
    private $midCount;

    /**
     * @var float
     *
     * @ORM\Column(name="esr", type="float", nullable=true)
     */
    private $esr;

    /**
     * @var float
     *
     * @ORM\Column(name="cir_eos", type="float", nullable=true)
     */
    private $cirEos;

    // --- RBC Parameters ---

    /**
     * @var float
     *
     * @ORM\Column(name="rbc", type="float", nullable=true)
     */
    private $rbc;

    /**
     * @var float
     *
     * @ORM\Column(name="hgb", type="float", nullable=true)
     */
    private $hgb;

    /**
     * @var float
     *
     * @ORM\Column(name="hct", type="float", nullable=true)
     */
    private $hct;

    /**
     * @var float
     *
     * @ORM\Column(name="mcv", type="float", nullable=true)
     */
    private $mcv;

    /**
     * @var float
     *
     * @ORM\Column(name="mch", type="float", nullable=true)
     */
    private $mch;

    /**
     * @var float
     *
     * @ORM\Column(name="mchc", type="float", nullable=true)
     */
    private $mchc;

    /**
     * @var float
     *
     * @ORM\Column(name="rdw_sd", type="float", nullable=true)
     */
    private $rdwSd;

    /**
     * @var float
     *
     * @ORM\Column(name="rdw", type="float", nullable=true)
     */
    private $rdw;

    // --- Platelet Parameters ---

    /**
     * @var float
     *
     * @ORM\Column(name="plt", type="float", nullable=true)
     */
    private $plt;

    /**
     * @var float
     *
     * @ORM\Column(name="mpv", type="float", nullable=true)
     */
    private $mpv;

    /**
     * @var float
     *
     * @ORM\Column(name="pct_val", type="float", nullable=true)
     */
    private $pctVal;

    /**
     * @var float
     *
     * @ORM\Column(name="pdw", type="float", nullable=true)
     */
    private $pdw;

    /**
     * @var float
     *
     * @ORM\Column(name="plcr", type="float", nullable=true)
     */
    private $plcr;

    /**
     * @var float
     *
     * @ORM\Column(name="bt", type="float", nullable=true)
     */
    private $bt;

    /**
     * @var float
     *
     * @ORM\Column(name="ct", type="float", nullable=true)
     */
    private $ct;

    // --- Histograms & Alarms ---

    /**
     * @var string
     *
     * @ORM\Column(name="wbc_histogram", type="text", nullable=true)
     */
    private $wbcHistogram;

    /**
     * @var string
     *
     * @ORM\Column(name="rbc_histogram", type="text", nullable=true)
     */
    private $rbcHistogram;

    /**
     * @var string
     *
     * @ORM\Column(name="plt_histogram", type="text", nullable=true)
     */
    private $pltHistogram;

    /**
     * @var string
     *
     * @ORM\Column(name="alarms", type="text", nullable=true)
     */
    private $alarms;

    // --- Dates ---

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="test_datetime", type="datetime", nullable=true)
     */
    private $testDatetime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="received_datetime", type="datetime", nullable=true)
     */
    private $receivedDatetime;

    // --- Mapping ---

    /**
     * @var integer
     *
     * @ORM\Column(name="invoice_particular_id", type="integer", nullable=true)
     */
    private $invoiceParticularId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_mapped", type="boolean", options={"default"=false})
     */
    private $isMapped = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_approved", type="boolean", options={"default"=false})
     */
    private $isApproved = false;

    // --- Meta ---

    /**
     * @var string
     *
     * @ORM\Column(name="ward_no", type="string", length=20, nullable=true)
     */
    private $wardNo;

    /**
     * @var string
     *
     * @ORM\Column(name="bed_no", type="string", length=20, nullable=true)
     */
    private $bedNo;

    /**
     * @var string
     *
     * @ORM\Column(name="raw_json", type="text", nullable=true)
     */
    private $rawJson;

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
