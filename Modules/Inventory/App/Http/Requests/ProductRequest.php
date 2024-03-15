<?php

namespace Modules\Inventory\App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ProductRequest extends FormRequest
{

    public function rules(): array
    {
        // Rules for 'GET' and 'DELETE' methods return an empty array, as they typically don't require validation
        if (in_array($this->method(), ['GET', 'DELETE'])) {
            return [];
        }

        // Common validation rules for 'POST', 'PUT', and 'PATCH' methods
        return [
            'product_type_id' => 'required|integer',
            'category_id' => 'required|integer',
            'unit_id' => 'required|integer',
            'brand_id' => 'nullable|integer',
            'name' => 'required|string',
            'alternative_name' => 'nullable|string',
            'sku' => 'nullable|string',
            'barcode' => 'nullable|string',
            'opening_quantity' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'sales_price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'purchase_price' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'min_quantity' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'reorder_quantity' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'status' => 'required|boolean',
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
