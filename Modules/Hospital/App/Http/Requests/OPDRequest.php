<?php

namespace Modules\Hospital\App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class OPDRequest extends FormRequest
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
                    'name' => 'required|max:255',
                    'appointment_date' => 'date|nullable',
                    'room_id' => 'integer|nullable',
                    'doctor_id' => 'integer|nullable',
                    'mobile' => 'string|nullable',
                    'district_id' => 'string|nullable',
                    'alternative_mobile' => 'nullable',
                    'age' => 'integer|nullable',
                    'day' => 'nullable',
                    'month' => 'nullable',
                    'year' => 'nullable',
                    'diabetes' => 'string|nullable',
                    'nid' => 'string|nullable',
                    'dob' => 'string|nullable',
                    'gender' => 'string|nullable',
                    'address' => 'string|nullable',
                    'email' => 'email|nullable',
                    'patient_mode' => 'string|nullable',
                    'patient_payment_mode_id' => 'integer|nullable',
                    'amount' => 'numeric|nullable',
                    'guardian_name' => 'string|nullable',
                    'guardian_mobile' => 'string|nullable',
                    'health_id' => 'string|nullable',
                    'api_patient_content' => 'string|nullable',
                    'district' => 'string|nullable',
                    'identity_mode' => 'string|nullable',
                    'identity' => 'string|nullable',
                    'unit_id' => 'integer|nullable',
                    'free_identification' => 'string|nullable',
                    'created_by_id' => 'integer|nullable',
                    'process' => 'string|nullable',
                    'customer_id' => 'integer|nullable',
                ];
            }

            case 'PUT':
            case 'PATCH':
            {
                return [
                    'name' => 'required'.$this->get('id').'|max:255',
                    'location_id' => 'integer|nullable',
                    'marketing_id' => 'integer|nullable',
                    'customer_group_id' => 'integer|nullable',
                    'credit_limit' => 'nullable',
                    'discount_percent' => 'nullable',
                    'reference_id' => 'string|nullable',
                    'alternative_mobile' => 'nullable',
                    'address' => 'string|nullable',
                    'email' => 'email|nullable'
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
