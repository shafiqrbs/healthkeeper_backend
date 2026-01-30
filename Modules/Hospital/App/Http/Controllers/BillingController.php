<?php

namespace Modules\Hospital\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\CustomerRequest;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\UserModel;
use Modules\Hospital\App\Entities\Prescription;
use Modules\Hospital\App\Http\Requests\OPDRequest;
use Modules\Hospital\App\Http\Requests\PrescriptionRequest;
use Modules\Hospital\App\Models\BillingModel;
use Modules\Hospital\App\Models\HospitalConfigModel;
use Modules\Hospital\App\Models\HospitalSalesModel;
use Modules\Hospital\App\Models\InvoiceContentDetailsModel;
use Modules\Hospital\App\Models\InvoiceModel;
use Modules\Hospital\App\Models\InvoiceParticularModel;
use Modules\Hospital\App\Models\InvoicePathologicalReportModel;
use Modules\Hospital\App\Models\InvoiceTransactionModel;
use Modules\Hospital\App\Models\IpdModel;
use Modules\Hospital\App\Models\LabInvestigationModel;
use Modules\Hospital\App\Models\OPDModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularModeModel;
use Modules\Hospital\App\Models\PatientModel;
use Modules\Hospital\App\Models\PrescriptionModel;
use Modules\Hospital\App\Models\RefundModel;


class BillingController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if ($userId && !empty($userId)){
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request){

        $domain = $this->domain;
        $data = BillingModel::getRecords($request,$domain);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Display a listing of the resource.
     */
    public function findBill(Request $request){

        $domain = $this->domain;
        $data = BillingModel::getFinalBillRecords($request,$domain);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }



    /**
     * Display a listing of the resource.
     */
    public function admission(Request $request){

        $domain = $this->domain;
        $data = BillingModel::getRecords($request,$domain);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Show the specified resource.
     *//**/
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = InvoiceModel::findByIdOrUid($id);
        if($entity->invoice_mode == 'ipd' and in_array($entity->process,['admitted','paid','refund'])){
            InvoiceParticularModel::getPatientSingleCountBedRoom($entity);
        }
        if($entity->process == 'billing'){
            $entity = BillingModel::getAdmissionBilling($id);
        }else{
            $entity = BillingModel::getShow($id);
        }
        $data = $service->returnJosnResponse($entity);
        return $data;

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function transaction($id,$reportId)
    {
        $service = new JsonRequestResponse();
        $invoiceParticular = InvoiceTransactionModel::with(['items','createdDoctorInfo'])->find($reportId);
        $data = $service->returnJosnResponse($invoiceParticular);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $domain = $this->domain;
        $data = $request->all();
        $entity = InvoiceModel::findByIdOrUid($id);
        if($entity->process == "billing"){
           $transactionId = InvoiceTransactionModel::insertAdmissionInvoiceTransaction($domain,$entity,$data);
           $invoice = InvoiceTransactionModel::showInvoiceData($transactionId);
        }else{
           $transactionId =  InvoiceTransactionModel::insertInvoiceTransaction($domain,$entity,$data);
           $invoice = InvoiceTransactionModel::showInvoiceData($transactionId);
        }
        $amount = InvoiceTransactionModel::where('hms_invoice_id', $entity->id)->where('process','Done')->sum('amount');
        $total = InvoiceParticularModel::where('hms_invoice_id', $entity->id)->where('status',true)->sum('sub_total');
        InvoiceParticularModel::getCountBedRoom($entity->id);
        $entity->update(['sub_total' => $total , 'total' => $total, 'amount' => $amount]);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($invoice);

    }

    public function inlineUpdate(Request $request,$id)
    {
        $input = $request->all();
        $findParticular = InvoicePathologicalReportModel::find($id);
        $findParticular->result = $input['result'];
        $findParticular->save();
        return response()->json(['success' => $findParticular]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $transaction = InvoiceTransactionModel::find($id);
        $entity = InvoiceModel::findByIdOrUid($transaction->hms_invoice_id);
        $service = new JsonRequestResponse();
        $transaction->delete();
        $amount = InvoiceTransactionModel::where('hms_invoice_id', $entity->id)->where('process','Done')->sum('amount');
        $total = InvoiceParticularModel::where('hms_invoice_id', $entity->id)->where('status',true)->sum('sub_total');
        InvoiceParticularModel::getCountBedRoom($entity->id);
        $entity->update(['sub_total' => $total , 'total' => $total, 'amount' => $amount]);
        $status = ['message' => 'delete'];
        return $service->returnJosnResponse($status);
    }

     /**
     * Remove the specified resource from storage.
     */
    public function print($id)
    {
        $service = new JsonRequestResponse();
        $entity = InvoiceTransactionModel::showInvoiceData($id);
        return $service->returnJosnResponse($entity);
    }

    /**
     * Show the specified resource.
     */
    public function finalBillDetails($id)
    {
        $entity = InvoiceModel::findByIdOrUid($id);
        $service = new JsonRequestResponse();
        InvoiceTransactionModel::updateInvoiceTransaction($entity);
        if(in_array($entity->process,['admitted','paid','refund']) and ){
            $entity->update([
                'admission_day' => 0,
                'payment_day'   => 0,
                'consume_day'   => 0,
                'remaining_day' => 0,
                'room_rent'     => 0,
                'total'         => 0,
                'amount'        => 0,
                'refund_amount' => 0,
                'refund_day'    => 0,
            ]);
            InvoiceParticularModel::getPatientSingleCountBedRoom($entity);
        }
        $entity = BillingModel::getFinalBillShow($id);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function finalBillProcess($id)
    {
        $domain = $this->domain;
        $entity = InvoiceModel::findByIdOrUid($id);
        $service = new JsonRequestResponse();
        if($entity->process == 'admitted'){
            InvoiceParticularModel::getPatientSingleCountBedRoom($entity);
        }
        $date =  new \DateTime("now");
        if($entity->is_free_bed == 1) {
            $entity->update([
                'process' => 'paid',
                'release_date' => $date,
                'consume_day' => $entity->admission_day,
                'remaining_day' => $entity->admission_day,
                'payment_day' => $entity->payment_day]);
        }elseif((float)$entity->amount === (float)$entity->total and (int)$entity->remaining_day === 0){
            $entity->update(['process' => 'paid','release_date'=>$date]);
        }else {
            $data = InvoiceTransactionModel::finalBillClosing($domain, $entity);
            if ($data['mode'] == 'refund') {
                $entity->update(['process' => 'refund','release_date'=> $date]);
            }else{
                $entity->update(['process' => 'paid','release_date'=> $date]);
            }
        }
        $status = ['status'=>'success'];
        $data = $service->returnJosnResponse($status);
        return $data;

    }

}
