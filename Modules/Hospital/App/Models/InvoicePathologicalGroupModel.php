<?php

namespace Modules\Hospital\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class InvoicePathologicalGroupModel extends Model
{
    use HasFactory;

    protected $table = 'hms_invoice_pathological_group';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            if (empty($model->barcode)) {
                $model->uid = self::generateUniqueCode(12);
            }
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function generateUniqueCode($length = 12)
    {
        do {
            // Generate a random 12-digit number
            $code = str_pad(random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (self::where('uid', $code)->exists());
        return $code;
    }

    public function items()
    {
        return $this->hasMany(InvoiceParticularModel::class, 'invoice_pathological_group_id');
    }
    public function reports()
    {
        return $this->hasMany(InvoicePathologicalReportModel::class, 'invoice_pathological_group_id')->orderBy('ordering', 'ASC');
    }


    public static function getCategoryGroupShow($domain,$id)
    {

        self::insertUpdateGroupReport($id);

        $userId = $domain['user_id'];
        $rooms = ParticularModel::where('employee_id',$userId)->first();
        $roomIds = ($rooms->particularDetails->diagnostic_room_ids);
        $entity = InvoiceModel::where(['hms_invoice.uid'=>$id])
            ->leftjoin('cor_customers','cor_customers.id','=','hms_invoice.customer_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->leftjoin('hms_prescription as prescription','prescription.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('users as prescription_doctor','prescription_doctor.id','=','prescription.created_by_id')
            ->leftjoin('hms_particular as room','room.id','=','hms_invoice.room_id')
            ->leftjoin('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('hms_particular as admit_consultant','admit_consultant.id','=','hms_invoice.admit_consultant_id')
            ->leftjoin('hms_particular as admit_doctor','admit_doctor.id','=','hms_invoice.admit_doctor_id')
            ->leftjoin('hms_particular_mode as admit_unit','admit_unit.id','=','hms_invoice.admit_unit_id')
            ->leftjoin('hms_particular_mode as admit_department','admit_department.id','=','hms_invoice.admit_department_id')
            ->leftjoin('hms_particular_mode as particular_payment_mode','particular_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                'hms_invoice.*',
                DB::raw('DATE_FORMAT(hms_invoice.updated_at, "%d-%m-%y") as created'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%m-%y") as appointment'),
                'hms_invoice.invoice as invoice',
                'hms_invoice.total as total',
                'hms_invoice.comment',
                'hms_invoice.guardian_name as guardian_name',
                'hms_invoice.guardian_mobile as guardian_mobile',
                'cor_customers.name as name',
                'cor_customers.mobile as mobile',
                'cor_customers.id as customer_id',
                'cor_customers.customer_id as patient_id',
                'cor_customers.health_id as health_id',
                'cor_customers.gender as gender',
                'cor_customers.father_name',
                'cor_customers.mother_name',
                'cor_customers.upazilla_id',
                'cor_customers.country_id',
                'cor_customers.profession',
                'cor_customers.religion_id',
                'cor_customers.nid',
                'cor_customers.identity_mode',
                'cor_customers.address',
                'cor_customers.permanent_address',
                DB::raw('DATE_FORMAT(cor_customers.dob, "%d-%m-%y") as dob'),
                'cor_customers.identity_mode as identity_mode',
                'hms_invoice.year as year',
                'hms_invoice.month as month',
                'hms_invoice.day as day',
                'createdBy.username as created_by_user_name',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'room.name as room_name',
                'patient_mode.name as mode_name',
                'particular_payment_mode.name as payment_mode_name',
                'hms_invoice.process as process',
                'admit_consultant.name as admit_consultant_name',
                'admit_unit.name as admit_unit_name',
                'admit_department.name as admit_department_name',
                'admit_doctor.name as admit_doctor_name',
                'prescription.id as prescription_id',
                DB::raw('DATE_FORMAT(prescription.created_at, "%d-%m-%Y") as prescription_created'),
                'prescription_doctor.employee_id as prescription_doctor_id',
                'prescription_doctor.name as prescription_doctor_name',
            ])
            ->with([
                'invoice_transaction' => function ($query) use($roomIds) {
                    $query->select([
                        'hms_invoice_transaction.id',
                        'hms_invoice_transaction.hms_invoice_id',
                        'hms_invoice_transaction.mode',
                        'hms_invoice_transaction.process',
                        'hms_invoice_transaction.total',
                        'hms_invoice_transaction.created_at'
                    ])
                        ->where('hms_invoice_transaction.mode', 'investigation')
                        ->where('hms_invoice_transaction.process', 'Done')
                        ->whereHas('items', function ($itemQuery) use($roomIds) {
                            $itemQuery->join('hms_particular as hp', 'hp.id', '=', 'hms_invoice_particular.particular_id')
                                ->where('hms_invoice_particular.status', 1)
                                ->whereIn('hp.diagnostic_room_id',$roomIds)
                                ->where('hp.is_available', 1);
                        }, '>=', 1)
                        ->orderBy('hms_invoice_transaction.created_at', 'DESC')
                        ->with(['report_groups']);
                }
            ])
            ->first();

        return $entity;
    }

    public static function insertUpdateGroupReport($id)
    {

        $invoice = InvoiceModel::findByIdOrUid($id);
        $transactions = InvoiceTransactionModel::where(['hms_invoice_id'=>$invoice->id,'mode'=>'investigation'])->get();
        foreach ($transactions as $transaction):
            self::getCategoryGroupInvoice($transaction);
        endforeach;

    }

    public static function getCategoryGroupInvoice($transaction)
    {

        $entities = InvoiceParticularModel::where('hms_invoice_particular.invoice_transaction_id', $transaction->id)
            ->join('hms_particular', 'hms_particular.id', '=', 'hms_invoice_particular.particular_id')
            ->join('inv_category', 'inv_category.id', '=', 'hms_particular.category_id')
            ->where('hms_invoice_particular.mode', 'investigation')
            ->where('hms_invoice_particular.status', 1)
            ->where('hms_particular.is_report_format', 1)
            ->where('hms_particular.is_available', 1)
            ->select([
                'inv_category.id as category_id',
                'inv_category.name as name',
                DB::raw('GROUP_CONCAT(hms_invoice_particular.name SEPARATOR ", ") as particular_names'),
            ])
            ->groupBy('inv_category.id', 'inv_category.name')
            ->get();

        if ($entities->isNotEmpty()) {
            foreach ($entities as $entity) {
                $date = new \DateTime("now");
                $groupReport = self::updateOrCreate(
                    [
                        'hms_invoice_id' => $transaction->hms_invoice_id,
                        'invoice_transaction_id' => $transaction->id,
                        'category_id' => $entity->category_id,
                    ],
                    [
                        'name' => $entity->name,
                        'report_name' => $entity->particular_names,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ]);
                InvoiceParticularModel::where('hms_invoice_particular.invoice_transaction_id', $transaction->id)
                    ->where('hms_invoice_particular.category_id', $entity->category_id)
                    ->update([
                        'invoice_pathological_group_id' => $groupReport->id
                    ]);
            }
        }


    }

    public static function generateReport($reportId)
    {
        $reportGroup = self::find($reportId);
        $i = 0;
        if (!$reportGroup || $reportGroup->items->isEmpty()) {
            return;
        }
        foreach ($reportGroup->items as $entity) {
            $investigation = $entity->particular_id;
            $reportElements = InvestigationReportFormatModel::where('particular_id', $investigation)->get();
            foreach ($reportElements as $row) {
                InvoicePathologicalReportModel::updateOrCreate(
                    [
                        'invoice_particular_id' => $entity->id,
                        'particular_id' => $investigation,
                        'investigation_report_format_id' => $row->id,
                        'invoice_pathological_group_id' => $reportId,
                    ],
                    [
                        'name' => $row->name,
                        'reference_value' => $row->reference_value,
                        'unit' => $row->unit,
                        'sample_value' => $row->sample_value,
                        'ordering' => ++$i,
                    ]
                );
            }
        }
    }

}
