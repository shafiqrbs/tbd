<?php

namespace Modules\Domain\App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchPriceUpdateRequest extends FormRequest
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
                    'field_name' => 'required|string',
                    'value' => 'required|integer',
                    'customer_id' => 'required|integer',
                ];
            }
            default:break;
        }
    }

    public function messages()
    {
        return [
            'field_name.required' => 'Field name must be required',
            'field_name.string' => 'Field name must be string',
            'value.required' => 'Amount field must be required',
            'customer_id.required' => 'Customer field must be required',
            'value.integer' => 'Amount field must be integer',
            'customer_id.integer' => 'Customer field must be integer',
        ];
    }
}
