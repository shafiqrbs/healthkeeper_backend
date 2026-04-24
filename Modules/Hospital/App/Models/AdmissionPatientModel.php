<?php

namespace Modules\Hospital\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class AdmissionPatientModel extends Model
{
    use HasFactory;

    protected $table = 'hms_admission_patient_details';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function insertDeathCertificate($domain,$id,$data)
    {
        self::updateOrCreate(
            [
                'hms_invoice_id' => $id,
            ],
            [
                'created_by_id'=> $domain['user_id'],
                'approved_by_id'=> $domain['user_id'],
                'reason'    => (isset($data['reason']) && $data['reason']) ? $data['reason']:null,
                'referred_hospital'    => (isset($data['referred_hospital']) && $data['referred_hospital']) ? $data['referred_hospital']:null,
                'diseases_profile'    => (isset($data['diseases_profile']) && $data['diseases_profile']) ? $data['diseases_profile']:null,
                'cause_death'    => (isset($data['cause_death']) && $data['cause_death']) ? $data['cause_death']:null,
                'about_death'    => (isset($data['about_death']) && $data['about_death']) ? $data['about_death']:null,
                'dead_date_time'    => (isset($data['dead_date_time']) && $data['dead_date_time']) ? $data['dead_date_time']:null,
                'death_date_time'    => (isset($data['death_date_time']) && $data['death_date_time'] && $data['death_date_time'] !== 'invalid') ? new \DateTime($data['death_date_time']) : new \DateTime(),
            ]
        );
    }

    public static function getDiseaseProfile($domain)
    {
        $entities = self::where('hms_invoice.config_id', $domain['hms_config'])
            ->whereNotNull('hms_admission_patient_details.diseases_profile')
            ->join('hms_invoice','hms_invoice.id','=','hms_admission_patient_details.hms_invoice_id')
            ->select([
                'hms_admission_patient_details.diseases_profile',
            ])
            ->groupBy('hms_admission_patient_details.diseases_profile')
            ->orderBY('hms_admission_patient_details.diseases_profile','ASC')
            ->get();
        return $entities;
    }

    public static function getReferredHospital($domain)
    {
        $entities = self::where('hms_invoice.config_id', $domain['hms_config'])
            ->whereNotNull('hms_admission_patient_details.referred_hospital')
            ->join('hms_invoice','hms_invoice.id','=','hms_admission_patient_details.hms_invoice_id')
            ->select([
                'hms_admission_patient_details.referred_hospital as name',
            ])
            ->groupBy('hms_admission_patient_details.referred_hospital')
            ->orderBY('hms_admission_patient_details.referred_hospital','ASC')
            ->get();
        return $entities;
    }









}
