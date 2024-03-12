<?php

namespace Modules\Inventory\App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class ConfigRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {

        switch($this->method())
        {
            case 'PUT':
            case 'PATCH':
            {
                return [
                    'business_model_id' => 'required|integer',
                    'vat_percent' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'ait_percent' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'address' => 'string|nullable',
                    'invoice_comment' => 'string|nullable'
                ];
            }
            default:break;
        }
    }

    /*public function messages()
    {
        return [
            'business_model_id.required' => 'Business model field is required.',
            'vat_percent.required' => 'Mobile Required .',
            'customer_unique_id.unique' => 'Customer already exists.',
        ];
    }*/

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
