<?php

namespace Modules\Hospital\App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class ParticularModeRequest extends FormRequest
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
                    'name' => 'required|string|max:255',
                    'short_code' => 'string|max:255',
                    'particular_module_id' => 'required|integer',
                ];
            }

            case 'PUT':
            case 'PATCH':
            {
                return [
                    'name' => 'required|string|max:255',
                    'short_code' => 'string|max:255',
                    'particular_module_id' => 'required|integer',
                ];
            }
            default:break;
        }
    }

    public function messages()
    {
        return [
            'particular_module_id.required' => 'The particular module is required.',
            'name.string' => 'Name field must be string',
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
