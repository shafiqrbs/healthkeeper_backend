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
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function insertUpdateGroupReport($id)
    {

        $invoice = InvoiceModel::findByIdOrUid($id);
        $transactions = InvoiceTransactionModel::where(['hms_invoice_id'=>$invoice->id,'mode'=>'investigation'])->get();
        foreach ($transactions as $transaction):
            $invoiceParticularCategoryGroup = self::getCategoryGroupInvoice($transaction);
        endforeach;
        dd($transactions);

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
            ])
            ->groupBy('inv_category.id', 'inv_category.name')
            ->get();

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
                    'process' => 'new',
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
