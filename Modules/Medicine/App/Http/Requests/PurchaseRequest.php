<?php

namespace Modules\Medicine\App\Http\Requests;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PurchaseRequest extends FormRequest
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
            'created_by_id' => 'nullable|integer',
            'comment' => 'nullable|string',
            'items' => 'required|array',
            'items*.expired_date' => 'nullable|date',
            'items*.production_date' => 'nullable|date',
            'items*.stock_item_id' => 'required|integer|regex:/^\d+(\.\d{1,2})?$/',
            'items*.quantity' => 'required|integer',
            'items*.medicine_name' => 'nullable|string',
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
