<?php

namespace Modules\Inventory\App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class SalesRequest extends FormRequest
{

    public function rules(): array
    {
        // Rules for 'GET' and 'DELETE' methods return an empty array, as they typically don't require validation
        if (in_array($this->method(), ['GET', 'DELETE'])) {
            return [];
        }

        // Common validation rules for 'POST', 'PUT', and 'PATCH' methods
        return [
            'customer_id' => 'required|integer|regex:/^\d+(\.\d{1,2})?$/',
            'sub_total' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'discount_calculation' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            'discount' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            'discount_type' => 'nullable|string',
            'vat' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            'total' => 'required|nullable|regex:/^\d+(\.\d{1,2})?$/',
            'payment' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            'transaction_mode_id' => 'nullable|integer|regex:/^\d+(\.\d{1,2})?$/',
            'items' => 'required|array',
            'sales_by_id' => 'nullable|integer',
            'created_by_id' => 'nullable|integer',
            'process' => 'nullable|string',
            'narration' => 'nullable|string',

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
