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

            case 'POST':
            {
                return [
                    'name' => 'required|string',
                    'authorised_mode_id' => 'required|string',
                    'account_mode_id' => 'required|string',
                    'service_charge' => 'nullable|string',
                    'short_name' => 'required|string',
                    'account_owner' => 'nullable|string',
                    'service_name' => 'nullable|string',
                    'method_id' => 'required|integer',
                    'path' => 'required|image|mimes:jpeg,png,jpg,gif',
//                    'path' => 'required',
                ];
            }

            case 'PUT':
            case 'PATCH':
            {
                return [
                    'name' => 'required|string',
                    'mobile' => 'required|numeric',
                    'email' => 'required|email',
                    'address' => 'string',
                    'company_name' => 'required|string',
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
