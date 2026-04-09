<?php

namespace Modules\HoribaIntegration\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class HoribaImportCbcRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'device_id' => 'required|integer',
            'records' => 'required|array|min:1|max:100',
            'records.*.lis_record_id' => 'required|integer',
            'records.*.sample_id' => 'required|string|max:50',
            'records.*.lab_id' => 'nullable|string|max:50',
            'records.*.patient_id' => 'nullable|string|max:50',
            'records.*.patient_name' => 'nullable|string|max:150',
            'records.*.patient_gender' => 'nullable|string|max:10',
            'records.*.patient_age_years' => 'nullable|integer',
            'records.*.patient_age_months' => 'nullable|integer',
            'records.*.test_datetime' => 'nullable|date',
            'records.*.received_datetime' => 'nullable|date',
            'records.*.ward_no' => 'nullable|string|max:20',
            'records.*.bed_no' => 'nullable|string|max:20',
            'records.*.wbc' => 'nullable|numeric',
            'records.*.gra_pct' => 'nullable|numeric',
            'records.*.lym_pct' => 'nullable|numeric',
            'records.*.mid_pct' => 'nullable|numeric',
            'records.*.mon_pct' => 'nullable|numeric',
            'records.*.eos_pct' => 'nullable|numeric',
            'records.*.bas_pct' => 'nullable|numeric',
            'records.*.gra_count' => 'nullable|numeric',
            'records.*.lym_count' => 'nullable|numeric',
            'records.*.mid_count' => 'nullable|numeric',
            'records.*.esr' => 'nullable|numeric',
            'records.*.cir_eos' => 'nullable|numeric',
            'records.*.rbc' => 'nullable|numeric',
            'records.*.hgb' => 'nullable|numeric',
            'records.*.hct' => 'nullable|numeric',
            'records.*.mcv' => 'nullable|numeric',
            'records.*.mch' => 'nullable|numeric',
            'records.*.mchc' => 'nullable|numeric',
            'records.*.rdw_sd' => 'nullable|numeric',
            'records.*.rdw' => 'nullable|numeric',
            'records.*.plt' => 'nullable|numeric',
            'records.*.mpv' => 'nullable|numeric',
            'records.*.pct_val' => 'nullable|numeric',
            'records.*.pdw' => 'nullable|numeric',
            'records.*.plcr' => 'nullable|numeric',
            'records.*.bt' => 'nullable|numeric',
            'records.*.ct' => 'nullable|numeric',
            'records.*.wbc_histogram' => 'nullable|string',
            'records.*.rbc_histogram' => 'nullable|string',
            'records.*.plt_histogram' => 'nullable|string',
            'records.*.alarms' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'device_id.required' => 'Device ID is required.',
            'records.required' => 'At least one record is required.',
            'records.max' => 'Maximum 100 records per request.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Form Validation errors',
            'data' => $validator->errors()
        ]));
    }

    public function authorize(): bool
    {
        return true;
    }
}
