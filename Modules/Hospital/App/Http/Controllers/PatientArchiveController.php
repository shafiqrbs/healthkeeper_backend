<?php

namespace Modules\Hospital\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
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
use Modules\Hospital\App\Http\Requests\IpdRequest;
use Modules\Hospital\App\Http\Requests\OPDRequest;
use Modules\Hospital\App\Http\Requests\ReferredRequest;
use Modules\Hospital\App\Models\HospitalConfigModel;
use Modules\Hospital\App\Models\InvoiceModel;
use Modules\Hospital\App\Models\InvoiceParticularModel;
use Modules\Hospital\App\Models\InvoicePatientReferredModel;
use Modules\Hospital\App\Models\IpdModel;
use Modules\Hospital\App\Models\OPDModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularModeModel;
use Modules\Hospital\App\Models\PatientArchiveModel;
use Modules\Hospital\App\Models\PatientModel;
use Modules\Hospital\App\Models\PatientPrescriptionMedicineModel;
use Modules\Hospital\App\Models\PoliceCaseModel;
use Modules\Hospital\App\Models\PrescriptionModel;
use function Symfony\Component\TypeInfo\null;


class PatientArchiveController extends Controller
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
        $data = PatientArchiveModel::getRecords($request,$domain);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message'   => 'success',
            'status'    => Response::HTTP_OK,
            'total'     => $data['count'],
            'data'      => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Show the specified resource.
     */
    public function store(IpdRequest $request)
    {
        $domain = $this->domain;
        $input = $request->validated();
        $reAdmission = InvoiceModel::where('uid',$input['hms_invoice_id'])->first();
        if (in_array($reAdmission->process, ['paid','discharged','re-admission'])) {

            ParticularModel::where([
                'is_booked' => 1,
                'admission_id' => $reAdmission->id
            ])->update([
                'is_booked' => 0,
                'admission_id' => null,
            ]);
            $reAdmission->update([
                'release_mode'    => 're-admission',
                'process'         => 're-admission',
                'is_prescription' => 1,
            ]);
        }
        $parentInvoice = $reAdmission->parent;
        DB::beginTransaction();
        try {

            $input['config_id'] = $domain['hms_config'];
            $input['parent_id'] = $parentInvoice->id;
            $input['customer_id'] = $parentInvoice->customer_id;
            $input['created_by_id'] = $domain['user_id'];
            $patient_mode_id = ParticularModeModel::firstWhere([
                ['slug', 'ipd'],
                ['particular_module_id', 3],
            ])->id;
            $input['patient_mode_id'] = $patient_mode_id;
            $entity = IpdModel::create($input);
            IpdModel::insertReadmissionHmsInvoice($domain,$parentInvoice,$reAdmission,$entity,$input);
            $newPrescription = PrescriptionModel::updateOrCreate(
                ['hms_invoice_id' => $entity->id],
                [
                    'created_by_id' => $domain['user_id'] ,
                    'process' => "new",
                ]
            );
            PatientPrescriptionMedicineModel::insertReadmissionPatient($reAdmission,$newPrescription);
            DB::commit();
            $status = ['status'=>'success'];
            $service = new JsonRequestResponse();
            return $service->returnJosnResponse($status);
        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();

            // Optionally log the exception for debugging purposes
            \Log::error('Error storing domain and related data: ' . $e->getMessage());

            // Return an error response
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'An error occurred while saving the domain and related data.',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }
    }

}
