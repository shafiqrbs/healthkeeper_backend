<?php

namespace Modules\Hospital\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class InvoiceParticularTestReportModel extends Model
{
    use HasFactory;

    protected $table = 'hms_invoice_particular_test_report';
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

    public static function reportKeywordSearch($reportId, $mode, $term)
    {
        $entities = self::where(function ($query) use ($reportId,$mode,$term) {
               // $query->where("hms_invoice_particular.particular_id",$reportId);
                $query->where("hms_invoice_particular_test_report.{$mode}", 'LIKE', trim($term) . '%');
            })
            ->join('hms_invoice_particular', 'hms_invoice_particular.id', '=', 'hms_invoice_particular_test_report.invoice_particular_id')
            ->select(["{$mode} as name"])
            ->orderBy("{$mode}", 'ASC')
            ->groupBy("{$mode}")
            ->take(100)
            ->get();
        return $entities;

    }

}
