<?php

namespace Modules\Core\App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorRequest extends FormRequest
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
                    'company_name' => 'required|string',
                    'name' => 'required|string',
                    'mobile' => [
                        'required',
                        Rule::unique('Modules\Core\App\Entities\Vendor', 'mobile')
                    ],
                    'customer_id' => [
                        'nullable',
                    ],
                    'email' => 'email|nullable',
                    'address' => 'string|nullable',
                ];
            }

            case 'PUT':
            case 'PATCH':
            {
                return [
                    'company_name' => 'required|string',
                    'name' => 'required|string',
                    'mobile' => [
                        'required|numeric',
                        Rule::unique('Modules\Core\App\Entities\Vendor', 'mobile')
                    ],
                    'email' => 'email|nullable',
                    'customer_id' => 'integer|nullable',
                    'address' => 'string|nullable',
                ];
            }
            default:break;
        }
    }

    public function messages()
    {
        return [
            'company_name.required' => 'Company name field must be required',
            'company_name.string' => 'Company name field must be string',
            'name.required' => 'Name field must be required',
            'name.string' => 'Name field must be string',
            'mobile.required' => 'Mobile field must be required',
            'mobile.integer' => 'Mobile field must be number',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
