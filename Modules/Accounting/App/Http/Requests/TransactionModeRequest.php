<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionModeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {

        switch($this->method())
        {

            case 'PATCH':
            case 'PUT':
            case 'POST':
            {
                return [
                    'name' => 'required|string',
                    'is_selected' => 'nullable',
                    'authorised_mode_id' => 'nullable|integer',
                    'account_mode_id' => 'nullable|integer',
                    'account_type_mode_id' => 'nullable|integer',
                    'mobile' => 'nullable|string',
                    'bank_id' => 'nullable|integer',
                    'service_charge' => 'nullable|string',
                    'short_name' => 'required|string',
                    'account_owner' => 'nullable|string',
                    'service_name' => 'nullable|string',
                    'account_number' => 'nullable|string',
                    'method_id' => 'required|integer',
                    'path' => 'nullable|image|mimes:jpeg,png,jpg,gif',
                ];
            }

            default:break;
        }
    }

    /*public function messages()
    {
        return [
            'name.required' => 'Name field must be required',
            'name.string' => 'Name field must be string',
            'mobile.required' => 'Mobile field must be required',
            'mobile.integer' => 'Mobile field must be number',
            'mobile.unique' => 'Mobile field must be unique',
            'email.required' => 'Email field must be required',
            'email.email' => 'Email field must be valid',
            'email.unique' => 'Email field must be unique',
        ];
    }*/
}
