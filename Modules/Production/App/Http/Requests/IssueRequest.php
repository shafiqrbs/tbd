<?php

namespace Modules\Production\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IssueRequest extends FormRequest
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
            case 'PUT':
            case 'POST':
            {
                return [
                    'issue_date' => 'required|date',
                    'issued_by' => 'nullable|string',
                    'batch_id' => 'nullable|string',
                    'issue_type' => 'required|string',
                    'issue_warehouse_id' => 'nullable',
                    'factory_id' => 'nullable',
                    'production_batch_id' => 'nullable',
                    'vendor_id' => 'nullable',
                    'narration' => 'nullable',
                    'type' => 'nullable',
                    'items' => 'required|array',
                ];
            }

            default:break;
        }
    }

}
