<?php

namespace Modules\Medicine\App\Http\Requests;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DispenseRequest extends FormRequest
{

    public function rules(): array
    {
        // Rules for 'GET' and 'DELETE' methods return an empty array, as they typically don't require validation
        if (in_array($this->method(), ['GET', 'DELETE'])) {
            return [];
        }

        // Common validation rules for 'POST', 'PUT', and 'PATCH' methods
        return [
            'remark' => 'nullable|string',
            'dispense_type' => 'required|string',
            'dispense_no' => 'nullable|string',
            'created_by_id' => 'nullable|integer|regex:/^\d+(\.\d{1,2})?$/',
            'warehouse_id' => 'required|integer|regex:/^\d+(\.\d{1,2})?$/',
            'items' => 'required|array',
            'items*.stock_item_id' => 'required|integer|regex:/^\d+(\.\d{1,2})?$/',
            'items*.name' => 'nullable|string',
            'items*.quantity' => 'required|integer',
            'items*.config_id' => 'required|integer',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Form Validation errors',
            'data'      => $validator->errors()
        ]));
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
