<?php

namespace Modules\Medicine\App\Http\Requests;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StockTransferRequest extends FormRequest
{

    public function rules(): array
    {
        // Rules for 'GET' and 'DELETE' methods return an empty array, as they typically don't require validation
        if (in_array($this->method(), ['GET', 'DELETE'])) {
            return [];
        }

        // Common validation rules for 'POST', 'PUT', and 'PATCH' methods
        return [
            'invoice_date' => 'nullable|date',
            'created_by_id' => 'nullable|integer',
            'issue_by_id' => 'nullable|integer',
            'to_warehouse_id' => 'required|integer',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items*.id' => 'required',
            'items*.stock_item_id' => 'required',
            'items*.purchase_item_id' => 'nullable',
            'items*.quantity' => 'required',
            'items*.sales_price' => 'nullable',
            'items*.purchase_price' => 'nullable',
            'items*.sub_total' => 'required',
            'items*.display_name' => 'nullable|string',
            'items*.unit_name' => 'nullable|string',
            'items*.stock_quantity' => 'nullable|integer',
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
