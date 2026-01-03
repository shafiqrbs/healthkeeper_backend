<?php

namespace Modules\Hospital\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class AdmissionPatientHistoryModel extends Model
{
    use HasFactory;

    protected $table = 'hms_admission_patient_history';
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

    public static function  insertAdmissionHistory($domain, $entity,$data)
    {
        if($data){
            $json = [
                'old_room_id' => $entity->room_id,
                'new_room_id'=> $data['room_id'],
                'comment'=> $data['comment'],
                'created_by_id'=> $domain['user_id'],
            ];


            $date = new \DateTime('now');
            return self::query()->insert([
                'hms_invoice_id'  => $entity->id,
                'mode'  => 'transfer',
                'content'    => json_encode($json) ?? null,
                'created_by_id'    => $domain['user_id'],
                'updated_at'    => $date,
                'created_at'    => $date,
            ]);
        }

    }



}
