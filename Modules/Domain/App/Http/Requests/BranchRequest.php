<?php

namespace Modules\Domain\App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
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
                    'child_domain_id' => 'required|integer',
                    'checked' => 'required|boolean',
                    'parent_domain_id' => 'required|integer',
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
