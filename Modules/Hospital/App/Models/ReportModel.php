<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\CustomerModel;
use Modules\Inventory\App\Models\SalesModel;


class ReportModel extends Model
{

    protected $table = 'hms_invoice';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];


    public static function findByIdOrUid($id)
    {
        return self::where('id', $id)
            ->orWhere('uid', $id)
            ->first();
    }

    public function customer()
    {
        return $this->hasOne(CustomerModel::class, 'id', 'customer_id');
    }

    public function invoice()
    {
        return $this->hasOne(OpdModel::class, 'id', 'sales_id');
    }

    public function invoice_particular()
    {
        return $this->hasMany(InvoiceParticularModel::class, 'hms_invoice_id');
    }

    public function invoice_transaction()
    {
        return $this->hasMany(InvoiceTransactionModel::class, 'hms_invoice_id');
    }

    public function room()
    {
        return $this->hasOne(ParticularModel::class, 'id', 'room_id');
    }
    public function patient_payment_mode()
    {
        return $this->hasOne(OpdModel::class, 'id', 'patient_payment_mode_id');
    }
    public function patient_mode()
    {
        return $this->hasOne(OpdModel::class, 'id', 'patient_mode_id');
    }
    public function sales()
    {
        return $this->belongsTo(SalesModel::class, 'sales_id');
    }

    public function children()
    {
        return $this->hasOne(InvoiceModel::class, 'parent_id');
    }


    public static function getSummary($domain,$request){


        $summary = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->select([
                DB::raw('COUNT(hms_invoice.id) as patient'),
                DB::raw('SUM(hms_invoice.total) as total'),
            ]);

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $summary = $summary->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (!empty($request['created'])) {
            $start = Carbon::parse($request['created'])->startOfDay();
            $end   = Carbon::parse($request['created'])->endOfDay();
            $summary->whereBetween('hms_invoice.created_at', [$start, $end]);
        }

        $summary = $summary->get();

        $userBase = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->join('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->select([
                DB::raw('hms_invoice.created_by_id as created_by_id'),
                DB::raw('createdBy.name as name'),
                DB::raw('COUNT(hms_invoice.id) as patient'),
                DB::raw('SUM(hms_invoice.total) as total'),
            ]);

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $userBase = $userBase->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $userBase = $userBase->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }
        $userBase->groupBy('hms_invoice.created_by_id');
        $userBase = $userBase->get();

        $roomBase = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->join('hms_particular as room','room.id','=','hms_invoice.room_id')
            ->select([
                DB::raw('room.name as name'),
                DB::raw('COUNT(hms_invoice.id) as patient'),
                DB::raw('SUM(hms_invoice.total) as total'),
            ]);

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $roomBase = $roomBase->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $roomBase = $roomBase->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }
        $roomBase->groupBy('room.name');
        $roomBase = $roomBase->get();

        $paymentMode = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->leftjoin('hms_particular_mode as particular_payment_mode','particular_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                DB::raw('particular_payment_mode.name as name'),
                DB::raw('COUNT(hms_invoice.id) as patient'),
                DB::raw('SUM(hms_invoice.total) as total'),
            ]);

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $paymentMode = $paymentMode->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $paymentMode = $paymentMode->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }
        $paymentMode->groupBy('particular_payment_mode.name');
        $paymentMode = $paymentMode->get();

        $patientMode = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->leftjoin('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->select([
                DB::raw('patient_mode.name as name'),
                DB::raw('COUNT(hms_invoice.id) as patient'),
                DB::raw('SUM(hms_invoice.total) as total'),
            ]);

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $patientMode = $patientMode->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $patientMode = $patientMode->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }
        $patientMode->groupBy('patient_mode.name');
        $patientMode = $patientMode->get();

        $doctorMode = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->join('hms_prescription as prescription','prescription.hms_invoice_id','=','hms_invoice.id')
            ->join('users as doctor','doctor.id','=','prescription.created_by_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->select([
                DB::raw('doctor.name as name'),
                DB::raw('COUNT(hms_invoice.id) as patient'),
                DB::raw('SUM(hms_invoice.total) as total'),
            ]);

        /*if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $doctorMode = $doctorMode->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }*/

        $doctorMode->groupBy('doctor.id');
        $doctorMode = $doctorMode->get();

        $services =self::serviceBaseInvestigation($domain,$request);
        $records =['summary'=>$summary,'userBase'=>$userBase,'roomBase'=>$roomBase,'paymentMode'=>$paymentMode,'patientMode'=>$patientMode,'doctorMode'=>$doctorMode,'services'=>$services];
        return $records;
    }

    public static function dailyCollectionServiceReport($domain,$request){

        $patientMode =self::dayPatientModeBaseCollection($domain,$request);
        $financialServices =self::dayFinancialServiceGroupInvestigation($domain,$request);
        $ipdRoomCollection =self::dayPatientRoomBaseCollection($domain,$request);
        $financialRefundRooms =self::dayRefundPatientRoomBaseCollection($domain,$request);
        $financialRefundInvestigations =self::dayRefundPatientInvestigationBaseCollection($domain,$request);
        $serviceFees = collect()
            ->merge($financialServices)
            ->merge($patientMode)
            ->merge($ipdRoomCollection)
            ->merge($financialRefundRooms)
            ->merge($financialRefundInvestigations)
            ->values();
        $serviceFees = $serviceFees->toArray();
        $filter = ['start_date'=>$request['start_date'],'end_date'=>$request['end_date']];
        $records =[
            'filter' => $filter,
            'serviceFees' => $serviceFees,
        ];
        return $records;
    }

    public static function dayPatientModeBaseCollection($domain,$request)
    {

        $entities = InvoiceParticularModel::where('hms_invoice.config_id', $domain['hms_config'])
            ->whereIn('hms_invoice_particular.mode', ['opd','emergency','ipd'])
            ->where('hms_invoice_particular.status', 1)
            ->join('hms_invoice as hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_particular.hms_invoice_id')
            ->join(
                'hms_invoice_transaction as hms_invoice_transaction',
                'hms_invoice_transaction.id',
                '=',
                'hms_invoice_particular.invoice_transaction_id'
            )
            ->select([
                DB::raw("DATE_FORMAT(hms_invoice_transaction.created_at, '%d-%m-%Y') as report_date"),
                DB::raw("
                    CONCAT(
                        UPPER(LEFT(hms_invoice_particular.report_mode, 1)),
                        LOWER(SUBSTRING(hms_invoice_particular.report_mode, 2)),
                        '-',
                        UPPER(LEFT(hms_invoice_particular.mode, 1)),
                        LOWER(SUBSTRING(hms_invoice_particular.mode, 2))
                    ) as name
                "),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
            ])
            ->groupBy(
                DB::raw('DATE(hms_invoice_transaction.created_at)'),
                'hms_invoice_particular.mode',
                'hms_invoice_particular.report_mode'
            );

        if(isset($request['invoice_mode']) && !empty($request['invoice_mode'])){
            $entities = $entities->where('hms_invoice_particular.report_mode',$request['invoice_mode']);
        }

        if (!empty($request['created_by_id'])) {
            $entities->where('hms_invoice.created_by_id', $request['created_by_id']);
        }

        if (isset($request['start_date']) && isset($request['end_date'])) {
            $start_date = (new \DateTime($request['start_date']))->format('Y-m-d 00:00:00');
            $end_date   = (new \DateTime($request['end_date']))->format('Y-m-d 23:59:59');
        } else {
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date   = $date->format('Y-m-d 23:59:59');
        }
        $entities = $entities
            ->whereBetween('hms_invoice_transaction.created_at', [$start_date, $end_date])
            ->orderBy('report_date')
            ->get();
        return $entities;
    }

    public static function dayPatientRoomBaseCollection($domain,$request)
    {
        $entities = InvoiceParticularModel::where('hms_invoice.config_id', $domain['hms_config'])
            ->whereIn('hms_invoice_particular.mode', ['room'])
            ->where('hms_invoice_particular.status', 1)
            ->whereIn('hms_invoice_parent.invoice_mode', ['opd','emergency'])
            ->join('hms_invoice as hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice as hms_invoice_parent', 'hms_invoice_parent.id', '=', 'hms_invoice.parent_id')
            ->join(
                'hms_invoice_transaction as hms_invoice_transaction',
                'hms_invoice_transaction.id',
                '=',
                'hms_invoice_particular.invoice_transaction_id'
            )

            ->select([
                DB::raw("DATE_FORMAT(hms_invoice_transaction.created_at, '%d-%m-%Y') as report_date"),
                DB::raw("
                    CONCAT(
                        UPPER(LEFT(hms_invoice_parent.invoice_mode, 1)),
                        LOWER(SUBSTRING(hms_invoice_parent.invoice_mode, 2)),
                        '-',
                        UPPER(LEFT(hms_invoice_particular.mode, 1)),
                        LOWER(SUBSTRING(hms_invoice_particular.mode, 2))
                    ) as name
                "),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
            ])

            ->groupBy(
                DB::raw('DATE(hms_invoice_transaction.created_at)'),
                'hms_invoice_particular.mode',
                'hms_invoice_parent.invoice_mode'
            );
        if (!empty($request['created_by_id'])) {
            $entities->where('hms_invoice.created_by_id', $request['created_by_id']);
        }

        if (isset($request['start_date']) && isset($request['end_date'])) {
            $start_date = (new \DateTime($request['start_date']))->format('Y-m-d 00:00:00');
            $end_date   = (new \DateTime($request['end_date']))->format('Y-m-d 23:59:59');
        } else {
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date   = $date->format('Y-m-d 23:59:59');
        }
        $entities = $entities
            ->whereBetween('hms_invoice_transaction.created_at', [$start_date, $end_date])
            ->orderBy('report_date')
            ->get();
        return $entities;
    }

    public static function dayRefundPatientRoomBaseCollection($domain,$request)
    {
        $entities = InvoiceParticularModel::where('hms_invoice.config_id', $domain['hms_config'])
            ->whereIn('hms_invoice_particular.mode', ['room'])
            ->where('hms_invoice_particular.status', 1)
            ->where('hms_invoice_transaction.process','Done')
            ->whereIn('hms_invoice_parent.invoice_mode', ['opd','emergency'])
            ->join('hms_invoice as hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice as hms_invoice_parent', 'hms_invoice_parent.id', '=', 'hms_invoice.parent_id')
            ->join(
                'hms_invoice_transaction_refund as hms_invoice_transaction',
                'hms_invoice_transaction.id',
                '=',
                'hms_invoice_particular.invoice_transaction_refund_id'
            )
            ->select([
                DB::raw("DATE_FORMAT(hms_invoice_transaction.created_at, '%d-%m-%Y') as report_date"),
                DB::raw("
                    CONCAT(
                        UPPER(LEFT('rfd', 1)), LOWER(SUBSTRING('rfd', 2)),
                        '-',
                        UPPER(LEFT(hms_invoice_particular.mode, 1)),
                        LOWER(SUBSTRING(hms_invoice_particular.mode, 2))
                    ) as name
                "),
                DB::raw('SUM(hms_invoice_particular.refund_amount) * -1 as total'),
            ])

            ->groupBy(
                DB::raw('DATE(hms_invoice_transaction.created_at)'),
                'hms_invoice_particular.mode',
                'hms_invoice_parent.invoice_mode'
            );
        if (!empty($request['created_by_id'])) {
            $entities->where('hms_invoice.created_by_id', $request['created_by_id']);
        }

        if (isset($request['start_date']) && isset($request['end_date'])) {
            $start_date = (new \DateTime($request['start_date']))->format('Y-m-d 00:00:00');
            $end_date   = (new \DateTime($request['end_date']))->format('Y-m-d 23:59:59');
        } else {
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date   = $date->format('Y-m-d 23:59:59');
        }
        $entities = $entities
            ->whereBetween('hms_invoice_transaction.updated_at', [$start_date, $end_date])
            ->orderBy('report_date')
            ->get();
        return $entities;
    }

    public static function dayRefundPatientInvestigationBaseCollection($domain,$request)
    {
        $entities = InvoiceParticularModel::where('hms_invoice.config_id', $domain['hms_config'])
            ->whereIn('hms_invoice_particular.mode', ['investigation'])
            ->where('hms_invoice_particular.status', 1)
            ->where('hms_invoice_transaction.process','Done')
            ->join('hms_invoice as hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_particular.hms_invoice_id')
            ->join(
                'hms_invoice_transaction_refund as hms_invoice_transaction',
                'hms_invoice_transaction.id',
                '=',
                'hms_invoice_particular.invoice_transaction_refund_id'
            )
            ->select([
                DB::raw("DATE_FORMAT(hms_invoice_transaction.created_at, '%d-%m-%Y') as report_date"),
                DB::raw("CONCAT(
                    'RFD-',
                    UPPER(LEFT(hms_invoice_particular.mode, 1)),
                    LOWER(SUBSTRING(hms_invoice_particular.mode, 2))
                ) as name
                "),
                DB::raw('SUM(hms_invoice_particular.refund_amount) * -1 as total'),
            ])
            ->groupBy(
                DB::raw('DATE(hms_invoice_transaction.created_at)'),
                'hms_invoice_particular.mode'
            );
        if (!empty($request['created_by_id'])) {
            $entities->where('hms_invoice.created_by_id', $request['created_by_id']);
        }

        if (isset($request['start_date']) && isset($request['end_date'])) {
            $start_date = (new \DateTime($request['start_date']))->format('Y-m-d 00:00:00');
            $end_date   = (new \DateTime($request['end_date']))->format('Y-m-d 23:59:59');
        } else {
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date   = $date->format('Y-m-d 23:59:59');
        }
        $entities = $entities
            ->whereBetween('hms_invoice_transaction.updated_at', [$start_date, $end_date])
            ->orderBy('report_date')
            ->get();
        return $entities;
    }

    public static function dayFinancialServiceGroupInvestigation($domain,$request)
    {
        $entities = InvoiceParticularModel::where('hms_invoice.config_id', $domain['hms_config'])
            ->whereIn('hms_invoice_particular.mode', ['investigation'])
            ->where('hms_invoice_particular.status', 1)
            ->join('hms_invoice as hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_particular.hms_invoice_id')
            ->join(
                'hms_invoice_transaction as hms_invoice_transaction',
                'hms_invoice_transaction.id',
                '=',
                'hms_invoice_particular.invoice_transaction_id'
            )
            ->leftJoin('hms_particular as hms_particular', 'hms_particular.id', '=', 'hms_invoice_particular.particular_id')
            ->join(
                'hms_particular_mode as particular_mode',
                'particular_mode.id',
                '=',
                'hms_particular.financial_service_id'
            )
            ->select([
                DB::raw("DATE_FORMAT(hms_invoice_transaction.created_at, '%d-%m-%Y') as report_date"),
                DB::raw('particular_mode.name as name'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
            ])
            ->groupBy(
                DB::raw('DATE(hms_invoice_transaction.created_at)'),
                'particular_mode.id',
                'particular_mode.name'
            );
        if (isset($request['start_date']) && isset($request['end_date'])) {
            $start_date = (new \DateTime($request['start_date']))->format('Y-m-d 00:00:00');
            $end_date   = (new \DateTime($request['end_date']))->format('Y-m-d 23:59:59');
        } else {
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date   = $date->format('Y-m-d 23:59:59');
        }
        $entities = $entities
            ->whereBetween('hms_invoice_transaction.created_at', [$start_date, $end_date])
            ->orderBy('report_date', 'ASC')
            ->orderBy('name', 'ASC')
            ->get();
        return $entities;
    }

    public static function dayRefundFinancialServiceGroupInvestigation($domain,$request)
    {

        $entities = InvoiceParticularModel::where('hms_invoice.config_id', $domain['hms_config'])
            ->whereIn('hms_invoice_particular.mode', ['opd','emergency','ipd'])
            ->where('hms_invoice_particular.status', 1)
            ->join('hms_invoice as hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_particular.hms_invoice_id')
            ->join(
                'hms_invoice_transaction_refund as hms_invoice_transaction',
                'hms_invoice_transaction.id',
                '=',
                'hms_invoice_particular.invoice_transaction_refund_id'
            )
            ->select([
                DB::raw("DATE_FORMAT(hms_invoice_transaction.created_at, '%d-%m-%Y') as report_date"),
                DB::raw('UPPER(hms_invoice_particular.mode) as name'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
            ])
            ->groupBy(
                DB::raw('DATE(hms_invoice_transaction.created_at)'),
                'hms_invoice_particular.mode'
            );
        if (!empty($request['created_by_id'])) {
            $entities->where('hms_invoice.created_by_id', $request['created_by_id']);
        }

        if (isset($request['start_date']) && isset($request['end_date'])) {
            $start_date = (new \DateTime($request['start_date']))->format('Y-m-d 00:00:00');
            $end_date   = (new \DateTime($request['end_date']))->format('Y-m-d 23:59:59');
        } else {
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date   = $date->format('Y-m-d 23:59:59');
        }
        $entities = $entities
            ->whereBetween('hms_invoice_transaction.created_at', [$start_date, $end_date])
            ->orderBy('report_date')
            ->get();
        return $entities;
    }

    public static function getUserDailyInvoiceSummary($domain,$request){

        $summary =self::summaryCollection($domain,$request);
        $userBase =self::userBaseCollection($domain,$request);
        $userRefund =self::refundUserCollection($domain,$request);

        $userMaps = [];
        $userNames = [];
        foreach ($userBase as $user){
            $modeName = $user['name'];          // string
            $userNames[] = $user['name'];
            $userMaps[$modeName] = $user; // correct
        }



        $userRefundMaps = [];
        $refundUserNames = [];
        foreach ($userRefund as $service) {
            $mode = $service['name'];
            $refundUserNames[] = $service['name'];
            $userRefundMaps[$mode] = $service;
        }
        $merged = array_values(array_unique(array_merge($userNames, $refundUserNames)));

        $invoiceMerged = [];
        foreach ($merged as $mode) {
            $mode_id = $mode;
            $total  = $userMaps[$mode_id]['total'] ?? 0;
            $group_name  = $userMaps[$mode_id]['group_name'] ?? $userRefundMaps[$mode_id]['group_name'];
            $refund_total  = $userRefundMaps[$mode_id]['total'] ?? 0;
            $sub_total = ($total - $refund_total);
            $invoiceMerged[] = [
                'name' => $mode_id,
                'group_name' => $group_name,
                'total' => $total,
                'refund' => $refund_total, // default 0 if not refunded
                'sub_total' => $sub_total, // default 0 if not refunded
            ];
        }
        $groups = [];
        foreach ($invoiceMerged as $user){
            $groups[$user['group_name']][] = $user;
        }
        $filter = ['start_date'=>$request['start_date'],'end_date'=>$request['end_date']];
        $records =[
            'filter' => $filter,
            'summary' => $summary,
            'userBase' => $groups,
        ];
        return $records;
    }

    public static function getDashboardOverview($domain,$request){

        $summary =self::summaryCollection($domain,$request);
        $patientOverview =self::patientOverview($domain,$request);
        $waiver =self::patientWaiverOverview($domain,$request);

        $patientMonthlyOpd = self::monthlyPatientMode($domain,'opd','','created_at',$request);
        $patientMonthlyEmergency = self::monthlyPatientMode($domain,'emergency','','created_at',$request);
        $patientMonthlyIpd = self::monthlyPatientMode($domain,'ipd','','admission_date',$request);
        $patientMonthlyDischarged = self::monthlyPatientMode($domain,'ipd','discharged','release_date',$request);

        $monthlyOverview = [
            'monthlyOpd' => $patientMonthlyOpd,
            'monthlyEmergency' => $patientMonthlyEmergency,
            'monthlyIpd' => $patientMonthlyIpd,
            'monthlyDischarged' => $patientMonthlyDischarged,
        ];

        $patientMode =self::patientModeBaseCollection($domain,$request);
        $patientServiceMode =self::patientServiceModeBaseCollection($domain,$request);
        $userBase =self::userBaseCollection($domain,$request);

        $serviceGroups =self::serviceBaseGroupInvestigation($domain,$request);

        $financialServices =self::financialServiceGroupInvestigation($domain,$request);
        $refundServiceGroups =self::refundFinancialServiceGroupInvestigation($domain,$request);

        $particularInvoiceModes = InvoiceParticularModel::getParticularInvoiceModes();

        $services =self::serviceBaseInvestigation($domain,$request);
        $refundInvestigations =self::refundServiceBaseInvestigation($domain,$request);


        $investigationMaps = [];
        foreach ($services as $service) {
            $modeName = $service['name'];          // string
            $investigationMaps[$modeName] = $service['total']; // correct
        }

        $refundInvestigationMaps = [];
        foreach ($refundInvestigations as $service) {
            $mode = $service['name'];
            $refundInvestigationMaps[$mode] = $service['total'];
        }

        $investigationMerged = [];
        foreach ($services as $mode) {
            $mode_id = $mode->name;
            $total  = $investigationMaps[$mode_id] ?? 0;
            $refund_total  = $refundInvestigationMaps[$mode_id] ?? 0;
            $sub_total = ($total - $refund_total);
            $investigationMerged[] = [
                'name' => $mode_id,
                'total' => $total,
                'refund' => $refund_total, // default 0 if not refunded
                'sub_total' => $sub_total, // default 0 if not refunded
            ];
        }

        $refundPatientRoomBaseCollection =self::refundPatientRoomBaseCollection($domain,$request);
        $refundTotalAmount =self::refundTotalAmount($domain,$request);

        $invoiceModes =self::invoiceModeCollection($domain,$request);
        $refundInvoiceModes =self::refundInvoiceModeCollection($domain,$request);

        $invoiceModeMap = [];
        foreach ($invoiceModes as $invoiceMode) {
            $modeName = $invoiceMode['name'];          // string
            $invoiceModeMap[$modeName] = $invoiceMode['total']; // correct
        }

        $refundInvoiceModeMap = [];
        foreach ($refundInvoiceModes as $service) {
            $mode = $service['name'];
            $refundInvoiceModeMap[$mode] = $service['total'];
        }

        $invoiceMerged = [];
        foreach ($particularInvoiceModes as $mode) {
            $mode_id = $mode->name;
            $total  = $invoiceModeMap[$mode_id] ?? 0;
            $refund_total  = $refundInvoiceModeMap[$mode_id] ?? 0;
            $sub_total = ($total - $refund_total);
            $invoiceMerged[] = [
                'name' => $mode_id,
                'total' => $total,
                'refund' => $refund_total, // default 0 if not refunded
                'sub_total' => $sub_total, // default 0 if not refunded
            ];
        }


        $financialServicesModes = ParticularModeModel::getParticularModuleDropdown('financial-service');
        $refundMap = [];
        foreach ($refundServiceGroups as $refund) {
            $mode_id = $refund['mode_id'];
            $refundMap[$mode_id] = $refund['refund_amount'];
        }

        $serviceMap = [];
        foreach ($financialServices as $service) {
            $mode_id = $service['mode_id'];
            $serviceMap[$mode_id] = $service['total'];
        }
        $financialServicesMerged = [];
        foreach ($financialServicesModes as $mode) {
            $mode_id = $mode->id;
            $total  = $serviceMap[$mode_id] ?? 0;
            $refund_total  = $refundMap[$mode_id] ?? 0;
            $sub_total = ($total - $refund_total);
            $financialServicesMerged[] = [
                'id' => $mode_id,
                'name' => $mode->name,
                'name_bn' => $mode->name_bn,
                'total' => $total,
                'refund' => $refund_total, // default 0 if not refunded
                'sub_total' => $sub_total,
            ];
        }

        $filter = ['start_date'=>$request['start_date'],'end_date'=>$request['end_date']];
        $records =[
            'filter' => $filter,
            'summary' => $summary,
            'waiver_amount' => $waiver,
            'refundTotal' => $refundTotalAmount,
            'invoiceMode' => $invoiceMerged,
            'patientMode' => $patientMode,
            'patientServiceMode' => $patientServiceMode,
            'serviceGroups' => $serviceGroups,
            'financialServices' => $financialServicesMerged,
            'financialServicesModes' => $financialServicesMerged,
            'invoiceMerged' => $invoiceMerged,
            'patientStatus' => $patientOverview,
            'monthlyOverview' => $monthlyOverview,
        ];
        return $records;
    }

    public static function getBedRoomOverview($domain,$request){


        $stats = ParticularModel::where('hms_particular.config_id', $domain['hms_config'])
            ->join('hms_particular_type as pt', 'pt.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type as pmt', 'pmt.id', '=', 'pt.particular_master_type_id')
            ->whereIn('pmt.slug', ['bed', 'cabin'])
            ->where('hms_particular.status', 1)
            ->where('hms_particular.is_delete', 0)
            ->selectRaw('
                pmt.slug as type,
                COUNT(hms_particular.id) AS total_count,
                COUNT(CASE 
                    WHEN hms_particular.is_booked = 1 
                     AND hms_particular.admission_id IS NOT NULL 
                    THEN hms_particular.id 
                END) AS occupied_count,
                COUNT(CASE 
                    WHEN hms_particular.is_booked = 0 
                     AND hms_particular.admission_id IS NULL 
                    THEN hms_particular.id 
                END) AS empty_count
            ')
            ->groupBy('pmt.slug')
            ->get();

        $stats = $stats->map(function ($row) {
            return [
                'type'           => $row->type,
                'total_count'    => (int) $row->total_count,
                'occupied_count' => (int) $row->occupied_count,
                'empty_count'    => (int) $row->empty_count,
            ];
        });
        $patientOverview =self::patientOverview($domain,$request);
        $beds = self::genderBedsOverview($domain);
        $cabins = self::genderRoomsOverview($domain);
        $bedCabins = ['bed' => $beds,'cabin' => $cabins];
        $filter = ['start_date'=>$request['start_date'],'end_date'=>$request['end_date']];
        $records =[
            'filter' => $filter,
            'patientStatus' => $patientOverview,
            'stats' => $stats,
            'bedCabin' => $bedCabins,

        ];
        return $records;
    }

    public static function getInvoiceSummary($domain,$request){

        $summary =self::summaryCollection($domain,$request);
        $patientMode =self::patientModeBaseCollection($domain,$request);
        $patientServiceMode =self::patientServiceModeBaseCollection($domain,$request);
        $userBase =self::userBaseCollection($domain,$request);

        $serviceGroups =self::serviceBaseGroupInvestigation($domain,$request);

        $financialServices =self::financialServiceGroupInvestigation($domain,$request);
        $refundServiceGroups =self::refundFinancialServiceGroupInvestigation($domain,$request);

        $particularInvoiceModes = InvoiceParticularModel::getParticularInvoiceModes();


        $services =self::serviceBaseInvestigation($domain,$request);
        $refundInvestigations =self::refundServiceBaseInvestigation($domain,$request);


        $investigationMaps = [];
        foreach ($services as $service) {
            $modeName = $service['name'];          // string
            $investigationMaps[$modeName] = $service['total']; // correct
        }

        $refundInvestigationMaps = [];
        foreach ($refundInvestigations as $service) {
            $mode = $service['name'];
            $refundInvestigationMaps[$mode] = $service['total'];
        }

        $investigationMerged = [];
        foreach ($services as $mode) {
            $mode_id = $mode->name;
            $total  = $investigationMaps[$mode_id] ?? 0;
            $refund_total  = $refundInvestigationMaps[$mode_id] ?? 0;
            $sub_total = ($total - $refund_total);
            $investigationMerged[] = [
                'name' => $mode_id,
                'total' => $total,
                'refund' => $refund_total, // default 0 if not refunded
                'sub_total' => $sub_total, // default 0 if not refunded
            ];
        }

        $refundPatientRoomBaseCollection =self::refundPatientRoomBaseCollection($domain,$request);
        $refundTotalAmount =self::refundTotalAmount($domain,$request);

        $invoiceModes =self::invoiceModeCollection($domain,$request);
        $refundInvoiceModes =self::refundInvoiceModeCollection($domain,$request);

        $invoiceModeMap = [];
        foreach ($invoiceModes as $invoiceMode) {
            $modeName = $invoiceMode['name'];          // string
            $invoiceModeMap[$modeName] = $invoiceMode['total']; // correct
        }

        $refundInvoiceModeMap = [];
        foreach ($refundInvoiceModes as $service) {
            $mode = $service['name'];
            $refundInvoiceModeMap[$mode] = $service['total'];
        }

        $invoiceMerged = [];
        foreach ($particularInvoiceModes as $mode) {
            $mode_id = $mode->name;
            $total  = $invoiceModeMap[$mode_id] ?? 0;
            $refund_total  = $refundInvoiceModeMap[$mode_id] ?? 0;
            $sub_total = ($total - $refund_total);
            $invoiceMerged[] = [
                'name' => $mode_id,
                'total' => $total,
                'refund' => $refund_total, // default 0 if not refunded
                'sub_total' => $sub_total, // default 0 if not refunded
            ];
        }




        $financialServicesModes = ParticularModeModel::getParticularModuleDropdown('financial-service');
        $refundMap = [];
        foreach ($refundServiceGroups as $refund) {
            $mode_id = $refund['mode_id'];
            $refundMap[$mode_id] = $refund['refund_amount'];
        }

        $serviceMap = [];
        foreach ($financialServices as $service) {
            $mode_id = $service['mode_id'];
            $serviceMap[$mode_id] = $service['total'];
        }
        $financialServicesMerged = [];
        foreach ($financialServicesModes as $mode) {
            $mode_id = $mode->id;
            $total  = $serviceMap[$mode_id] ?? 0;
            $refund_total  = $refundMap[$mode_id] ?? 0;
            $sub_total = ($total - $refund_total);
            $financialServicesMerged[] = [
                'id' => $mode_id,
                'name' => $mode->name,
                'name_bn' => $mode->name_bn,
                'total' => $total,
                'refund' => $refund_total, // default 0 if not refunded
                'sub_total' => $sub_total,
            ];
        }

        $filter = ['start_date'=>$request['start_date'],'end_date'=>$request['end_date']];
        $records =[
            'filter' => $filter,
            'summary' => $summary,
            'refundTotal' => $refundTotalAmount,
            'invoiceMode' => $invoiceMerged,
            'userBase' => $userBase,
            'patientMode' => $patientMode,
            'patientServiceMode' => $patientServiceMode,
            'serviceGroups' => $serviceGroups,
            'services' => $investigationMerged,
            'financialServices' => $financialServicesMerged,
            'financialServicesModes' => $financialServicesMerged,
            'invoiceMerged' => $invoiceMerged,

        ];
        return $records;
    }

    public static function getPatientCollections($domain,$request)
    {
        $entities = InvoiceTransactionModel::where(['hms_invoice.config_id' => $domain['hms_config'],'hms_invoice_transaction.process'=>'Done'])
            ->leftjoin('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_transaction.hms_invoice_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->join('cor_customers as customer','customer.id','=','hms_invoice.customer_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->join('hms_particular_mode as patient_payment_mode','patient_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                'hms_invoice.id',
                'hms_invoice.uid',
                'hms_invoice.parent_id as parent_id',
                'hms_invoice.invoice as invoice',
                'hms_invoice.barcode  as barcode',
                'customer.customer_id as patient_id',
                'customer.health_id',
                'customer.name',
                'customer.mobile',
                'customer.address',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d %b %Y, %h:%i %p") as created_at'),
                DB::raw('DATE_FORMAT(hms_invoice.admission_date, "%d-%M-%Y") as admission_date'),
                DB::raw('DATE_FORMAT(customer.dob, "%d-%M-%Y") as dob'),
                'hms_invoice.process as process',
                'patient_mode.name as patient_mode_name',
                'patient_payment_mode.name as patient_payment_mode_name',
                'patient_payment_mode.slug as patient_payment_mode_slug',
                'createdBy.name as created_by',
                DB::raw('SUM(hms_invoice_transaction.amount) as amount'),
            ])->groupBy('hms_invoice_transaction.hms_invoice_id');


        if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->orderBy('hms_invoice_transaction.updated_at','DESC')
            ->get();
        return $entities;
    }

    public static function patientOverview($domain,$request)
    {

        if (!empty($request['start_date'])) {
            $start_date = (new \DateTime($request['start_date']))->format('Y-m-d 00:00:00');
            $end_date   = (new \DateTime($request['end_date']))->format('Y-m-d 23:59:59');
        } else {
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date   = $date->format('Y-m-d 23:59:59');
        }

        $discharged = InvoiceModel::where('hms_invoice.config_id', $domain['hms_config'])
            ->where('invoice_mode', 'ipd')
            ->where('process', 'discharged')
            ->whereBetween('release_date', [$start_date, $end_date])
            ->count();

        $admission = InvoiceModel::where('config_id', $domain['hms_config'])
            ->where('invoice_mode', 'ipd')
            ->where('process', 'admitted')
            ->whereBetween('admission_date', [$start_date, $end_date])
            ->count();

        $currentPatient = ParticularModel::where('config_id', $domain['hms_config'])
            ->where('is_booked', 1)
            ->whereNotNull('admission_id')
            ->count();

        $data = ['patient_admission' => $admission,'patient_discharged' => $discharged, 'patient_total' => $currentPatient];
        return $data;
    }

    public static function patientWaiverOverview($domain,$request)
    {

        if (!empty($request['start_date'])) {
            $start_date = (new \DateTime($request['start_date']))->format('Y-m-d 00:00:00');
            $end_date   = (new \DateTime($request['end_date']))->format('Y-m-d 23:59:59');
        } else {
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date   = $date->format('Y-m-d 23:59:59');
        }
        $amount = InvoiceParticularModel::where('i.config_id', $domain['hms_config'])
            ->join('hms_invoice as i', 'i.id', '=', 'hms_invoice_particular.hms_invoice_id')
            ->join('hms_patient_waiver as pmt', 'pmt.id', '=', 'hms_invoice_particular.patient_waiver_id')
            ->whereNotNull('pmt.checked_by_id')
            ->whereBetween('pmt.checked_date', [$start_date, $end_date])
            ->selectRaw('
        SUM(hms_invoice_particular.estimate_price * hms_invoice_particular.quantity) as total_amount
        ')->value('total_amount');
        return (float) $amount;
    }

    public static function patientRefundOverview($domain,$request)
    {

        if (!empty($request['start_date'])) {
            $start_date = (new \DateTime($request['start_date']))->format('Y-m-d 00:00:00');
            $end_date   = (new \DateTime($request['end_date']))->format('Y-m-d 23:59:59');
        } else {
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date   = $date->format('Y-m-d 23:59:59');
        }
        $amount = RefundModel::where('i.config_id', $domain['hms_config'])
            ->join('hms_invoice as i', 'i.id', '=', 'hms_invoice_transaction_refund.hms_invoice_id')
            ->where('hms_invoice_transaction_refund.process','Done')
            ->whereBetween('hms_invoice_transaction_refund.updated_at', [$start_date, $end_date])
            ->selectRaw('SUM(hms_invoice_transaction_refund.amount) as total_amount')->value('total_amount');
        return (float) $amount;

    }

    public static function genderBedsOverview($domain)
    {

        $genderRooms = ParticularModel::where('hms_particular.config_id', $domain['hms_config'])
            ->join('hms_particular_type as pt', 'pt.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type as pmt', 'pmt.id', '=', 'pt.particular_master_type_id')
            ->join('hms_particular_details as d', 'd.particular_id', '=', 'hms_particular.id')
            ->join('hms_particular_mode as m', 'm.id', '=', 'd.gender_mode_id')
            ->join('hms_particular_mode as p', 'p.id', '=', 'd.payment_mode_id')
            ->join('hms_particular_mode as pm', 'pm.id', '=', 'd.patient_mode_id')
            ->where('pmt.slug', 'bed')
            ->where('hms_particular.status', 1)
            ->where('hms_particular.is_delete', 0)
            ->selectRaw('
                m.name  as gender,
                p.name  as payment,
                pm.name as patient,
                COUNT(hms_particular.id) AS total_count,
                COUNT(CASE 
                    WHEN hms_particular.is_booked = 1 
                     AND hms_particular.admission_id IS NOT NULL 
                    THEN hms_particular.id 
                END) AS occupied_count,
                COUNT(CASE 
                    WHEN hms_particular.is_booked = 0 
                     AND hms_particular.admission_id IS NULL 
                    THEN hms_particular.id 
                END) AS empty_count
            ')
            ->groupBy('m.name', 'p.name', 'pm.name')
            ->orderBy('m.name')
            ->orderBy('p.name')
            ->orderBy('pm.name')
            ->get();
        return $genderRooms;

    }

    public static function genderRoomsOverview($domain)
    {

        $genderRooms = ParticularModel::where('hms_particular.config_id', $domain['hms_config'])
            ->join('hms_particular_type as pt', 'pt.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type as pmt', 'pmt.id', '=', 'pt.particular_master_type_id')
            ->join('hms_particular_details as d', 'd.particular_id', '=', 'hms_particular.id')
            ->join('hms_particular_mode as m', 'm.id', '=', 'd.gender_mode_id')
            ->join('hms_particular_mode as p', 'p.id', '=', 'd.payment_mode_id')
            ->where('pmt.slug', 'cabin')
            ->where('hms_particular.status', 1)
            ->where('hms_particular.is_delete', 0)
            ->selectRaw('
                m.name  as gender,
                p.name  as payment,
                COUNT(hms_particular.id) AS total_count,
                COUNT(CASE 
                    WHEN hms_particular.is_booked = 1 
                     AND hms_particular.admission_id IS NOT NULL 
                    THEN hms_particular.id 
                END) AS occupied_count,
                COUNT(CASE 
                    WHEN hms_particular.is_booked = 0 
                     AND hms_particular.admission_id IS NULL 
                    THEN hms_particular.id 
                END) AS empty_count
            ')
            ->groupBy('m.name', 'p.name')
            ->orderBy('m.name')
            ->orderBy('p.name')
            ->get();
        return $genderRooms;

    }

    public static function monthlyPatientMode($domain,$mode, $process = '',$field,$request)
    {

        if (!empty($request['start_date'])) {
            $start_date = (new \DateTime($request['start_date']))->format('Y-m-d 00:00:00');
            $end_date   = (new \DateTime($request['end_date']))->format('Y-m-t 23:59:59');
        } else {
            $date = new \DateTime();
            $start_date = $date->format('Y-m-01 00:00:00');
            $end_date   = $date->format('Y-m-t 23:59:59');
        }
        return InvoiceModel::where('hms_invoice.config_id', $domain['hms_config'])
            ->where('invoice_mode', $mode)
            ->when(!empty($process), function ($q) use ($process) {
                $q->where('process', $process);
            }) // ✅ now used
            ->whereBetween($field, [$start_date, $end_date])
            ->selectRaw('DATE('.$field.') as date, COUNT(id) as total')
            ->groupBy(DB::raw('DATE('.$field.')'))
            ->orderBy('date')
            ->get();
    }

    public static function getPatientTickets($domain,$request)
    {

        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->whereIn('mode',['opd','emergency','ipd'])
            ->leftjoin('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->join('cor_customers as customer','customer.id','=','hms_invoice.customer_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->join('hms_particular_mode as patient_payment_mode','patient_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                'hms_invoice.id',
                'hms_invoice.uid',
                'hms_invoice.parent_id as parent_id',
                'hms_invoice.invoice as invoice',
                'hms_invoice.barcode  as barcode',
                'customer.customer_id as patient_id',
                'customer.health_id',
                'customer.name',
                'customer.mobile',
                'customer.address',
                'vr.display_name as visiting_room',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d %b %Y, %h:%i %p") as created_at'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%M-%Y") as appointment'),
                DB::raw('DATE_FORMAT(hms_invoice.admission_date, "%d-%M-%Y") as admission_date'),
                DB::raw('DATE_FORMAT(customer.dob, "%d-%M-%Y") as dob'),
                'hms_invoice.process as process',
                'patient_mode.name as patient_mode_name',
                'patient_payment_mode.name as patient_payment_mode_name',
                'patient_payment_mode.slug as patient_payment_mode_slug',
                'createdBy.name as created_by',
                'hms_invoice_particular.price as amount',
                'hms_invoice_particular.report_mode as invoice_report_mode'
            ]);

        if (isset($request['start_date']) && !empty($request['start_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }

        if (isset($request['patient_mode']) && !empty($request['patient_mode']) && $request['patient_mode'] != 'all'){
            $entities = $entities->where('hms_invoice_particular.report_mode',$request['patient_mode']);
        }
        $entities = $entities->orderBy('hms_invoice.created_at','ASC')->orderBy('hms_invoice_particular.report_mode','ASC')->get();
        return $entities;
    }

    public static function summaryCollection($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->where('hms_invoice_particular.status',1)
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice_transaction as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as total_count'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
            ]);
        if(isset($request['invoice_mode']) && !empty($request['invoice_mode'])){
            $entities = $entities->where('hms_invoice_particular.report_mode',$request['invoice_mode']);
        }
        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $entities = $entities->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['start_date']) && !empty($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->get();
        return $entities;
    }

    public static function invoiceModeCollection($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->where('hms_invoice_particular.status',1)
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice_transaction as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_id')
            ->select([
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
                DB::raw('hms_invoice_particular.mode as name'),
            ])->groupBy('hms_invoice_particular.mode')
            ->orderBy('name','ASC');

        if (isset($request['invoice_mode']) && !empty($request['invoice_mode'])){
            $entities = $entities->where('hms_invoice_particular.report_mode',$request['invoice_mode']);
        }

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $entities = $entities->where('hms_invoice.created_by_id',$request['created_by_id']);
        }

        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->get();
        return $entities;
    }

    public static function refundUserCollection($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->where('hms_invoice_particular.status',1)
            ->where('hms_invoice_particular.is_refund',1)
            ->where('hms_invoice_transaction.process','Done')
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice_transaction_refund as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_refund_id')
            ->join('users as createdBy','createdBy.id','=','hms_invoice_transaction.created_by_id')
            ->join('cor_setting as employeeGroup','employeeGroup.id','=','createdBy.employee_group_id')
            ->select([
                DB::raw('SUM(hms_invoice_transaction.amount) as total'),
                'createdBy.name as name',
                'employeeGroup.name as group_name',
            ])->groupBy('createdBy.name');
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.updated_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.updated_at',[$start_date, $end_date]);
        }
        $entities = $entities->get();
        return $entities;
    }

    public static function refundInvoiceModeCollection($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->where('hms_invoice_particular.status',1)
            ->where('hms_invoice_particular.is_refund',1)
            ->where('hms_invoice_transaction.process','Done')
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice_transaction_refund as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_refund_id')
            ->select([
                DB::raw('SUM(hms_invoice_particular.refund_amount) as total'),
                'hms_invoice_particular.mode as name',
            ])->groupBy('hms_invoice_particular.mode');

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $entities = $entities->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.updated_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.updated_at',[$start_date, $end_date]);
        }
        $entities = $entities->get();
        return $entities;
    }

    public static function patientModeBaseCollection($domain,$request)
    {

        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->whereIn('hms_invoice_particular.mode',['opd','emergency','ipd'])
            ->where('hms_invoice_particular.status',1)
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice_transaction as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as total_count'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
                DB::raw("
                    CONCAT(
                        UPPER(LEFT(hms_invoice_particular.report_mode, 1)),
                        LOWER(SUBSTRING(hms_invoice_particular.report_mode, 2)),
                        '-',
                        UPPER(LEFT(hms_invoice_particular.mode, 1)),
                        LOWER(SUBSTRING(hms_invoice_particular.mode, 2))
                    ) as name
                "),
            ])->groupBy('hms_invoice_particular.mode')->groupBy('hms_invoice_particular.report_mode')->orderBy('name','ASC');

        if(isset($request['invoice_mode']) && !empty($request['invoice_mode'])){
            $entities = $entities->where('hms_invoice_particular.report_mode',$request['invoice_mode']);
        }
        if(isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $entities = $entities->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->get();
        return $entities;
    }

    public static function patientServiceModeBaseCollection($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->whereIn('hms_invoice_particular.mode',['opd','emergency','ipd'])
            ->where('hms_invoice_particular.status',1)
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_particular_mode as hms_particular_mode','hms_invoice.patient_payment_mode_id','=','hms_particular_mode.id')
            ->join('hms_invoice_transaction as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as patient'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
                'hms_particular_mode.name as name',
            ])->groupBy('hms_particular_mode.name');

        if(isset($request['invoice_mode']) && !empty($request['invoice_mode'])){
            $entities = $entities->where('hms_invoice_particular.report_mode',$request['invoice_mode']);
        }
        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $entities = $entities->where('hms_invoice.created_by_id',$request['created_by_id']);
        }
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->get();
        return $entities;
    }

    public static function userBaseCollection($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->where('hms_invoice_particular.status',1)
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice_transaction as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_id')
            ->join('users as createdBy','createdBy.id','=','hms_invoice_transaction.created_by_id')
            ->join('cor_setting as employeeGroup','employeeGroup.id','=','createdBy.employee_group_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as total_count'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
                'createdBy.name as name',
                'employeeGroup.name as group_name',
            ]);
        if(isset($request['invoice_mode']) && !empty($request['invoice_mode'])){
            $entities = $entities->where('hms_invoice_particular.report_mode',$request['invoice_mode']);
        }
        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $entities = $entities->where('hms_invoice_transaction.created_by_id',$request['created_by_id']);
        }
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities->groupBy('hms_invoice_transaction.created_by_id');
        $rows = $entities->get();
        return $rows;
    }

    public static function financialServiceGroupInvestigation($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->whereIn('hms_invoice_particular.mode',['investigation'])
            ->where('hms_invoice_particular.status',1)
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice_transaction as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_id')
            ->leftjoin('hms_particular as hms_particular','hms_particular.id','=','hms_invoice_particular.particular_id')
            ->join('hms_particular_mode as particular_mode','particular_mode.id','=','hms_particular.financial_service_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as total_count'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
                'particular_mode.id as mode_id',
                'particular_mode.name as name',
            ])->groupBy('particular_mode.id');
        if(isset($request['invoice_mode']) && !empty($request['invoice_mode'])){
            $entities = $entities->where('hms_invoice_particular.report_mode',$request['invoice_mode']);
        }
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->orderBy('particular_mode.name','ASC')->get();
        return $entities;
    }

    public static function serviceBaseGroupInvestigation($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->whereIn('hms_invoice_particular.mode',['investigation'])
            ->where('hms_invoice_particular.status',1)
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice_transaction as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_id')
            ->leftjoin('hms_particular as hms_particular','hms_particular.id','=','hms_invoice_particular.particular_id')
            ->join('inv_category as inv_category','inv_category.id','=','hms_particular.category_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as total_count'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
                'inv_category.name as name',
            ])->groupBy('inv_category.id');
        if(isset($request['invoice_mode']) && !empty($request['invoice_mode'])){
            $entities = $entities->where('hms_invoice_particular.report_mode',$request['invoice_mode']);
        }
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->orderBy('inv_category.name','ASC')->get();
        return $entities;
    }

    public static function serviceBaseInvestigation($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->whereIn('hms_invoice_particular.mode',['investigation'])
            ->where('hms_invoice_particular.status',1)
            ->leftjoin('hms_invoice_transaction as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_id')
            ->leftjoin('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->leftjoin('hms_particular as hms_particular','hms_particular.id','=','hms_invoice_particular.particular_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as total_count'),
                DB::raw('SUM(hms_invoice_particular.sub_total) as total'),
                'hms_particular.display_name as name',
            ])->groupBy('particular_id');
        if(isset($request['invoice_mode']) && !empty($request['invoice_mode'])){
            $entities = $entities->where('hms_invoice_particular.report_mode',$request['invoice_mode']);
        }
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->orderBy('hms_particular.name','ASC')->get();
        return $entities;
    }

    public static function refundTotalAmount($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->whereIn('hms_invoice_particular.mode',['investigation','room'])
            ->where('hms_invoice_particular.is_refund',1)
            ->where('hms_invoice_transaction.process',"Done")
            ->join('hms_invoice_transaction_refund as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_refund_id')
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_particular as hms_particular','hms_particular.id','=','hms_invoice_particular.particular_id')
            ->select([DB::raw('SUM(hms_invoice_particular.refund_amount) as refund')]);
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.updated_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.updated_at',[$start_date, $end_date]);
        }
        $entities = $entities->get()->first();
        return $entities;
    }

    public static function refundServiceBaseInvestigation($domain,$request)
    {
        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->whereIn('hms_invoice_particular.mode',['investigation'])
            ->where('hms_invoice_particular.is_refund',1)
            ->where('hms_invoice_transaction.process',"Done")
            ->leftjoin('hms_invoice_transaction_refund as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_refund_id')
            ->leftjoin('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->leftjoin('hms_particular as hms_particular','hms_particular.id','=','hms_invoice_particular.particular_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as count'),
                DB::raw('SUM(hms_invoice_particular.refund_amount) as total'),
                'hms_particular.display_name as name',
            ])->groupBy('particular_id');
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.created_at',[$start_date, $end_date]);
        }
        $entities = $entities->orderBy('hms_particular.name','ASC')
            ->get();
        return $entities;
    }

    public static function refundFinancialServiceGroupInvestigation($domain,$request)
    {

        $entities = InvoiceParticularModel::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->whereIn('hms_invoice_particular.mode',['investigation'])
            ->where('hms_invoice_particular.status',1)
            ->where('hms_invoice_particular.is_refund',1)
            ->where('hms_invoice_transaction.process',"Done")
            ->join('hms_invoice as hms_invoice','hms_invoice.id','=','hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice_transaction_refund as hms_invoice_transaction','hms_invoice_transaction.id','=','hms_invoice_particular.invoice_transaction_refund_id')
            ->leftjoin('hms_particular as hms_particular','hms_particular.id','=','hms_invoice_particular.particular_id')
            ->join('hms_particular_mode as particular_mode','particular_mode.id','=','hms_particular.financial_service_id')
            ->select([
                DB::raw('COUNT(hms_invoice_particular.id) as total_count'),
                DB::raw('SUM(hms_invoice_particular.refund_amount) as refund_amount'),
                'particular_mode.id as mode_id',
                'particular_mode.name as name',
            ])->groupBy('particular_mode.id');
        if (isset($request['start_date']) && isset($request['end_date'])){
            $start_date = new \DateTime($request['start_date']);
            $end_date = new \DateTime($request['end_date']);
            $start_date = $start_date->format('Y-m-d 00:00:00');
            $end_date = $end_date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.updated_at',[$start_date, $end_date]);
        }else{
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice_transaction.updated_at',[$start_date, $end_date]);
        }
        $entities = $entities->orderBy('particular_mode.name','ASC')->get();
        return $entities;

    }

    public static function refundPatientRoomBaseCollection($domain,$request)
    {
        $entities = InvoiceParticularModel::where('hms_invoice.config_id', $domain['hms_config'])
            ->whereIn('hms_invoice_particular.mode', ['room'])
            ->where('hms_invoice_particular.status', 1)
            ->where('hms_invoice_transaction.process','Done')
            ->whereIn('hms_invoice_parent.invoice_mode', ['opd','emergency'])
            ->join('hms_invoice as hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_particular.hms_invoice_id')
            ->join('hms_invoice as hms_invoice_parent', 'hms_invoice_parent.id', '=', 'hms_invoice.parent_id')
            ->join(
                'hms_invoice_transaction_refund as hms_invoice_transaction',
                'hms_invoice_transaction.id',
                '=',
                'hms_invoice_particular.invoice_transaction_refund_id'
            )
            ->select([
               'hms_invoice_particular.mode',
                DB::raw('SUM(hms_invoice_particular.refund_amount) as total'),
            ])
            ->groupBy(
                'hms_invoice_particular.mode',
                'hms_invoice_parent.invoice_mode'
            );
        if (!empty($request['created_by_id'])) {
            $entities->where('hms_invoice.created_by_id', $request['created_by_id']);
        }

        if (isset($request['start_date']) && isset($request['end_date'])) {
            $start_date = (new \DateTime($request['start_date']))->format('Y-m-d 00:00:00');
            $end_date   = (new \DateTime($request['end_date']))->format('Y-m-d 23:59:59');
        } else {
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date   = $date->format('Y-m-d 23:59:59');
        }
        $entities = $entities
            ->whereBetween('hms_invoice_transaction.updated_at', [$start_date, $end_date])
            ->get();
        return $entities;
    }


    public static function dailyOpdEmergencyIpd($domain, $request)
    {
        $data = [
            'age_0_To_4' => self::formatAgeWise(
                self::dailyCount($domain, $request, 0, 4)
            ),
            'age_5_To_14' => self::formatAgeWise(
                self::dailyCount($domain, $request, 5, 14)
            ),
            'age_15_To_24' => self::formatAgeWise(
                self::dailyCount($domain, $request, 15, 24)
            ),
            'age_25_To_49' => self::formatAgeWise(
                self::dailyCount($domain, $request, 25, 49)
            ),
            'age_50_To_120' => self::formatAgeWise(
                self::dailyCount($domain, $request, 50, 120)
            ),
        ];
        $filter = ['start_date'=>$request['start_date'],'end_date'=>$request['end_date']];
        $records =[
            'filter' => $filter,
            'entities' => $data,
        ];
        return $records;
    }

    private static function formatAgeWise($rows)
    {
        $modes = ['opd', 'emergency', 'ipd'];

        $result = [];

        foreach ($modes as $mode) {
            $result[$mode] = [
                'male'   => 0,
                'female' => 0,
            ];
        }

        foreach ($rows as $row) {
            $result[$row->invoice_mode][$row->gender] = (int) $row->total;

        }
        return $result;
    }


    public static function dailyCount($domain, $request, $startAge, $endAge)
    {
        $query = InvoiceModel::where('hms_invoice.config_id', $domain['hms_config'])
            ->whereIn('hms_invoice.invoice_mode', ['opd', 'emergency', 'ipd'])
            ->join('cor_customers as customer', 'customer.id', '=', 'hms_invoice.customer_id')
            ->select([
                DB::raw("COUNT(hms_invoice.id) as total"),
                'hms_invoice.invoice_mode',
                'customer.gender'
            ])
            ->groupBy(
                'customer.gender',
                'hms_invoice.invoice_mode'
            );

        // Date range
        if (!empty($request['start_date']) && !empty($request['end_date'])) {
            $start_date = (new \DateTime($request['start_date']))->format('Y-m-d 00:00:00');
            $end_date   = (new \DateTime($request['end_date']))->format('Y-m-d 23:59:59');
        } else {
            $date = new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date   = $date->format('Y-m-d 23:59:59');
        }

        return $query
            ->whereBetween('customer.age', [$startAge, $endAge])
            ->whereBetween('hms_invoice.created_at', [$start_date, $end_date])
            ->get();
    }



}
