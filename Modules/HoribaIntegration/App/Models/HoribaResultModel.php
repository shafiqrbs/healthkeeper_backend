<?php

namespace Modules\HoribaIntegration\App\Models;

use Illuminate\Database\Eloquent\Model;

class HoribaResultModel extends Model
{
    protected $table = 'horiba_results';

    public $timestamps = true;

    protected $guarded = ['id'];

    protected $fillable = [
        'device_id',
        'lis_record_id',
        'sample_id',
        'lab_id',
        'patient_id',
        'patient_name',
        'patient_gender',
        'patient_age_years',
        'patient_age_months',
        'wbc',
        'gra_pct',
        'lym_pct',
        'mid_pct',
        'mon_pct',
        'eos_pct',
        'bas_pct',
        'gra_count',
        'lym_count',
        'mid_count',
        'esr',
        'cir_eos',
        'rbc',
        'hgb',
        'hct',
        'mcv',
        'mch',
        'mchc',
        'rdw_sd',
        'rdw',
        'plt',
        'mpv',
        'pct_val',
        'pdw',
        'plcr',
        'bt',
        'ct',
        'wbc_histogram',
        'rbc_histogram',
        'plt_histogram',
        'alarms',
        'test_datetime',
        'received_datetime',
        'invoice_particular_id',
        'is_mapped',
        'is_approved',
        'ward_no',
        'bed_no',
        'raw_json',
        'created_by',
    ];

    protected $casts = [
        'is_mapped' => 'boolean',
        'is_approved' => 'boolean',
        'test_datetime' => 'datetime',
        'received_datetime' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(HoribaDeviceModel::class, 'device_id');
    }

    public function scopeUnmapped($query)
    {
        return $query->where('is_mapped', false);
    }

    public function scopeUnapproved($query)
    {
        return $query->where('is_approved', false);
    }

    public static function getRecords($request)
    {
        $query = self::select([
            'id',
            'device_id',
            'lis_record_id',
            'sample_id',
            'lab_id',
            'patient_id',
            'patient_name',
            'patient_gender',
            'patient_age_years',
            'patient_age_months',
            'wbc',
            'rbc',
            'hgb',
            'hct',
            'plt',
            'esr',
            'test_datetime',
            'received_datetime',
            'is_mapped',
            'is_approved',
            'invoice_particular_id',
            'created_at',
        ]);

        if ($request->filled('sample_id')) {
            $query->where('sample_id', 'like', '%' . $request->sample_id . '%');
        }

        if ($request->filled('patient_name')) {
            $query->where('patient_name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', 'like', '%' . $request->patient_id . '%');
        }

        if ($request->filled('is_mapped')) {
            $query->where('is_mapped', $request->is_mapped);
        }

        if ($request->filled('is_approved')) {
            $query->where('is_approved', $request->is_approved);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('test_datetime', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('test_datetime', '<=', $request->date_to);
        }

        $count = $query->count();

        $page = $request->input('page', 1);
        $offset = $request->input('offset', 50);

        $entities = $query->orderBy('id', 'desc')
            ->skip(($page - 1) * $offset)
            ->take($offset)
            ->get();

        return [
            'count' => $count,
            'entities' => $entities,
        ];
    }

    public static function getStats()
    {
        $today = now()->toDateString();
        $lastResult = self::orderBy('id', 'desc')->first();

        return [
            'total_records' => self::count(),
            'today_records' => self::whereDate('created_at', $today)->count(),
            'unmapped_records' => self::where('is_mapped', false)->count(),
            'last_sync' => $lastResult ? $lastResult->created_at->toIso8601String() : null,
            'last_sample_id' => $lastResult ? $lastResult->sample_id : null,
        ];
    }
}
