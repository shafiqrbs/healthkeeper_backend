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
                'cause_death'    => $data['cause_death'],
                'about_death'    => $data['about_death'],
                'death_date_time'    => new \DateTime($data['death_date_time']),
            ]
        );
    }






}
