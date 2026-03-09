<?php

namespace Modules\Inventory\App\Http\Requests;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SalesReturnRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [

            'sales_id' => 'required',
            'items' => 'required|array|min:1',
            'items.*.sales_item_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.stock_entry_quantity' => 'required|numeric|min:0',
            'items.*.damage_entry_quantity' => 'required|numeric|min:0',

        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [

            'sales_id.required' => 'Sales ID is required',

            'items.required' => 'At least one return item is required',

            'items.*.sales_item_id.required' => 'Sales item is required',

            'items.*.quantity.required' => 'Return quantity is required',

            'items.*.quantity.min' => 'Return quantity must be at least 1',

        ];
    }
}
