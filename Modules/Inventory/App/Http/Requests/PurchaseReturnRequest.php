<?php

namespace Modules\Inventory\App\Http\Requests;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PurchaseReturnRequest extends FormRequest
{

    public function rules(): array
    {
        // Rules for 'GET' and 'DELETE' methods return an empty array, as they typically don't require validation
        if (in_array($this->method(), ['GET', 'DELETE'])) {
            return [];
        }

        // Common validation rules for 'POST', 'PUT', and 'PATCH' methods
        return [
            'vendor_id' => 'required|integer|regex:/^\d+(\.\d{1,2})?$/',
            'issue_by_id' => 'nullable|integer',
            'narration' => 'nullable|string',
            'return_type' => 'required|string',
            'invoice_date' => 'nullable|date',
            'warehouse_id' => 'nullable',
            'items' => 'required|array',
            'items*.id' => 'required',
            'items*.quantity' => 'required',
            'items*.purchase_price' => 'required',
            'items*.sub_total' => 'required',
            'items*.display_name' => 'nullable|string',
            'items*.unit_name' => 'nullable|string',
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
