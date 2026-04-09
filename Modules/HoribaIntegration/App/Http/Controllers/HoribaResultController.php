<?php

namespace Modules\HoribaIntegration\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\UserModel;
use Modules\HoribaIntegration\App\Http\Requests\HoribaImportCbcRequest;
use Modules\HoribaIntegration\App\Models\HoribaResultModel;
use Modules\Hospital\App\Models\InvoiceModel;
use Modules\Hospital\App\Models\InvoiceParticularModel;

class HoribaResultController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if ($userId && !empty($userId)) {
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }

    /**
     * Bridge Agent health check.
     */
    public function ping()
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Bridge Agent sends CBC records.
     */
    public function importCbc(HoribaImportCbcRequest $request)
    {
        $validated = $request->validated();
        $deviceId = $validated['device_id'];
        $records = $validated['records'];

        $storedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($records as $record) {
                $exists = HoribaResultModel::where('device_id', $deviceId)
                    ->where('lis_record_id', $record['lis_record_id'])
                    ->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                $record['device_id'] = $deviceId;
                $record['raw_json'] = json_encode($record);
                HoribaResultModel::create($record);
                $storedCount++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ]));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'success' => true,
            'stored_count' => $storedCount,
            'skipped_count' => $skippedCount,
            'received_at' => now()->toIso8601String(),
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * List results with filters (frontend).
     */
    public function index(Request $request)
    {
        $data = HoribaResultModel::getRecords($request);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities'],
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Single result detail.
     */
    public function show($id)
    {
        $entity = HoribaResultModel::with('device')->find($id);

        if (!$entity) {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'Record not found',
                'status' => Response::HTTP_NOT_FOUND,
                'data' => [],
            ]));
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $entity,
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Update result fields (inline edit).
     */
    public function update(Request $request, $id)
    {
        $entity = HoribaResultModel::find($id);

        if (!$entity) {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'Record not found',
                'status' => Response::HTTP_NOT_FOUND,
            ]));
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response;
        }

        $allowedFields = [
            'sample_id', 'patient_id', 'patient_name', 'patient_gender',
            'patient_age_years', 'patient_age_months',
            'wbc', 'gra_pct', 'lym_pct', 'mid_pct', 'mon_pct', 'eos_pct', 'bas_pct',
            'gra_count', 'lym_count', 'mid_count', 'esr', 'cir_eos',
            'rbc', 'hgb', 'hct', 'mcv', 'mch', 'mchc', 'rdw_sd', 'rdw',
            'plt', 'mpv', 'pct_val', 'pdw', 'plcr', 'bt', 'ct',
        ];

        $data = $request->only($allowedFields);
        $entity->update($data);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'Result updated successfully',
            'status' => Response::HTTP_OK,
            'data' => $entity->fresh(),
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Map result to invoice particular.
     */
    public function mapResult(Request $request, $id)
    {
        $entity = HoribaResultModel::find($id);

        if (!$entity) {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'Record not found',
                'status' => Response::HTTP_NOT_FOUND,
            ]));
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response;
        }

        $entity->update([
            'invoice_particular_id' => $request->input('invoice_particular_id'),
            'is_mapped' => true,
            'is_approved' => true,
        ]);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'Result mapped successfully',
            'status' => Response::HTTP_OK,
            'data' => $entity,
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Lab tech approval.
     */
    public function approveResult($id)
    {
        $entity = HoribaResultModel::find($id);

        if (!$entity) {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'Record not found',
                'status' => Response::HTTP_NOT_FOUND,
            ]));
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response;
        }

        $entity->update([
            'is_approved' => true,
        ]);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'Result approved successfully',
            'status' => Response::HTTP_OK,
            'data' => $entity,
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Search invoices for mapping.
     */
    public function searchInvoices(Request $request)
    {
        $term = trim($request->input('term', ''));

        if (strlen($term) < 2) {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'success',
                'status' => Response::HTTP_OK,
                'data' => [],
            ]));
            $response->setStatusCode(Response::HTTP_OK);
            return $response;
        }

        $domain = $this->domain;
        $entities = InvoiceModel::join('cor_customers as customer', 'customer.id', '=', 'hms_invoice.customer_id')
            ->where(function ($q) use ($term) {
                $q->where('customer.name', 'LIKE', "%{$term}%")
                    ->orWhere('customer.mobile', 'LIKE', "%{$term}%")
                    ->orWhere('hms_invoice.invoice', 'LIKE', "%{$term}%");
            })
            ->select([
                'hms_invoice.id',
                'hms_invoice.invoice',
                'hms_invoice.uid',
                'customer.name as patient_name',
                'customer.customer_id as patient_id',
                'customer.mobile',
                'hms_invoice.total',
                'hms_invoice.process',
                'hms_invoice.created_at',
            ])
            ->orderByRaw("
                CASE
                    WHEN hms_invoice.invoice = ? THEN 1
                    WHEN hms_invoice.invoice LIKE ? THEN 2
                    WHEN customer.customer_id LIKE ? THEN 3
                    WHEN customer.mobile LIKE ? THEN 4
                    ELSE 5
                END ASC, hms_invoice.created_at DESC
            ", [$term, "%{$term}%", "%{$term}%", "%{$term}%"])
            ->limit(20)
            ->get();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $entities,
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Get invoice particulars for mapping.
     */
    public function invoiceParticulars($invoiceId)
    {
        $particulars = InvoiceParticularModel::where('hms_invoice_id', $invoiceId)
            ->select([
                'id',
                'hms_invoice_id',
                'particular_id',
                'name',
                'price',
                'quantity',
                'mode',
                'status',
                'process',
            ])
            ->orderBy('id', 'ASC')
            ->get();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $particulars,
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Sync stats for Bridge Agent status widget.
     */
    public function stats()
    {
        $data = HoribaResultModel::getStats();
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $data,
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }
}
