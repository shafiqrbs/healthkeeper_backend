<?php

namespace Modules\Medicine\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Inventory\App\Models\StockItemInventoryHistoryModel;
use Modules\Medicine\App\Entities\DispenseItem;

class DispenseModel extends Model
{
    protected $table = 'hms_dispense';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'config_id',
        'warehouse_id',
        'created_by_id',
        'approved_by_id',
        'uid',
        'code',
        'approved_date',
        'invoice',
        'dispense_type',
        'dispense_no',
        'discount_type',
        'remark',
        'process',
        'status',
    ];


    public static function generateUniqueCode($length = 12)
    {
        do {
            // Generate a random 12-digit number
            $code = str_pad(random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (self::where('uid', $code)->exists());
        return $code;
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $codes = self::salesEventListener($model);
            $model->invoice = $codes['generateId'];
            $model->code = $codes['code'];
            if (empty($model->uid)) {
                $model->uid = self::generateUniqueCode(12);
            }
        });
    }

    public static function salesEventListener($model): array
    {
        $patternCodeService = app(GeneratePatternCodeService::class);

        return $patternCodeService->invoiceNo([
            'config' => $model->config_id,
            'table'  => 'hms_dispense',
            'prefix' => 'DIS-',
        ]);
    }

    public function dispenseItems(): HasMany
    {
        return $this->hasMany(DispenseItemModel::class, 'dispense_id');
    }

    public static function getRecords($request, $domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        $entities = self::where([['hms_dispense.config_id', $domain['hms_config']]])
            ->leftjoin('users as cb', 'cb.id', '=', 'hms_dispense.created_by_id')
            ->leftjoin('cor_warehouses', 'cor_warehouses.id', '=', 'hms_dispense.warehouse_id')
            ->select([
                'hms_dispense.id',
                'cor_warehouses.name as warehouse_name',
                'hms_dispense.uid',
                DB::raw('DATE_FORMAT(hms_dispense.created_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(hms_dispense.approved_date, "%d-%m-%Y") as approved_date'),
                'hms_dispense.invoice as invoice',
                'hms_dispense.approved_by_id',
                'hms_dispense.warehouse_id',
                'hms_dispense.dispense_type',
                'hms_dispense.dispense_no',
                'hms_dispense.remark',
                'cb.username as cbUser',
                'cb.name as cbName',
                'cb.id as cbId',
                'hms_dispense.process as process',
            ])
            ->with(['dispenseItems' => function ($query) {
                $query->select([
                    'hms_dispense_item.id',
                    'hms_dispense_item.dispense_id',
                    'hms_dispense_item.name',
                    'hms_dispense_item.quantity',
                ]);
            }]);

        if (isset($request['term']) && !empty($request['term'])) {
            $entities = $entities->whereAny(['hms_dispense.invoice','hms_dispense.dispense_type','hms_dispense.dispense_no'], 'LIKE', '%' . $request['term'] . '%');
        }
        if (isset($request['warehouse_id']) && !empty($request['warehouse_id'])) {
            $entities = $entities->where('hms_dispense.warehouse_id', $request['warehouse_id']);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && empty($request['end_date'])) {
            $start_date = $request['start_date'] . ' 00:00:00';
            $end_date = $request['start_date'] . ' 23:59:59';
            $entities = $entities->whereBetween('hms_dispense.created_at', [$start_date, $end_date]);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && isset($request['end_date']) && !empty($request['end_date'])) {
            $start_date = $request['start_date'] . ' 00:00:00';
            $end_date = $request['end_date'] . ' 23:59:59';
            $entities = $entities->whereBetween('hms_dispense.created_at', [$start_date, $end_date]);
        }

        $total = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('hms_dispense.id', 'DESC')
            ->get();
        return array('count' => $total, 'entities' => $entities);
    }
    public static function show($id)
    {
        $data = self::where('hms_dispense.id', $id)
            ->leftjoin('users as cb', 'cb.id', '=', 'hms_dispense.created_by_id')
            ->leftjoin('users as ap', 'ap.id', '=', 'hms_dispense.approved_by_id')
            ->leftjoin('cor_warehouses', 'cor_warehouses.id', '=', 'hms_dispense.warehouse_id')
            ->select([
                'hms_dispense.id',
                'cor_warehouses.name as warehouse_name',
                'hms_dispense.uid',
                DB::raw('DATE_FORMAT(hms_dispense.created_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(hms_dispense.approved_date, "%d-%m-%Y") as approved_date'),
                'hms_dispense.invoice as invoice',
                'hms_dispense.approved_by_id',
                'hms_dispense.warehouse_id',
                'hms_dispense.dispense_type',
                'hms_dispense.dispense_no',
                'hms_dispense.remark',
                'cb.username as cbUser',
                'cb.name as cbName',
                'ap.username as approve_username',
                'ap.name as approve_name',
                'cb.id as cbId',
                'hms_dispense.process as process',
            ])
            ->with(['dispenseItems' => function ($query) {
                $query->select([
                    'hms_dispense_item.id',
                    'hms_dispense_item.config_id',
                    'hms_dispense_item.warehouse_id',
                    'hms_dispense_item.unit_id',
                    'hms_dispense_item.stock_item_id',
                    'hms_dispense_item.dispense_id',
                    'hms_dispense_item.name',
                    'hms_dispense_item.quantity',
                ]);
            }])->first();

        return $data;
    }


}
