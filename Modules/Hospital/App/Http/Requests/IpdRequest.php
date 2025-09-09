<?php

namespace Modules\Hospital\App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class IpdRequest extends FormRequest
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
            case 'GET':
            case 'DELETE':
            {
                return [];
            }
            case 'POST':
            {
                return [
                    'config_id' => 'integer|nullable',
                    'hms_invoice_id' => 'integer|nullable',
                    'room_id' => 'integer|nullable',
                    'admit_unit_id' => 'integer|nullable',
                    'admit_doctor_id' => 'integer|nullable',
                    'admit_department_id' => 'integer|nullable',
                    'comment' => 'string|nullable',
                ];
            }

            case 'PUT':
            case 'PATCH':
            {
                return [
                    'room_id' => 'integer|nullable',
                    'unit_id' => 'integer|nullable',
                    'admit_doctor_id' => 'integer|nullable',
                    'department_id' => 'integer|nullable',
                    'comment' => 'string|nullable',
                ];
            }
            default:break;
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'Name field Required.',
            'mobile.required' => 'Mobile Required .',
            'customer_unique_id.unique' => 'Customer already exists.',
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
