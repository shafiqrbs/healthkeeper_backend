<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\CustomerModel;
use Modules\Inventory\App\Models\SalesModel;


class PatientArchiveModel extends Model
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


    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            if (empty($model->barcode)) {
                $model->barcode = self::generateUniqueBarcode(12);
                $model->uid = self::generateUniqueCode(12);
            }
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public function customer()
    {
        return $this->hasOne(CustomerModel::class, 'id', 'customer_id');
    }

    public function parent()
    {
        return $this->hasOne(InvoiceModel::class, 'id', 'parent_id');
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

     public function invoice_transaction_refund()
    {
        return $this->hasMany(RefundModel::class, 'hms_invoice_id');
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

    public function admission_patient()
    {
        return $this->hasOne(AdmissionPatientModel::class, 'hms_invoice_id');
    }

    public function prescription_medicine()
    {
        return $this->hasMany(PatientPrescriptionMedicineModel::class, 'hms_invoice_id');
    }

    public function prescription_medicine_history()
    {
        return $this->hasMany(AdmissionPatientPrescriptionHistoryModel::class, 'hms_invoice_id');
    }

    public static function getRecords($request,$domain)
    {
        $sortBy =  isset($request['sortBy']) && $request['sortBy'] ? $request['sortBy'] : 'updated_at';
        $orderBy =  isset($request['order']) && $request['order'] ? $request['order'] : 'DESC';

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $entities = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->leftjoin('hms_prescription as prescription','prescription.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('users as doctor','doctor.id','=','prescription.created_by_id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->join('cor_customers as customer','customer.id','=','hms_invoice.customer_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->join('hms_particular_mode as patient_payment_mode','patient_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->leftjoin('hms_invoice_patient_referred as hms_invoice_patient_referred','hms_invoice_patient_referred.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('hms_particular as referred_vr','referred_vr.id','=','hms_invoice_patient_referred.referred_opd_room_id')
            ->select([
                'hms_invoice.id',
                'hms_invoice.uid',
                'hms_invoice.parent_id as parent_id',
                'prescription.id as prescription_id',
                'prescription.uid as prescription_uid',
                'prescription.created_by_id as prescription_created_by_id',
                'hms_invoice.invoice as invoice',
                'hms_invoice.barcode  as barcode',
                'customer.customer_id as patient_id',
                'customer.health_id',
                'doctor.name as doctor_name',
                'customer.name',
                'customer.mobile',
                'customer.address',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d %b %Y, %h:%i %p") as created_at'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%M-%Y") as appointment'),
                DB::raw('DATE_FORMAT(hms_invoice.admission_date, "%d-%M-%Y") as admission_date'),
                DB::raw('DATE_FORMAT(customer.dob, "%d-%M-%Y") as dob'),
                'hms_invoice.process as process',
                'vr.name as visiting_room',
                'vr.display_name as room_name',
                'referred_vr.display_name as referred_room_name',
                'patient_mode.name as patient_mode_name',
                'patient_payment_mode.name as patient_payment_mode_name',
                'patient_payment_mode.slug as patient_payment_mode_slug',
                'createdBy.name as created_by',
                'hms_invoice.sub_total as total',
                'hms_invoice.amount as amount',
                'hms_invoice.referred_mode as referred_mode',
                'prescription.diabetes as diabetes',
                'prescription.blood_pressure as blood_pressure',
                'hms_invoice.admission_day as admission_day',
                'hms_invoice.consume_day as consume_day',
                'hms_invoice.remaining_day as remaining_day',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $term = trim($request['term']);
            $entities = $entities->where(function ($q) use ($term) {
                $q->where('hms_invoice.invoice', 'LIKE', "%{$term}%")
                    ->orWhere('hms_invoice.uid', 'LIKE', "%{$term}%")
                    ->orWhere('customer.customer_id', 'LIKE', "%{$term}%")
                    ->orWhere('customer.name', 'LIKE', "%{$term}%")
                    ->orWhere('customer.mobile', 'LIKE', "%{$term}%")
                    ->orWhere('customer.nid', 'LIKE', "%{$term}%")
                    ->orWhere('customer.health_id', 'LIKE', "%{$term}%");
            });
        }

        $entities = $entities->whereIn('hms_invoice.process',['discharged','closed','done','paid','re-admission']);

        if (isset($request['patient_mode']) && !empty($request['patient_mode']) && $request['patient_mode'] !== 'all' ){
            if (is_array($request['patient_mode'])) {
                $entities = $entities->whereIn('patient_mode.slug', $request['patient_mode']);
            } else {
                $entities = $entities->where('patient_mode.slug', $request['patient_mode']);
            }
        }

        if (isset($request['room_id']) && !empty($request['room_id'])){
            $entities = $entities->where('hms_invoice.room_id',$request['room_id']);
        }

        if ($request->filled('room_ids') && is_array($request['room_ids'])) {
            $intNumbers = array_map('intval', $request['room_ids']);
            $entities = $entities->whereIn('hms_invoice.room_id', $intNumbers);
        }

        if (isset($request['created_by_id']) && !empty($request['created_by_id'])){
            $entities = $entities->where('hms_invoice.created_by_id',$request['created_by_id']);
        }

         if (isset($request['customer_id']) && !empty($request['customer_id'])){
            $entities = $entities->where('hms_invoice.customer_id',$request['customer_id']);
        }

        if (isset($request['created']) and !empty($request['created'])) {
            $date = !empty($request['created'])
                ? new \DateTime($request['created'])
                : new \DateTime();
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice.created_at', [$start_date, $end_date]);
        }

        $total  = $entities->count();
        $entities = $entities->skip($skip)->take($perPage);


        if ($sortBy == "visiting_room"){
            $entities = $entities->orderBy("vr.name",$orderBy);
        }elseif ($sortBy == "gender"){
            $entities = $entities->orderBy("customer.gender",$orderBy);
            $entities = $entities->orderBy("vr.name",'ASC');
        }else{
            $entities = $entities->orderBy("hms_invoice.{$sortBy}",$orderBy);
            $entities = $entities->orderBy("vr.name",'ASC');
        }
        $entities = $entities->get();
        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }

    public static function getShow($id)
    {

            $entity = self::where(function ($query) use ($id) {
                $query->where('hms_invoice.id', '=', $id)
                    ->orWhere('hms_invoice.uid', '=', $id);
            })
            ->leftjoin('cor_customers','cor_customers.id','=','hms_invoice.customer_id')
            ->leftjoin('cor_locations','cor_locations.id','=','cor_customers.upazilla_id')
            ->leftjoin('inv_sales','inv_sales.id','=','hms_invoice.sales_id')
            ->leftjoin('hms_prescription as prescription','prescription.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('users as doctor','doctor.id','=','prescription.created_by_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->leftjoin('hms_particular as room','room.id','=','hms_invoice.room_id')
            ->leftjoin('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('hms_particular_mode as particular_payment_mode','particular_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->leftjoin('hms_invoice as invoice_parent','invoice_parent.id','=','hms_invoice.parent_id')
            ->leftjoin('hms_particular_mode as parent_patient_mode','parent_patient_mode.id','=','invoice_parent.patient_mode_id')
            ->select([
                'hms_invoice.*',
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d-%m-%y") as created'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%m-%y") as appointment'),
                'hms_invoice.invoice as invoice',
                'parent_patient_mode.name as parent_patient_mode_name',
                'parent_patient_mode.slug as parent_patient_mode_slug',
                'hms_invoice.total as total',
                'hms_invoice.barcode',
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
                'cor_locations.upazila',
                'cor_locations.district',
                'cor_customers.country_id',
                'cor_customers.profession',
                'cor_customers.religion_id',
                'cor_customers.nid',
                'cor_customers.identity_mode',
                'cor_customers.address',
                'cor_customers.permanent_address',
                DB::raw('DATE_FORMAT(cor_customers.dob, "%d-%m-%y") as dob'),
                DB::raw('DATE_FORMAT(cor_customers.dob,"%Y-%m-%d") as date_of_birth'),
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
                'hms_invoice.referred_mode as referred_mode',
            ])
            ->with(['invoice_particular' => function ($query) {
                $query->select([
                    'hms_invoice_particular.id',
                    'hms_invoice_particular.hms_invoice_id',
                    'hms_invoice_particular.name as item_name',
                    'hms_invoice_particular.quantity',
                    'hms_invoice_particular.price',
                ]);
            }])->with(['prescription_medicine'])
            ->first();

        return $entity;
    }

    public static function getIpdShow($id)
    {
        $entity = self::where(function ($query) use ($id) {
            $query->where('hms_invoice.id', '=', $id)
                ->orWhere('hms_invoice.uid', '=', $id);
        })
            ->leftjoin('cor_customers','cor_customers.id','=','hms_invoice.customer_id')
            ->leftjoin('cor_setting as religion','religion.id','=','cor_customers.religion_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->leftjoin('users as admittedBy','admittedBy.id','=','hms_invoice.admitted_by_id')
            ->leftjoin('hms_prescription as prescription','prescription.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('hms_admission_patient_details as admission_patient','admission_patient.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('users as prescription_doctor','prescription_doctor.id','=','prescription.created_by_id')
            ->leftjoin('hms_particular as room','room.id','=','hms_invoice.room_id')
            ->leftjoin('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('hms_particular as admit_consultant','admit_consultant.id','=','hms_invoice.admit_consultant_id')
            ->leftjoin('hms_particular as admit_doctor','admit_doctor.id','=','hms_invoice.admit_doctor_id')
            ->leftjoin('hms_particular_mode as admit_unit','admit_unit.id','=','hms_invoice.admit_unit_id')
            ->leftjoin('hms_particular_mode as admit_department','admit_department.id','=','hms_invoice.admit_department_id')
            ->leftjoin('hms_particular_mode as particular_payment_mode','particular_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->leftjoin('hms_invoice as invoice_parent','invoice_parent.id','=','hms_invoice.parent_id')
            ->leftjoin('hms_particular_mode as parent_patient_mode','parent_patient_mode.id','=','invoice_parent.patient_mode_id')
            ->select([
                'hms_invoice.*',
                'parent_patient_mode.name as parent_patient_mode_name',
                'parent_patient_mode.slug as parent_patient_mode_slug',
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%m-%Y") as appointment'),
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
                'religion.name as religion_name',
                'cor_customers.permanent_address',
                DB::raw('DATE_FORMAT(cor_customers.dob, "%m-%d-%Y") as dob'),
                'cor_customers.identity_mode as identity_mode',
                'hms_invoice.year as year',
                'hms_invoice.month as month',
                'hms_invoice.day as day',
                'createdBy.username as created_by_user_name',
                'createdBy.name as created_by_name',
                'admittedBy.name as admitted_by_name',
                'createdBy.id as created_by_id',
                'room.display_name as room_name',
                'patient_mode.name as mode_name',
                'particular_payment_mode.name as payment_mode_name',
                'hms_invoice.process as process',
                'admit_consultant.name as admit_consultant_name',
                'admit_unit.name as admit_unit_name',
                'admit_department.name as admit_department_name',
                'admit_doctor.name as admit_doctor_name',
                'prescription.id as prescription_id',
                'prescription.uid as prescription_uid',
                'prescription.json_content as json_content',
                'prescription_doctor.name as prescription_doctor_name',
                'admission_patient.vital_chart_json as vital_chart_json',
                'admission_patient.insulin_chart_json as insulin_chart_json',
                'admission_patient.change_mode as change_mode',
                'admission_patient.comment as change_comment',
                'admission_patient.reason as reason',
                'admission_patient.cause_death as cause_death',
                'admission_patient.about_death as about_death',
                'admission_patient.diseases_profile as diseases_profile',
                'admission_patient.death_date_time as death_date_time',
                'admission_patient.reason as reason',
                'admission_patient.referred_hospital as referred_hospital',
            ])
            ->with(['invoice_particular' => function ($query) {
                $query->select([
                    'hms_invoice_particular.id',
                    'hms_invoice_particular.hms_invoice_id',
                    'hms_invoice_particular.name as item_name',
                    'hms_invoice_particular.quantity',
                    'hms_invoice_particular.price',
                    'hms_invoice_particular.sub_total',
                    'hms_invoice_particular.process',
                    'hms_invoice_particular.is_waiver',
                    'hms_invoice_particular.patient_waiver_id',
                ])->where('hms_invoice_particular.mode','investigation');
            }])
            ->with(['invoice_transaction' => function ($query) {
                $query->select([
                    'hms_invoice_transaction.id',
                    'hms_invoice_transaction.hms_invoice_id',
                    'hms_invoice_transaction.created_by_id',
                    'hms_invoice_transaction.mode',
                    'hms_invoice_transaction.sub_total',
                    'hms_invoice_transaction.process',
                    DB::raw('DATE_FORMAT(hms_invoice_transaction.created_at, "%d-%m-%y") as created'),
                ])->orderBy('hms_invoice_transaction.created_at','DESC');
            }])->with(['prescription_medicine'])->with('prescription_medicine_history')
            ->first();

        return $entity;
    }

}
