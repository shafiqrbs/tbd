<?php

namespace Modules\Hospital\App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
                    'category_nature_id' => 'required|string',
                    'name' => 'required|string',
                    'status' => 'boolean|nullable',
                ];
            }

            case 'PUT':
            case 'PATCH':
            {
                return [
                    'category_nature_id' => 'required|string',
                    'name' => 'required|string',
                    'status' => 'boolean|nullable',
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
