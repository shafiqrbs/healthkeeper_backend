<?php

namespace Modules\Medicine\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class MedicineGenericModel extends Model
{
    use HasFactory;

    protected $table = 'medicine_generic';
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

    public static function insertNewGeneric($medicine,$generic)
    {

        self::updateOrCreate(
            [
                'name' => trim($generic)
            ]
        );
        MedicineBrandModel::updateOrCreate(
            [
                'medicineGeneric_id' => $generic->id,
                'name' => trim($medicine)
            ]
        );



    }


}
