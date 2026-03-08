<?php

namespace Modules\Inventory\App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProcessSalesReturnRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'data'                        => 'required|array|min:1',
            'data.*.id'                   => 'required|integer',
            'data.*.stock_entry_quantity'  => 'required|numeric|min:0',
            'data.*.damage_entry_quantity' => 'required|numeric|min:0',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Form Validation errors',
            'data'    => $validator->errors()
        ]));
    }

    public function authorize(): bool
    {
        return true;
    }
}
