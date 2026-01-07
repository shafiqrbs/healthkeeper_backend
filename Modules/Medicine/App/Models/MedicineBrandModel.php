<?php

namespace Modules\Medicine\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class MedicineBrandModel extends Model
{
    use HasFactory;

    protected $table = 'medicine_brand';
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

    public function generic()
    {
        return $this->hasOne(MedicineGenericModel::class, 'id', 'medicine_generic_id');
    }

    public static function getMedicineGenericDropdown($term , $mode ='generic')
    {

        $entities = DB::table('medicine_brand')
            ->leftJoin(
                'medicine_generic',
                'medicine_generic.id',
                '=',
                'medicine_brand.medicineGeneric_id'
            )
            ->select([
                DB::raw("
            CONCAT(
                IF(medicine_brand.medicineForm <> '',
                    CONCAT(TRIM(medicine_brand.medicineForm),' - '),
                    ''
                ),
                TRIM(medicine_brand.name),
                IF(medicine_brand.strength <> '',
                    CONCAT(' - ', TRIM(medicine_brand.strength)),
                    ''
                ),
                IF(medicine_generic.name <> '',
                    CONCAT(' (', TRIM(medicine_generic.name), ')'),
                    ''
                )
            ) AS name
        "),
                'medicine_brand.id AS generic_id',
                'medicine_generic.name AS generic',
            ]);

        if ($mode === 'brand') {
            $entities->where('medicine_brand.name', 'LIKE', "%{$term}%");
        } else {
            $entities->where('medicine_generic.name', 'LIKE', "%{$term}%");
        }

        $results = $entities
            ->groupBy(
                'medicine_brand.id',
                'medicine_brand.name',
                'medicine_brand.medicineForm',
                'medicine_brand.strength',
                'medicine_generic.name'
            )
            ->orderBy('medicine_brand.name', 'ASC')
            ->limit(100)
            ->get();

        return $results;

    }

}
