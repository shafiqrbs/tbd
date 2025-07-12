<?php

namespace Modules\Domain\App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DomainRequest extends FormRequest
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
                    'mobile' => [
                        'required',
                        Rule::unique('Modules\Domain\App\Entities\GlobalOption', 'mobile')
                    ],
                    'email' => 'required|email',
                    'company_name' => 'required|string',
                    'alternative_mobile' => 'string|nullable',
                    'short_name' => 'string|nullable',
                    'username' => 'required|string',
                    'address' => 'string|nullable',
                    'business_model_id' => 'required',
                    'modules' => 'nullable|array',
                ];
            }

            case 'PUT':
            case 'PATCH':
            {
                $domainId = $this->route('global');
                return [
                    'name' => 'required|string',
                    'mobile' => [
                        'required',
                        Rule::unique('Modules\Domain\App\Entities\GlobalOption', 'mobile')->ignore($domainId)
                    ],
                    'email' => 'required|email',
                    'company_name' => 'required|string',
                    'alternative_mobile' => 'string|nullable',
                    'address' => 'string|nullable',
                    'business_model_id' => 'required',
                    'modules' => 'nullable|array',
                    'product_types' => 'nullable|array',
                ];
            }
            default:break;
        }
    }

    public function messages()
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
    }
}
