<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Modules\Hospital\App\Entities\InvoiceTransactionRefund;
use Ramsey\Collection\Collection;
use function Symfony\Component\TypeInfo\null;

class InvoiceParticularModel extends Model
{
    use HasFactory;

    protected $table = 'hms_invoice_particular';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            if (empty($model->barcode)) {
                $model->barcode = self::generateUniqueCode(12);
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
        } while (self::where('barcode', $code)->exists());
        return $code;
    }



    public function particular()
    {
        return $this->hasOne(ParticularModel::class, 'id', 'particular_id');
    }

    public function custom_report()
    {
        return $this->hasOne(InvoiceParticularTestReportModel::class, 'invoice_particular_id');
    }

    public function reports()
    {
        return $this->hasMany(InvoicePathologicalReportModel::class, 'invoice_particular_id');
    }

    public static function getPatientCountBedRoom($domain){

        InvoiceModel::where('hms_invoice.config_id', $domain['hms_config'])
            ->where('process', $domain['admitted'])
            ->chunk(25, function ($entities) {
                foreach ($entities as $entity) {
                    self::getPatientSingleCountBedRoom($entity);
                }
            });
        }

    public static function getPatientSingleCountBedRoom($entity)
    {

        if($entity->process !== 'admitted'){
            return false;
        }

        $admissionDate = new \DateTime($entity->admission_date);
        $currentDate   = new \DateTime('now');
        $dayCount = (int) $admissionDate
            ->setTime(0, 0, 0)
            ->diff($currentDate->setTime(0, 0, 0))
            ->days;
        $admissionDay = ($dayCount == 0) ? 1 : $dayCount;
        $totalQuantity = DB::table('hms_invoice_particular')
            ->join('hms_particular', 'hms_particular.id', '=', 'hms_invoice_particular.particular_id')
            ->join('hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_particular.hms_invoice_id')
            ->where('hms_invoice_particular.hms_invoice_id', $entity->id)
            ->where('hms_invoice_particular.status', 1)
            ->whereIn('hms_invoice_particular.mode', ['room','bed','cabin'])
            ->sum('hms_invoice_particular.quantity');
        $remainingDay = ($admissionDay - $totalQuantity);

        if($entity->is_free_bed == 1){
            $roomRent = 0;
        }elseif($entity->room){
            $roomRent = ($entity->room->price * $remainingDay);
        }else{
            return false;
        }
        $amount = InvoiceTransactionModel::where(['hms_invoice_id'=> $entity->id,'process'=>'Done'])->sum('sub_total');
        $refund = RefundModel::getRoomRefundAmount($entity);
        $total = ($amount + $roomRent);
        $entity->update([
            'admission_day' => $admissionDay,
            'payment_day'   => $totalQuantity,
            'consume_day'   => $totalQuantity,
            'remaining_day' => $remainingDay,
            'room_rent'     => $roomRent,
            'total'         => $total,
            'amount'        => $amount,
            'refund_amount' => $refund['refund_amount'] ?? 0,
            'refund_day'    => $refund['refund_quantity'] ?? 0,
        ]);
    }

    public static function getCountBedRoom($id){

        $entity = InvoiceModel::find($id);
        if($entity->process !== 'admitted'){
            return false;
        }
        $admissionDate = new \DateTime($entity->admission_date);
        $currentDate   = new \DateTime('now');
        $dayCount = (int) $admissionDate
            ->setTime(0, 0, 0)
            ->diff($currentDate->setTime(0, 0, 0))
            ->days;
        $admissionDay = ($dayCount == 0) ? 1 : $dayCount;
        $totalQuantity = DB::table('hms_invoice_particular')
            ->join('hms_particular', 'hms_particular.id', '=', 'hms_invoice_particular.particular_id')
            ->where('hms_invoice_particular.hms_invoice_id', $entity->id)
            ->where('hms_invoice_particular.status', 1)
            ->whereIn('hms_invoice_particular.mode', ['room','bed','cabin'])
            ->sum('hms_invoice_particular.quantity');
        $remainingDay = ($admissionDay - $totalQuantity);
        if($entity->room->price == 0 and $entity->is_free_bed == 1){
            $roomRent = 0;
        }else{
            $roomRent = ($entity->room->price * $remainingDay);
        }
        $amount = InvoiceTransactionModel::where(['hms_invoice_id'=> $entity->id,'process'=>'Done'])->sum('sub_total');
        $refund = RefundModel::getRoomRefundAmount($entity);
        $total = ($amount + $roomRent);
        $entity->update([
            'admission_day' => $admissionDay,
            'payment_day'   => $totalQuantity,
            'consume_day'   => $totalQuantity,
            'remaining_day' => $remainingDay,
            'room_rent'     => $roomRent,
            'total'         => $total,
            'amount'        => $amount,
            'refund_amount' => $refund['refund_amount'] ?? 0,
            'refund_day'    => $refund['refund_quantity'] ?? 0,
        ]);
    }

    public static function updateWaverParticular($entity,$data)
    {

        // Reset all to 0 first
        $array = json_decode($data, true);
        self::where('hms_invoice_id', $entity)->update(['is_waiver' => 0]);
        // Set only selected ones to 1
        self::where('hms_invoice_id', $entity)
            ->whereIn('id', $array)
            ->update(['is_waiver' => 1]);

    }

   public static function getParticularInvoiceModes()
    {
        return $entities = DB::table('hms_invoice_particular')
            ->select('hms_invoice_particular.mode as name')
            ->groupBy('hms_invoice_particular.mode')->get();
    }

   public static function checkExistingWaiver($data)
   {
        // Reset all to 0 first
        $entity = $data['hms_invoice_id'];
        $array = $data['particulars'];

        $query = self::where('hms_invoice_id', $entity)
            ->whereIn('id', $array)
            ->where('is_waiver', 1);

        $count = $query->count();
        $availableIds = $query->pluck('id')->toArray();
        $missingIds = array_diff($array, $availableIds);
        return [
            'count' => $count,
            'available' => $availableIds,
            'new' => $missingIds,
        ];
    }


    public static function getGroupParticulars($id)
    {
        $rows = self::join('hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_particular.hms_invoice_id')
            ->where(['hms_invoice_particular.mode' => 'investigation'])
            ->where(function ($query) use ($id) {
                $query->where('hms_invoice.id', $id)
                    ->orWhere('hms_invoice.uid', $id);
            })
            ->select([
                'hms_invoice_particular.unique_id',
                'hms_invoice_particular.id',
                'hms_invoice_particular.invoice_transaction_id',
                'hms_invoice_particular.name',
                'hms_invoice_particular.content',
                'hms_invoice_particular.price',
                'hms_invoice_particular.quantity',
                'hms_invoice_particular.status',
                'hms_invoice_particular.process',
            ])
            ->orderBy('hms_invoice_particular.unique_id')
            ->get()
            ->groupBy('unique_id')
            ->map(function ($group) {
                return [
                    'created' => $group->first()->unique_id,
                    'items'     => $group->values(),  // child rows
                ];
            })
            ->values();
        return $rows;
    }







}
