<?php

namespace Modules\Inventory\App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ProductRequest extends FormRequest
{

    public function rules(): array
    {
        if (in_array($this->method(), ['GET', 'DELETE'])) {
            return [];
        }

        switch($this->method())
        {

            case 'PATCH':
            {
                return [
                    'product_type_id' => 'required|integer',
                    'category_id' => 'required|integer',
                    'unit_id' => 'required|integer',
//                    'brand_id' => 'nullable|integer',
                    'name' => 'required|string',
                    'alternative_name' => 'nullable|string',
                    'bangla_name' => 'nullable|string',
                    'sku' => 'nullable|string',
                    'barcode' => 'nullable',
                    'opening_quantity' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'min_quantity' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'reorder_quantity' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'description' => 'nullable|string',
                    'brand_id' => 'nullable|integer',
                    'color_id' => 'nullable|integer',
                    'size_id' => 'nullable|integer',
                    'grade_id' => 'nullable|integer',
                    'model_id' => 'nullable|integer',
                    'expiry_duration' => 'nullable|integer',
                    'purchase_price' => 'nullable',
                    'sales_price' => 'nullable',
                ];
            }
            case 'PUT':
            case 'POST':
            {
                return [
                    'product_type_id' => 'required|integer',
                    'category_id' => 'required|integer',
                    'unit_id' => 'required|integer',
//                    'brand_id' => 'nullable|integer',
                    'name' => 'required|string',
                    'alternative_name' => 'nullable|string',
                    'bangla_name' => 'nullable|string',
                    'sku' => 'nullable|string',
                    'barcode' => 'nullable',
                    'opening_quantity' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'sales_price' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'purchase_price' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'min_quantity' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'reorder_quantity' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'status' => 'required|boolean',
                    'description' => 'nullable|string',
                    'expiry_duration' => 'nullable|integer',
                    'brand_id' => 'nullable|integer',
                    'color_id' => 'nullable|integer',
                    'size_id' => 'nullable|integer',
                    'grade_id' => 'nullable|integer',
                    'model_id' => 'nullable|integer',
                ];
            }

            default:break;
        }
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
