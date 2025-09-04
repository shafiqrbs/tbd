<?php

namespace Modules\Hospital\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvestigationReportRequest extends FormRequest
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

            case 'PATCH':
            {
                return [
                    'name' => 'required|string|nullable',
                    'reference_value' => 'string|nullable',
                    'sample_value' => 'string|nullable',
                    'unit_name' => 'string|nullable',
                    'parent_id' => 'integer|nullable',
                ];
            }
            case 'PUT':
            case 'POST':
            {
                return [
                    'name' => 'required|string|nullable',
                    'reference_value' => 'string|nullable',
                    'sample_value' => 'string|nullable',
                    'unit_name' => 'string|nullable',
                    'parent_id' => 'integer|nullable',
                ];
            }

            default:break;
        }
    }

}
