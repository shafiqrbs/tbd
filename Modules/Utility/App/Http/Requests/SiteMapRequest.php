<?php

namespace Modules\Utility\App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class SiteMapRequest extends FormRequest
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
                    'name' => [
                        'string|required|max:255',
                        Rule::unique('Modules\Core\App\Utility\SiteMap', 'name')
                    ],
                    'router_name' => [
                        'string|required|max:255',
                        Rule::unique('Modules\Core\App\Utility\SiteMap', 'router_name')
                    ],
                    'router_slug' => [
                        'string|required|max:255',
                        Rule::unique('Modules\Core\App\Utility\SiteMap', 'router_slug')
                    ],
                    'module_id' => 'integer|nullable',
                    'description' => 'string|nullable',
                    'status' => 'boolean|nullable',

                ];
            }

            case 'PUT':
            case 'PATCH':
            {
                return [
                    'name' => 'required'.$this->get('id').'|max:255',
                    'mobile' => [
                        'required',
                        Rule::unique('Modules\Core\App\Entities\Customer', 'mobile')
                    ],
                    'location_id' => 'integer|nullable',
                    'marketing_id' => 'integer|nullable',
                    'customer_group' => 'string|nullable',
                    'credit_limit' => 'string|nullable',
                    'reference_id' => 'integer|nullable',
                    'alternative_mobile' => 'integer|nullable',
                    'address' => 'string|nullable',
                    'email' => 'email|nullable',
                    'customer_unique_id' => ['unique:domain_id,mobile,name']
                ];
            }
            default:break;
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'Name Required.',
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
