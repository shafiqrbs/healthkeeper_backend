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
use Modules\Hospital\App\Models\HospitalConfigModel;
use Modules\Hospital\App\Models\HospitalSalesModel;
use Modules\Hospital\App\Models\InvoiceContentDetailsModel;
use Modules\Hospital\App\Models\InvoiceModel;
use Modules\Hospital\App\Models\InvoiceParticularModel;
use Modules\Hospital\App\Models\InvoiceParticularTestReportModel;
use Modules\Hospital\App\Models\InvoicePathologicalReportModel;
use Modules\Hospital\App\Models\InvoiceTransactionModel;
use Modules\Hospital\App\Models\LabInvestigationModel;
use Modules\Hospital\App\Models\OPDModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularModeModel;
use Modules\Hospital\App\Models\PatientModel;
use Modules\Hospital\App\Models\PrescriptionModel;



class LabInvestigationController extends Controller
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
        $data = LabInvestigationModel::getRecords($request,$domain);
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
    public function labReports(Request $request){

        $domain = $this->domain;
        $data = LabInvestigationModel::getLabReports($request,$domain);
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
    public function categoryGroup($id){

        $domain = $this->domain;
        $entity = LabInvestigationModel::getCategoryGroupShow($domain,$id);
        $service = new JsonRequestResponse();
        $response = $service->returnJosnResponse($entity);
        return $response;
    }

    /**
     * Show the specified resource.
     *//**/
    public function show($id)
    {
        $domain = $this->domain;
        $service = new JsonRequestResponse();
        $entity = LabInvestigationModel::getShow($domain,$id);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function reportKeywordSearch(Request $request,$id)
    {
        $domain = $this->domain;
        $input = $request->input();
        $mode = $input['field_name'];
        $term = $input['term'];

        $dropdown = InvoiceParticularTestReportModel::reportKeywordSearch($id,$mode,$term);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($dropdown);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function barcodeConfirm($id)
    {
        $service = new JsonRequestResponse();
        InvoiceParticularModel::where('uid', $id)->first()->update([
            'sample_collected_name' => $this->domain['user_name'],
            'sample_collected_by_id' => $this->domain['user_id'],
            'process' => 'Tagged',
            'collection_date' => now(),
        ]);
        return  $service->returnJosnResponse('valid');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function report($id,$reportId)
    {
        $service = new JsonRequestResponse();
        LabInvestigationModel::generateReport($reportId);
        $invoiceParticular = InvoiceParticularModel::with(['reports','particular:id,slug,diagnostic_department_id as diagnostic_department_id,diagnostic_room_id as diagnostic_room_id,is_custom_report,instruction,specimen,slug','particular.category:id,name','custom_report'])->where(['uid'=> $reportId])->first();
        if($invoiceParticular){
            $data = $service->returnJosnResponse($invoiceParticular);
        }else{
            $data = $service->returnJosnResponse(null);
        }
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function print($id)
    {
        $service = new JsonRequestResponse();
        $invoiceParticular = InvoiceParticularModel::with(['reports','particular:id,slug,is_custom_report,instruction,slug,category_id,specimen','particular.category:id,name','custom_report'])->where(['uid'=>$id])->first();
        $entity = InvoiceModel::getInvoiceBasicInfo($invoiceParticular->hms_invoice_id);
        $data = ['entity'=>$entity,'invoiceParticular' => $invoiceParticular];
        return  $service->returnJosnResponse($data);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $domain = $this->domain;
        $data = $request->only(['json_content','comment','lab_no']);
        $entity = InvoiceParticularModel::where(['uid'=>$id])->first();
        $data['assign_labuser_id'] = $domain['user_id'];
        $data['assign_labuser_name'] = $domain['user_name'];
        $data['collection_date'] = new \DateTime();
        $data['process'] = 'Done';
        /*if($entity->process == "Tagged"){
            $data['assign_labuser_id'] = $domain['user_id'];
            $data['assign_labuser_name'] = $domain['user_name'];
            $data['collection_date'] = new \DateTime();
            $data['process'] = 'Done';
        }if($entity->process == "In-progress"){
            $data['assign_doctor_id'] = $domain['user_id'];
            $data['assign_doctor_name'] = $domain['user_name'];
            $data['process'] = 'Done';
        }*/
        if(isset($data['json_content'])){
            $data['json_report'] = json_encode($data['json_content']);
            $testReport = InvoiceParticularTestReportModel::where('invoice_particular_id',$entity->id)->first();
            if($testReport){
                $testReport->update($data['json_content']);
            }
        }
        $data['comment'] = $data['comment'] ?? null;
        $data['lab_no'] = $data['lab_no'] ?? null;
        $entity->update($data);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);

    }

    public function inlineUpdate(Request $request,$id)
    {
        $input = $request->all();
        $findParticular = InvoicePathologicalReportModel::find($id);
        $result = (isset($input['result']) and $input['result']) ? $input['result']:'';
        if($result){
            $findParticular->result = $result;
        }
        $ordering = (isset($input['ordering']) and $input['ordering']) ? $input['ordering']:'';
        if($ordering){
            $findParticular->ordering = $ordering;
        }
        $findParticular->save();
        return response()->json(['success' => $findParticular]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        PrescriptionModel::find($id)->delete();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }

}
