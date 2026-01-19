<?php

namespace Modules\Hospital\App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * InvoiceParticularTestReport
 *
 * @ORM\Table(name = "hms_invoice_particular_test_report")
 * @ORM\Entity()
 */
class InvoiceParticularTestReport
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
     * @ORM\OneToOne(targetEntity="InvoiceParticular")
     * @ORM\JoinColumn(onDelete="CASCADE")
     **/
    private $invoiceParticular;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string",  nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="test_id", type="string", length=20, nullable=true)
     */
    private $testId;


    /**
     * @var string
     *
     * @ORM\Column(name="sample_id", type="string",length=20, nullable=true)
     */
    private $sampleId;

     /**
     * @var string
     *
     * @ORM\Column(name="sample_type", type="string", nullable=true)
     */
    private $sampleType;

    /**
     * @var string
     *
     * @ORM\Column(type="string",  nullable=true)
     */
    private $geneXpertValue;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $technique;


     /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
     private $findings;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $parenchyma;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $mediastinumVessels;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $mediastinumTrachea;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $mediastinumOesophagus;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $mediastinumThymus;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $mediastinumLymphNodes;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $heart;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $pleura;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $bones;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
     private $afterIvContrast;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
     private $impression;


    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
     private $impression_two;


    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
     private $trachea;


    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
     private $diaphragm;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
     private $lungs;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
     private $bonyThorax;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $rifResistanceNotDetected;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
     private $rifResistanceDetected;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $rifResistanceIndeterminate;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
     private $MtbNotDetected;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
     private $invalid;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
     private $sarsCov;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
     private $dengueNs;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $TbHospital;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $TbRegistrationNumber;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $DrTbRegistrationNumber;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $ETbRegistrationNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $AfbDiagnosis;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $AfbContaminated;

     /**
     * @var string
     *
     * @ORM\Column(type="string", name="atypical_mycobacteria_species" , nullable=true)
     */
    private $AfbMycobacteriaSpecies;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $diagnosis;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $colonies_1;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $colonies_2;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $colonies_3;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $colonies_4;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $followUpMonth;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $otherTestMicroscopy;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $otherGeneXpert;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $otherCulture;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $otherDst;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $firstLineInh;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $firstLineRif;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $secondLineSr;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $secondLineResistanceLevel;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $secondRecommendation;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $secondLineKmAmCm;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $secondLineKmCm;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $secondLineLowHeightKmCm;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $nonInterpretable;



     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $flqLowResistance;



     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $flqHighResistance;



     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $flqLowToHigh;



     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $islResistantKmAmCm;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $islResistantKmCm;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $islResistantLowKmToHighCm;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $specimenIdentificationNumber;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $referralCenter;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $typePatient;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $lastCovidTestCenter;

     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $specimen;

     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $preservative;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $testType;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $geneXpertHospital;


     /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $referenceLaboratorySpecimenId;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $sarsCovPositive;



    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $presumptivePos;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $sarsCovnegative;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $covInvalid;

    /**
     * @var boolean
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $afbNotFound;


     /**
     * @var boolean
     *
      * @ORM\Column(type="string", nullable=true)
     */
    private $afbFound;


     /**
     * @var boolean
     *
      * @ORM\Column(type="string", nullable=true)
     */
    private $afbScanty;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $afbScantyOne;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $afbScantyTwo;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $afbScantyThree;

    /**
     * @var boolean
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $afbSampleFound;


    /**
     * @var boolean
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $afbSampleNotFound;

    /**
     * @var boolean
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $afbSampleScanty;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $afbSampleScantyOne;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $afbSampleScantyTwo;


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean",options={"default"="false"})
     */
    private $afbSampleScantyThree;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstMethod;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstMtb;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstInh;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstRif;



    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstFlq;



    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstLfx;



    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstMfx;



    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstEth;



    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstBdq;



    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstDlm;



    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstPa;



    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstLzd;



    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstCfz;



    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstAmk;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstKan;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $dstCap;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=150)
     */
    private $dstOther;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=150)
     */
    private $mtBcc;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=250)
     */
    private $mtResult;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $mtExaminationDate;


    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $mtReadingDate;


    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateSpecimenCollection;

     /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateSpecimenReceived;

     /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
     private $lastCovidTestDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $testDate;


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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

}

