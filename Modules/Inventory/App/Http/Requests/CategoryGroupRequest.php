<?php

namespace Modules\Inventory\App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CategoryGroupRequest extends FormRequest
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
                    'name' => 'required|string',
                    'expiry_duration' => 'integer|nullable',
                    'status' => 'required|boolean',
                    'parent' => 'integer|nullable',
                ];
            }

            case 'PUT':
            case 'PATCH':
            {
                return [
                    'name' => 'required|string',
                    'status' => 'required|boolean',
                    'parent' => 'integer|nullable',
                    'expiry_duration' => 'integer|nullable',
                ];
            }
            default:break;
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'Category group field must be required',
            'name.string' => 'Category group  field must be string',
            'status.required' => 'Category group status field must be required',
            'status.boolean' => 'Category group status field must be boolean',
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
