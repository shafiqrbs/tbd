<?php

namespace Modules\Inventory\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PosSalesProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_id'     => 'required|string|max:100',
            'sync_batch_id' => 'required|string|max:100',
            'content'       => 'required|array|min:1|max:500',

//            'content.*.sales_items' => 'required|array|min:1',
//            'content.*.sales_items.*.stock_item_id' => 'required|integer',
//            'content.*.sales_items.*.quantity' => 'required|numeric|min:1',

//            'content.*.customerName' => 'nullable|string|max:255',
//            'content.*.customerMobile' => 'nullable|string|max:20',
//            'content.*.salesById' => 'nullable|integer',
        ];
    }


    // Return JSON on validation error
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 422,
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422));
    }
}
