<?php

namespace Modules\Domain\App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class B2bDomainWiseCategoryRequest extends FormRequest
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
                    'percent_mode' => 'required|in:Increase,Decrease',
                    'purchase_mode' => 'required|in:Increase,Decrease',
                    'mrp_percent' => 'required',
                    'purchase_percent' => 'required',
                    'bonus_percent' => 'nullable',
                ];
            }
            default:break;
        }
    }

    public function messages()
    {
        return [
            'child_domain_id.required' => 'Branch field must be required',
            'checked.string' => 'Check field must be string',
            'parent_domain_id.required' => 'Domain field must be required',
        ];
    }
}
