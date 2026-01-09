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
use Modules\Hospital\App\Models\AdmissionPatientModel;
use Modules\Hospital\App\Models\HospitalConfigModel;
use Modules\Hospital\App\Models\HospitalSalesModel;
use Modules\Hospital\App\Models\InvoiceContentDetailsModel;
use Modules\Hospital\App\Models\InvoiceModel;
use Modules\Hospital\App\Models\InvoiceParticularModel;
use Modules\Hospital\App\Models\InvoiceTransactionModel;
use Modules\Hospital\App\Models\MedicineDosageModel;
use Modules\Hospital\App\Models\OPDModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularModeModel;
use Modules\Hospital\App\Models\PatientModel;
use Modules\Hospital\App\Models\PatientPrescriptionMedicineModel;
use Modules\Hospital\App\Models\PrescriptionModel;
use Modules\Hospital\App\Models\TreatmentMedicineModel;


class IpdPrescriptionController extends Controller
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
        $data = PrescriptionModel::getRecords($request,$domain);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'ipdRooms' => $data['ipdRooms'],
            'selectedRoom' => $data['selectedRoom'],
            'total' => $data['count'],
            'data' => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $domain = $this->domain;
        $entity = PatientPrescriptionMedicineModel::insertIpdSingleMedicine($data);
        $service = new JsonRequestResponse();
        $localMedicines = PatientPrescriptionMedicineModel::getIpdMedicineLocalDropdown($domain);
        $entity['localMedicines'] = $localMedicines;
        $data = $service->returnJosnResponse($entity);
        return $data;
    }


    /**
     * Show the specified resource.
     *//**/
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = PrescriptionModel::getShow($id);
        //$entity = PrescriptionModel::with(['invoice_details','invoice_details.customer_details'])->find($id);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $service = new JsonRequestResponse();
        $entity = PrescriptionModel::getShow($id);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

     /**
     * Show the form for editing the specified resource.
     */
    public function vitalCheck($id)
    {
        $service = new JsonRequestResponse();
        $entity = PrescriptionModel::findByIdOrUid($id);
        $invoice = InvoiceModel::find($entity->hms_invoice_id);
        $vital = $invoice->is_vital == 0 ? 1 : 0;
        $invoice->update([
            'is_vital' => $vital
        ]);
        $data = $service->returnJosnResponse('success');
        return $data;
    }

    public function medicineInlineUpdate(Request $request,$id)
    {
        $input = $request->all();
        $findParticular = PatientPrescriptionMedicineModel::find($id);
        if(isset($input['is_active'])){
            $findParticular->is_active = ($input['is_active'] == 'true') ? 1:0;
        }
        if(isset($input['ordering']) and $input['ordering']){
            $findParticular->ordering = $input['ordering'];
        }
        if(isset($input['medicine_dosage_id']) and $input['medicine_dosage_id']){
            $findParticular->medicine_dosage_id  = $input['medicine_dosage_id'];
            $dosage = MedicineDosageModel::find($input['medicine_dosage_id']);
            if ($dosage) {
                $findParticular->dose_details = $dosage->name;
                $findParticular->dose_details_bn = $dosage->name_bn;
                $findParticular->continue_mode = $dosage->continue_mode;
                $findParticular->daily_quantity = $dosage->quantity;
            }
        }
        if(isset($input['medicine_bymeal_id']) and $input['medicine_bymeal_id']){
            $findParticular->medicine_bymeal_id  = $input['medicine_bymeal_id'];
            $bymeal = MedicineDosageModel::find($input['medicine_bymeal_id']);
            if ($bymeal) {
                $findParticular->by_meal = $bymeal->name;
                $findParticular->by_meal_bn = $bymeal->name_bn;
            }

        }
        if(isset($input['instruction']) and $input['instruction']){
            $findParticular->instruction  = $input['instruction'];
        }
        if(isset($input['start_date']) and $input['start_date']){
            $findParticular->start_date  = new \DateTime($input['start_date']);
        }
        $findParticular->save();
        return response()->json(['success' => true]);
    }

    public function updateOrdering(Request $request)
    {
        foreach ($request->order as $row) {
            PatientPrescriptionMedicineModel::where('id', $row['id'])
                ->update(['order' => $row['ordering']]);
        }
        return response()->json(['success' => true]);
    }

    public function updateTemplate(Request $request,$id)
    {
        $domain = $this->domain;
        $entity = PrescriptionModel::findByIdOrUid($id);
        $templateId = $request->get('template_id');
        if($templateId){
            $template = TreatmentMedicineModel::where(['treatment_template_id'=> $templateId])->get();
            PatientPrescriptionMedicineModel::insertTemplateMedicine($entity,$template);
        }
        $return = PrescriptionModel::getShow($id);
        $localMedicines = PatientPrescriptionMedicineModel::getMedicineLocalDropdown($domain);
        $return['localMedicines'] = $localMedicines;
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($return);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $domain = $this->domain;
        $data = $request->all();
        $entity = PrescriptionModel::findByIdOrUid($id);
        $data['json_content'] = json_encode($data);
        $data['prescribe_doctor_id'] = $domain['user_id'];
        $data['follow_up_id'] = (isset($data['follow_up_date']) and $data['follow_up_date']) ? $data['follow_up_date'] :'';
        $data['process'] = 'done';
        $entity->update($data);
        $weight = $data['weight'] ?? null;
        InvoiceModel::invoicePrescriptionProcess($entity->invoice);
        $entity->invoice->invoice_mode;
        if($entity->invoice->invoice_mode == "ipd"){
            $entity->invoice->update(['is_prescription' => 1,'weight' => $weight]);
        }
        InvoiceTransactionModel::insertInvestigations($domain,$entity->id);
        HospitalSalesModel::insertMedicineIssue($domain,$entity->id);
        InvoiceContentDetailsModel::insertContentDetails($domain,$entity->id);
        AdmissionPatientModel::insertDeathCertificate($domain,$entity->invoice->id,$data);
        $return = PrescriptionModel::getShow($entity->id);
        $localMedicines = PatientPrescriptionMedicineModel::getMedicineLocalDropdown($domain);
        $return['localMedicines'] = $localMedicines;
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($return);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteMedicine($id)
    {
        $service = new JsonRequestResponse();
        PatientPrescriptionMedicineModel::find($id)->delete();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        PatientPrescriptionMedicineModel::find($id)->delete();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }

}
