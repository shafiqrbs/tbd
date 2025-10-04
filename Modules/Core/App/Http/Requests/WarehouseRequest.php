<?php

namespace Modules\Core\App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        // Check if we are updating (when an 'id' is present) or creating (when 'id' is not present)
        $warehouse = $this->route('warehouse'); // Make sure to match the route parameter name.
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
                    'location' => 'string|nullable',
                    'contract_person' => 'string|nullable',
                    'mobile' => [
                        'nullable',
                        Rule::unique('Modules\Core\App\Entities\Warehouse', 'mobile')
                    ],
                    'email' => 'email|nullable',
                    'address' => 'string|nullable',
                ];
            }

            case 'PUT':
            case 'PATCH':
            {
                return [
                    'name' => 'required|string',
                    'location' => 'string|nullable',
                    'contract_person' => 'string|nullable',
                    'mobile' => [
                        'nullable',
                    ],
                    'email' => 'email|nullable',
                    'address' => 'string|nullable',
                ];
            }
            default:
                break;
        }
    }


    public function messages()
    {
        return [
            'name.required' => 'Name field must be required',
            'name.string' => 'Name field must be string',
            'location.required' => 'Location field must be required',
            'location.string' => 'Location field must be string',
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
